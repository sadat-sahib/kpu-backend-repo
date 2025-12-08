<?php

namespace App\Http\Controllers\Api\FrontControllers;

use App\Models\Book;
use App\Models\User;
use App\Models\Cart;
use App\Models\Faculty;
use App\Models\Reserve;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Category;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Models\Pdf;

class HomeController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $faculty = Faculty::find($request->fac_id, ['id']);
        $department = Department::find($request->dep_id, ['id']);

        if (!$faculty || !$department) {
            return response()->json([
                'message' => $faculty ? "دیپارتمنت پیدا نشد" : "فاکولته پیدا نشد"
            ], Response::HTTP_NOT_FOUND);
        }

        DB::beginTransaction();
        try {
            $baseData = $request->only([
                'firstName',
                'lastName',
                'phone',
                'nin',
                'nic',
                'current_residence',
                'original_residence',
                'fac_id',
                'dep_id'
            ]);

            $imagePath = $request->hasFile('image')
                ? "storage/" . $request->file('image')->store('images/users', 'public')
                : null;

            $userType = strtolower($request->type);
            $userable = $this->createUserable($userType, $baseData, $imagePath);

            $user = $userable->user()->create([
                "email" => $request->email,
                "password" => Hash::make($request->password),
                "status" => "inactive",
                "type" => $userType,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'token' => $user->createToken($user->email)->plainTextToken,
            'user' => $this->formatUser($user)
        ], Response::HTTP_CREATED);
    }

    private function createUserable($type, $data, $imagePath)
    {
        $userable = $type === 'teacher'
            ? Teacher::create($data)
            : Student::create($data);

        if ($imagePath) {
            $userable->image()->create(['image' => $imagePath]);
        }

        return $userable;
    }

    private function formatUser(User $user)
    {
        return [
            'id' => $user->id,
            'email' => $user->email,
            'type' => $user->type,
            'status' => $user->status
        ];
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)
            ->first(['id', 'email', 'password', 'type', 'status']);

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'ایمیل یا پسورد شما اشتباه می‌باشد'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            'token' => $user->createToken($user->email)->plainTextToken,
            'user' => $this->formatUser($user)
        ]);
    }

    public function logout()
    {
        if ($user = auth()->user()) {
            $user->tokens()->delete();
            $user->carts()->delete();
        }
        return response()->json([
            'message' => 'شما موفقانه خارج شدید'
        ]);
    }

    public function booksByCategoryId(Request $request, $id)
    {
        $category = Category::with([
            'books' => fn($q) => $q->select(['id', 'title', 'author', 'cat_id'])
                ->with(['image:id,imageable_id,image'])
        ])->find($id, ['id', 'name']);

        if (!$category) {
            return response()->json([
                'message' => "کتگوری وجود ندارد"
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'category_id' => $category->id,
            'category_name' => $category->name,
            'books' => $category->books->map(fn($book) => [
                'id' => $book->id,
                'title' => $book->title,
                'author' => $book->author,
                'image' => $book->image ? asset($book->image->image) : null
            ])
        ]);
    }

    public function reserveBook(Request $request, $id)
    {
        $user = auth()->user();

        if ($user->status === 'inactive') {
            return response()->json([
                'message' => 'حساب کاربری شما غیرفعال است.'
            ], Response::HTTP_FORBIDDEN);
        }

        if ($user->reserves()->where('book_id', $id)->exists()) {
            Cart::where('book_id', $id)->delete();
            return response()->json([
                'message' => 'شما قبلاً این کتاب را رزرو کرده‌اید.'
            ], Response::HTTP_CONFLICT);
        }

        $book = Book::with('stock')->find($id);
        if (!$book) {
            return response()->json([
                'message' => 'کتاب مورد نظر یافت نشد.'
            ], Response::HTTP_NOT_FOUND);
        }

        if ($book->stock->remain < 1) {
            return response()->json([
                'message' => 'تمام نسخه‌های این کتاب رزرو شده‌اند.لطفاً بعداً دوباره تلاش کنید.'
            ], Response::HTTP_CONFLICT);
        }

        DB::transaction(function () use ($user, $book, $id) {
            Reserve::create([
                'book_id' => $book->id,
                'user_id' => $user->id,
                'user_type' => $user->type,
            ]);

            $book->stock()->decrement('remain');
            Cart::where('book_id', $id)->delete();
        });

        return response()->json([
            'message' => 'رزرو کتاب با موفقیت انجام شد لطفا منتظر تایید درخواست تان باشید.'
        ], Response::HTTP_OK);
    }

    public function search(Request $request)
    {
        $validTypes = ['title', 'author', 'department', 'faculty'];
        $type = in_array($request->searchType, $validTypes)
            ? $request->searchType
            : 'title';

        $query = Book::query()->with(['department.faculty', 'image']);

        match ($type) {
            'title' => $query->where('title', 'LIKE', "%{$request->searchKey}%"),
            'author' => $query->where('author', 'LIKE', "%{$request->searchKey}%"),
            'department' => $query->whereHas('department', fn($q) =>
            $q->where('name', 'LIKE', "%{$request->searchKey}%")),
            'faculty' => $query->whereHas('department.faculty', fn($q) =>
            $q->where('name', 'LIKE', "%{$request->searchKey}%")),
        };

        return response()->json([
            'data' => $query->get()->map(fn($book) => [
                'id' => $book->id,
                'title' => $book->title,
                'author' => $book->author,
                'department' => $book->department->name,
                'faculty' => $book->department->faculty->name,
                'image' => $book->image ? asset($book->image->image) : null
            ])
        ]);
    }
    public function getCategories()
    {
        $categories = Category::pluck('name', 'id')->toArray();

        $formatted = [];
        foreach ($categories as $id => $name) {
            $formatted[] = ['id' => $id, 'name' => $name];
        }

        return response()->json([
            'data' => $formatted
        ]);
    }

    public function getMainInformation()
    {
        $counts = [
            'all_books' => Book::count(),
            'all_reservable_books' => Book::where('borrow', 'no')->count(),
            'all_barrowable_books' => Book::where('borrow', 'yes')->count(),
            'pdf_books' => Book::where('format', 'pdf')->count(),
            'hard_books' => Book::where('format', 'hard')->count(),
            'both_type_books' => Book::where('format', 'both')->count(),
            'all_registered_users' => User::count(),
        ];

        return response()->json(compact('counts'));
    }

    public function getCategoriesWithBooks()
    {
        $categories = Category::withCount('books')
            ->with([
                'books' => fn($q) => $q
                    ->select(['id', 'title', 'author', 'cat_id', 'format'])
                    ->with(['image:id,imageable_id,image'])
            ])
            ->get(['id', 'name']);

        return response()->json([
            'data' => $categories->map(fn($cat) => [
                'category_id' => $cat->id,
                'category_name' => $cat->name,
                'books_count' => $cat->books_count,
                'books' => $cat->books->map(fn($book) => [
                    'id' => $book->id,
                    'title' => $book->title,
                    'author' => $book->author,
                    'image' => $book->image ? asset($book->image->image) : null,
                    'format' => $book->format
                ])
            ])
        ]);
    }

    public function BookDetailById($id)
    {
        $book = Book::with([
            'category:id,name',
            'department.faculty:id,name',
            'image:id,imageable_id,image'
        ])->find($id, [
            'id',
            'title',
            'author',
            'publisher',
            'publicationYear',
            'lang',
            'edition',
            'translator',
            'isbn',
            'code',
            'description',
            'cat_id',
            'dep_id',
            'format',
            'borrow'
        ]);

        if (!$book) {
            return response()->json([
                'message' => "کتاب وجود ندارد"
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => [
                ...$book->toArray(),
                'category' => $book->category->name,
                'department' => $book->department->name,
                'faculty' => $book->department->faculty->name,
                'image' => $book->image ? asset($book->image->image) : null
            ]
        ]);
    }

    public function getFacultyWithDepartments()
    {
        $faculties = Faculty::with(['departments' => fn($q) => $q->select('id', 'name', 'fac_id')])
            ->get(['id', 'name'])
            ->map(fn($f) => [
                'id' => $f->id,
                'name' => $f->name,
                'departments' => $f->departments->map(fn($d) => [
                    'id' => $d->id,
                    'name' => $d->name
                ])
            ]);

        return response()->json(compact('faculties'));
    }

    public function streamPdf(string $id)
    {
        $pdf = Pdf::where('book_id', $id)->first();

        if (!$pdf) {
            return response()->json(['error' => 'PDF not found'], 404);
        }

        $relativePath = str_replace('storage/', '', $pdf->path);
        $filePath = storage_path('app/public/' . $relativePath);

        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"',
            'Access-Control-Allow-Origin' => '*',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function getPdf(string $id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json(['error' => 'Book not found'], 404);
        }

        $pdf = Pdf::where('book_id', $id)->first();

        if (!$pdf) {
            return response()->json(['error' => 'PDF not found for this book'], 404);
        }

        return response()->json([
            'pdf_url' => url("get/pdf/{$id}"),
            'title'   => $book->title
        ]);
    }
}
