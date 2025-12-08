<?php

namespace App\Http\Controllers\Api\BackControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Pdf;
use App\Models\Section;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class BookController extends Controller
{
    private const COMMON_RELATIONS = [
        'department.faculty',
        'section',
        'stock',
        'image',
        'pdf'
    ];

    private const COMMON_SELECT = [
        'id',
        'title',
        'author',
        'publisher',
        'publicationYear',
        'isbn',
        'code',
        'format',
        'edition',
        'lang',
        'translator',
        'description',
        'dep_id',
        'sec_id',
        'cat_id',
        'borrow',
        'created_at',
        'updated_at'
    ];

    public function index(): JsonResponse
    {
        $paginator = Book::select(self::COMMON_SELECT)
            ->with(self::COMMON_RELATIONS)
            ->latest('id')
            ->cursorPaginate(20);

        $paginator->getCollection()->transform(function ($book) {
            return $this->formatBookResponse($book);
        });

        return response()->json($paginator);
    }

    public function store(StoreBookRequest $request): JsonResponse
    {
        $data = $request->validated();

        DB::beginTransaction();
        try {
            $book = $this->handleBookCreation($data);
            $this->handleFileUploads($request, $book);

            DB::commit();
            return response()->json([
                'message' => 'کتاب موفقانه ثبت شد',
                'data' => $this->formatBookResponse($book, 'light')
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('مشکل در ثبت کتاب  مجدد تلاش بکنید', $e);
        }
    }

    public function show(Book $book): JsonResponse
    {
        $book->load(self::COMMON_RELATIONS);
        return response()->json([
            'data' => $this->formatBookResponse($book)
        ]);
    }

    public function update(UpdateBookRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();

        $book = Book::find($id);
        if (!$book) {
            return response()->json([
                'message' => 'کتاب یافت نشد'
            ], Response::HTTP_NOT_FOUND);
        }

        DB::beginTransaction();
        try {
            $this->handleFormatChanges($book, $data);
            $this->handleFileUpdates($request, $book);
            $book->update($data);

            DB::commit();
            return response()->json([
                'message' => 'کتاب با موفقیت تجدید شد',
                'data' => $this->formatBookResponse($book->fresh(), 'light')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('خطا در تجدید کتاب', $e);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        $book = Book::find($id);
        if (!$book) {
            return response()->json([
                'message' => 'کتاب یافت نشد'
            ], Response::HTTP_NOT_FOUND);
        }

        DB::beginTransaction();
        try {
            $this->deleteBookAssets($book);
            $book->delete();

            DB::commit();
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('خطا در حذف کتاب', $e);
        }
    }

    private function formatBookResponse(Book $book, string $detailLevel = 'full'): array
    {
        $response = [
            'id' => $book->id,
            'title' => $book->title,
            'author' => $book->author,
            'publisher' => $book->publisher,
            'publicationYear' => $book->publicationYear,
            'lang' => $book->lang,
            'edition' => $book->edition,
        ];

        if ($detailLevel === 'full') {
            $response = array_merge($response, [
                'translator' => $book->translator,
                'isbn' => $book->isbn,
                'description' => $book->description,
                'format' => $book->format,
                'category' => $book->category->name ?? null,
                'department' => $book->department->name ?? null,
                'faculty' => optional($book->department->faculty)->name,
                'created_at' => $book->created_at->toDateTimeString(),
            ]);

            $this->addFileUrls($response, $book);
            $this->addPhysicalBookFields($response, $book);
        } elseif ($detailLevel === 'light') {
            $response['category'] = $book->category->name ?? null;
        }

        return $response;
    }


    private function addFileUrls(array &$response, Book $book): void
    {
        if ($book->image) {
            $response['image_url'] = asset($book->image->image);
        }

        if ($book->pdf) {
            $response['pdf_url'] = asset($book->pdf->path);
        }
    }

    private function addPhysicalBookFields(array &$response, Book $book): void
    {
        if ($book->format === 'pdf') return;

        $response['code'] = $book->code;
        $response['borrow'] = $book->borrow;

        if ($book->section) {
            $response['section'] = $book->section->section;
            $response['shelf'] = $book->section->shelf;
        }

        $response['stock'] = [
            'total' => $book->stock->total ?? 0,
            'remain' => $book->stock->remain ?? 0,
            'status' => $book->stock->status ?? 'not_exist'
        ];
    }

    private function handleBookCreation(array $data): Book
    {
        if ($data['format'] === 'pdf') {
            $data = array_merge($data, [
                'borrow' => 'no',
                'sec_id' => null,
                'shelf' => null,
                'total' => null,
                'code' => null,
            ]);
        }

        $book = Book::create($data);

        if ($data['format'] !== 'pdf') {
            Section::where('id', $data['sec_id'])->update(['shelf' => $data['shelf']]);
            $book->stock()->create([
                'total' => $data['total'],
                'remain' => $data['total'],
                'status' => 'exist'
            ]);
        }

        return $book;
    }

    private function handleFileUploads($request, Book $book): void
    {
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images/books', 'public');
            $book->image()->create(['image' => "storage/{$path}"]);
        }

        if (($book->format === 'pdf' || $book->format === 'both') && $request->hasFile('pdf')) {
            $pdfPath = $request->file('pdf')->store('pdfs', 'public');
            $book->pdf()->create(['path' => "storage/{$pdfPath}"]);
        }
    }

    private function handleFormatChanges(Book $book, array &$data): void
    {
        $originalFormat = $book->format;
        $newFormat = $data['format'] ?? $originalFormat;

        if ($newFormat === 'pdf' && $originalFormat !== 'pdf') {
            $data = array_merge($data, [
                'sec_id' => null,
                'shelf' => null,
                'total' => null,
                'code' => null,
                'borrow' => 'no'
            ]);
            $book->stock()->delete();
        }

        if ($newFormat !== 'pdf' && $originalFormat === 'pdf') {
            $data['borrow'] ??= 'yes';
            $book->stock()->create([
                'total' => $data['total'],
                'remain' => $data['total'],
                'status' => 'exist'
            ]);
        }
    }

    private function handleFileUpdates($request, Book $book): void
    {
        if ($request->hasFile('image')) {
            $this->deleteFile($book->image?->image);
            $book->image()->delete();
            $path = $request->file('image')->store('images/books', 'public');
            $book->image()->create(['image' => "storage/{$path}"]);
        }

        if ($request->hasFile('pdf') || $book->format !== 'pdf') {
            $this->deleteFile($book->pdf?->path);
            $book->pdf()->delete();
        }

        if (($book->format === 'pdf' || $book->format === 'both') && $request->hasFile('pdf')) {
            $pdfPath = $request->file('pdf')->store('pdfs', 'public');
            $book->pdf()->create(['path' => "storage/{$pdfPath}"]);
        }
    }

    private function deleteBookAssets(Book $book): void
    {
        $this->deleteFile($book->image?->image);
        $this->deleteFile($book->pdf?->path);
        $book->image()->delete();
        $book->pdf()->delete();
        $book->stock()->delete();
    }

    private function deleteFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($cleanPath = str_replace('storage/', '', $path))) {
            Storage::disk('public')->delete($cleanPath);
        }
    }

    private function errorResponse(string $message, \Exception $e): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'error' => $e->getMessage()
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function getPdf(string $id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json(['error' => 'کتاب پیدا نشد'], 404);
        }

        $pdf = Pdf::where('book_id', $id)->first();

        if (!$pdf) {
            return response()->json(['error' => 'برای این کتاب پیدا نشد PDF'], 404);
        }

        $relativePath = str_replace('storage/', '', $pdf->path);
        $publicUrl = asset('storage/' . $relativePath);

        return response()->json(['pdf_url' => $publicUrl]);
    }
}
