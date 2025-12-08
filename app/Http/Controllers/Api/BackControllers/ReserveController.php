<?php

namespace App\Http\Controllers\Api\BackControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\SetBookRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Reserve;
use Carbon\Carbon;

class ReserveController extends Controller
{

   protected function transform(Reserve $reserve, array $flags = []): array
   {
      $book    = $reserve->book;
      $user    = $reserve->user;
      $duration = $reserve->duration;
      $data = [];

      if (isset($flags['inactive'])) {
         $data = [
            'id'               => $reserve->id,
            'book_title'       => $book->title,
            'book_author'      => $book->author,
            'category'         => $book->category->name,
            'isbn'             => $book->isbn,
            'book_code'        => $book->code,
            'book_status'      => $book->borrow === 'no' ? 'reservable' : 'borrowable',
            'user_id'          => $user->id,
            'firstName'        => $user->userable->firstName,
            'lastName'         => $user->userable->lastName,
            'user_department'  => $user->userable->department->name,
            'nic'              => $user->userable->nic,
            'nin'              => $user->userable->nin,
            'remain_book'      => $book->stock->remain,
            'section'          => $book->section->section,
            'shelf'            => $book->section->shelf,
            'total_book'       => $book->stock->total,
            'user_status'      => $user->status,
         ];
      } elseif (isset($flags['got'])) {
         $data = [
            'id'               => $reserve->id,
            'book_title'       => $book->title,
            'book_author'      => $book->author,
            'category'         => $book->category->name,
            'return_date'      => $duration->return_by,
            'isbn'             => $book->isbn,
            'book_code'        => $book->code,
            'book_status'      => $book->borrow === 'no' ? 'reservable' : 'borrowable',
            'user_id'          => $user->id,
            'firstName'        => $user->userable->firstName,
            'lastName'         => $user->userable->lastName,
            'user_department'  => $user->userable->department->name,
            'nic'              => $user->userable->nic,
            'nin'              => $user->userable->nin,
            'remain_book'      => $book->stock->remain,
            'section'          => $book->section->section,
            'shelf'            => $book->section->shelf,
            'total_book'       => $book->stock->total,
            'user_status'      => $user->status,
         ];
      } elseif (isset($flags['reserved'])) {
         $data = [
            'id'               => $reserve->id,
            'book_title'       => $book->title,
            'book_author'      => $book->author,
            'publicationYear'  => $reserve->publicationYear,
            'category'         => $book->category->name,
            'return_date'      => $duration->return_by,
            'isbn'             => $book->isbn,
            'book_code'        => $book->code,
            'book_status'      => $book->borrow === 'no' ? 'reservable' : 'borrowable',
            'user_id'          => $user->id,
            'section'          => $book->section->section,
            'shelf'            => $book->section->shelf,
            'total_book'       => $book->stock->total,
         ];
      }
      // new flag for returning the correct reserved date of the books
       elseif (isset($flags['in_reserve'])) {
         $data = [
            'id'               => $reserve->id,
            'book_title'       => $book->title,
            'book_author'      => $book->author,
            'publicationYear'  => $reserve->publicationYear,
            'category'         => $book->category->name,
            'return_date'      => $duration->return_by,
            'reserve_date'     => $reserve->updated_at->format('Y-m-d'),
            'isbn'             => $book->isbn,
            'book_code'        => $book->code,
            'book_status'      => $book->borrow === 'no' ? 'reservable' : 'borrowable',
            'user_id'          => $user->id,
            'section'          => $book->section->section,
            'shelf'            => $book->section->shelf,
            'total_book'       => $book->stock->total,
         ];
      }
       else {
         $data = [
            'book_title'  => $book->title,
            'book_image'  => asset($book->image->image),
            'book_author' => $book->author,
            'reserve_date' => $reserve->updated_at->format('Y-n-j'),
            'return_date' => $duration->return_by,
         ];
      }

      return $data;
   }

   public function getAllReserves(): JsonResponse
   {
      $reserves = Reserve::with(['book.category', 'book.stock', 'book.section', 'user.userable.department', 'duration'])
         ->where('status', 'inactive')->get();

      $data = $reserves->map(fn($r) => $this->transform($r, ['inactive' => true]));
      return response()->json(['data' => $data], Response::HTTP_OK);
   }

   public function setBook(SetBookRequest $request, int $id): JsonResponse
   {
      $reserve = Reserve::find($id);
      if (!$reserve) {
         return response()->json(['message' => 'ID وجود ندارد'], 404);
      }

      $returnBy = Carbon::parse($request->return_by);

      $reserve->update([
         'status'      => 'active',
         'borrowed_at' => now()->toDateString(),
         'due_at'      => $returnBy->toDateString(),
      ]);

      $reserve->duration()->create([
         'res_id'      => $reserve->id,
         'borrowed_at' => now()->toDateTimeString(),
         'return_by'   => $returnBy->toDateTimeString(),
      ]);

      return response()->json(['message' => 'کتاب موفقانه رزرو شد'], 200);
   }


   public function deleteReserve(int $id): JsonResponse
   {
      $reserve = Reserve::find($id);
      if (! $reserve) {
         return response()->json(['message' => 'درخواست امانت وجود ندارد'], Response::HTTP_NOT_FOUND);
      }
      $reserve->delete();
      return response()->json(['message' => 'درخواست امانت پاک شد'], Response::HTTP_NO_CONTENT);
   }

   public function getReservedBookUserById(int $id): JsonResponse
   {
      $reserve = Reserve::with('user.userable.department')->find($id);
      if (! $reserve) {
         return response()->json(['message' => 'درخواست امانت پیدا نشد'], Response::HTTP_NOT_FOUND);
      }
      $user = $reserve->user;
      return response()->json(['data' => [
         'id' => $user->id,
         'firstName' => $user->userable->firstName,
         'lastName' => $user->userable->lastName,
         'email' => $user->email,
         'department' => $user->userable->department->name,
         'status' => $user->status,
      ]], Response::HTTP_OK);
   }

   public function getReservedBookDetailById(int $id): JsonResponse
   {
      $reserve = Reserve::with('book.category', 'book.section', 'book.stock')->find($id);
      if (! $reserve) {
         return response()->json(['message' => 'درخواست امانت پیدا نشد'], Response::HTTP_NOT_FOUND);
      }
      $b = $reserve->book;
      $data = [
         'id' => $b->id,
         'title' => $b->title,
         'author' => $b->author,
         'category' => $b->category->name,
         'remain' => $b->stock->remain,
         'section' => $b->section->section,
         'shelf' => $b->section->shelf,
      ];
      return response()->json(['data' => $data], Response::HTTP_OK);
   }

   public function usersGotBook(): JsonResponse
   {
      $reserves = Reserve::with(['book.category', 'book.stock', 'book.section', 'user.userable.department', 'duration'])
         ->where('status', 'active')->get();
      $data = $reserves->map(fn($r) => $this->transform($r, ['got' => true]));
      return response()->json(['data' => $data], Response::HTTP_OK);
   }

   public function userReturnBook(int $reserve): JsonResponse
   {
      $reserve = Reserve::find($reserve);

      if (! $reserve) {
         return response()->json(['message' => 'درخواست موجود نیست'], Response::HTTP_NOT_FOUND);
      }

      $book = $reserve->book;

      if ($book && $book->stock) {
         $book->stock->remain = $book->stock->remain + 1;
         $book->stock->save();
      }
      $reserve->delete();
      return response()->json([
         'message' => 'کتاب موفقانه پس آورده شد و تعداد موجودی به‌روز گردید',
         'remain_book' => $book->stock->remain ?? null,
      ], Response::HTTP_OK);
   }

   public function allReservedBook(): JsonResponse
   {
      $reserves = Reserve::withTrashed()
         ->with(['book.category', 'book.stock', 'book.section', 'duration'])
         ->where('status', 'active')->get();
      $data = $reserves->map(fn($r) => $this->transform($r, ['reserved' => true]));
      return response()->json(['data' => $data], Response::HTTP_OK);
   }

   public function allBookInReserve(): JsonResponse
   {
      $reserves = Reserve::with(['book.category', 'book.stock', 'book.section', 'duration'])
         ->where('status', 'active')->get();
      
      $data = $reserves->map(fn($r) => $this->transform($r, ['in_reserve' => true]));
      return response()->json(['data' => $data], Response::HTTP_OK);
   }
}
