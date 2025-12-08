<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'category' => CategoryResource::make($this->category),
            'faculty' => FacultyResource::make($this->department->faculty),
            'department' => DepartmentResource::make($this->department),
            'book' => BookResource::make($this)
        ];
    }
}
