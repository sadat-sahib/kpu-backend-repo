<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ReserveResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($request->has('get_inactive_users')) {
            return [
                'id' => $this->id,
                'book_title' => $this->book->title,
                'book_author' => $this->book->author,
                'category' => $this->book->category->name,

                'isbn' => $this->book->isbn,
                'book_code' => $this->book->code,
                'book_status' => $this->book->borrow  == "no" ? "reservable" : "borrowable",
                'user_id' => $this->user->id,
                'firstName' => $this->user->userable->firstName,
                'lastName' => $this->user->userable->lastName,
                'user_department' => $this->user->userable->department->name,
                'nic' => $this->user->userable->nic,
                'nin' => $this->user->userable->nin,
                'remain_book' => $this->book->stock->remain,
                'section' => $this->book->section->section,
                'shelf' => $this->book->section->shelf,
                'total_book' => $this->book->stock->total,
                'user_status' => $this->user->status,
            ];
        } else if ($request->has('get_users_got_book')) {
            return [
                'id' => $this->id,
                'book_title' => $this->book->title,
                'book_author' => $this->book->author,
                'category' => $this->book->category->name,
                'return_date' => $this->duration->return_by,
                'isbn' => $this->book->isbn,
                'book_code' => $this->book->code,
                'book_status' => $this->book->borrow  == "no" ? "reservable" : "borrowable",
                'user_id' => $this->user->id,
                'firstName' => $this->user->userable->firstName,
                'lastName' => $this->user->userable->lastName,
                'user_department' => $this->user->userable->department->name,
                'nic' => $this->user->userable->nic,
                'nin' => $this->user->userable->nin,
                'remain_book' => $this->book->stock->remain,
                'section' => $this->book->section->section,
                'shelf' => $this->book->section->shelf,
                'total_book' => $this->book->stock->total,
                'user_status' => $this->user->status,
            ];
        } else if ($request->has('get_reserved_book')) {
            return [
                'id' => $this->id,
                'book_title' => $this->book->title,
                'book_author' => $this->book->author,
                'publicationYear' => $this->publicationYear,
                'category' => $this->book->category->name,
                'return_date' => $this->duration->return_by,
                'isbn' => $this->book->isbn,
                'book_code' => $this->book->code,
                'book_status' => $this->book->borrow  == "no" ? "reservable" : "borrowable",
                'user_id' => $this->user->id,
                'section' => $this->book->section->section,
                'shelf' => $this->book->section->shelf,
                'total_book' => $this->book->stock->total,
            ];
        } else {
            return [
                'book_title' => $this->book->title,
                'book_image' => asset($this->book->image->image),
                'book_author' => $this->book->author,
                'reserve_date' => $this->updated_at->format('Y-n-j'),
                'return_date' => $this->duration->return_by
            ];
        }
    }
}
