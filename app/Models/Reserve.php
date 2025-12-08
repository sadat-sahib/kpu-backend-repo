<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Book;

class Reserve extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['book_id', 'user_id', 'user_type', 'status'];
    public function duration()
    {
        return $this->hasOne(Duration::class, 'res_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function reserve()
    {
        return $this->hasOne(Fine::class, 'res_id');
    }
}
