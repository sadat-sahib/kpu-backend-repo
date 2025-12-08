<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Book;

class Section extends Model
{
    use HasFactory;
    protected $fillable = ['section','shelf','total'];

    public function books() {
        return $this->hasMany(Book::class,'sec_id');
    }
}
