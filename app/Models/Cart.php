<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Book;
use App\Models\User;

class Cart extends Model
{
    use HasFactory;
    protected $fillable = ['book_id','user_id'];
    public function book() {
        return $this->belongsTo(Book::class,'book_id');
    }

    public function user() {
        return $this->belongsTo(User::class,'user_id');
    }


}

