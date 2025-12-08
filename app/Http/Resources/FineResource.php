<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class FineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($request->has('paid_users')) {
            return [
                'id' => $this->id,
                'firstName' => $this->user->userable->firstName,
                'nin' => $this->user->userable->nin,
                'nic' => $this->user->userable->nic,
                'department' => $this->user->userable->department->name,
                'faculty' => $this->user->userable->faculty->name,
                'amount' => $this->amount,
                'paid_date' => Carbon::parse($this->updated_at)->format('Y-m-d')
            ];
        }
        return [
            'firstName' => $this->user->userable->firstName,
            'nin' => $this->user->userable->nin,
            'nic' => $this->user->userable->nic,
            'department' => $this->user->userable->department->name,
            'faculty' => $this->user->userable->faculty->name,
            'book' => $this->book->title,
            'image_url' => asset($this->book->image->image),
            'fine_amount' => $this->amount,
            'issue_date' => $this->issue_date,
            'time_passed' => Carbon::parse($this->issue_date)->diffForHumans()
        ];
    }
}
