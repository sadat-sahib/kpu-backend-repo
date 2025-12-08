<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Image;

class Banner extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'description', 'banner'];
    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}
