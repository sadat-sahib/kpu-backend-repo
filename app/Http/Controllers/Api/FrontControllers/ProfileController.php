<?php

namespace App\Http\Controllers\Api\FrontControllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

use App\Models\Fine;
use App\Http\Requests\StudentProfileRequest;

class ProfileController extends Controller
{
    public function showProfile(Request $request)
    {
        $user = auth()->user();

        $user->load(['userable.faculty', 'userable.department']);

        $activeReserves = $user->reserves()
            ->with(['book.category', 'book.stock', 'book.image', 'book.section', 'duration'])
            ->where("status", "active")
            ->get();

        $inactiveReserves = $user->reserves()
            ->with(['book.category', 'book.stock', 'book.image', 'book.section', 'duration'])
            ->where("status", "inactive")
            ->get();

        if (in_array($user->type, ["student", "teacher"])) {
            return response()->json([
                "user" => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'status' => $user->status,
                    'type' => $user->type,
                    'firstName' => $user->userable->firstName,
                    'lastName' => $user->userable->lastName,
                    'phone' => $user->userable->phone ?? null,
                    'image' => $user->userable->image->image ?? null,
                ],
                "reserved_books" => $activeReserves->map(fn($r) => [
                    'book_title' => $r->book->title,
                    'book_image' => asset($r->book->image->image),
                    'book_author' => $r->book->author,
                    'reserve_date' => $r->updated_at->format('Y-n-j'),
                    'return_date' => $r->duration->return_by ?? null
                ]),
                "requested_books" => $inactiveReserves->map(fn($r) => [
                    'book_title' => $r->book->title,
                    'book_image' => asset($r->book->image->image),
                    'book_author' => $r->book->author,
                    'reserve_date' => $r->updated_at->format('Y-n-j'),
                ]),
            ]);
        }

        return response()->json(['message' => 'نوع کاربر معتبر نیست'], Response::HTTP_BAD_REQUEST);
    }
    public function updateProfile(StudentProfileRequest $request)
    {
        $user = auth()->user();

        if ($user->type !== "student") {
            return response()->json(['message' => 'شما اجازه تغییر این پروفایل را ندارید'], Response::HTTP_FORBIDDEN);
        }

        if ($request->hasFile('image')) {
            $oldPath = $user->userable->image->image ?? null;
            if ($oldPath) {
                $orgPath = implode('/', array_slice(explode('/', $oldPath), 1));
                if (Storage::disk('public')->exists($orgPath)) {
                    Storage::disk('public')->delete($orgPath);
                }
            }

            $path = $request->file('image')->store('images/users', 'public');
            $user->userable->image->image = "storage/" . $path;
            $user->userable->image->save();
        }

        $user->email = $request->email;
        if ($request->password !== null) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        $user->userable->phone = $request->phone;
        $user->userable->save();

        return response()->json(['message' => 'پروفایل شما موفقانه بروزرسانی شد']);
    }
    public function deleteAccount()
    {
        $user = auth()->user();
        $fine = Fine::where('user_id', $user->id)->where('paid', 'no')->first();

        if ($fine) {
            return response()->json(['message' => 'شما اول بابد جریمه خود را پرداخت کنید'], Response::HTTP_FORBIDDEN);
        }

        $user->tokens()->delete();
        $user->userable->image()->delete();
        $user->userable()->delete();
        $user->delete();

        return response()->noContent();
    }
}
