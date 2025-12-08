<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Book;

class Stock extends Model
{
    use HasFactory;
    protected $fillable = ['book_id','total','remain','status'];
    public function book() {
        return $this->belongsTo(Book::class,'book_id');
    }
}

