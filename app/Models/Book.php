<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Section;
use App\Models\Image;
use App\Models\Stock;
use App\Models\Reserve;
use Illuminate\Support\Facades\Storage;

class Book extends Model

{

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($book) {
            if ($book->image) {
                Storage::disk('public')->delete(str_replace('storage/', '', $book->image->image));
                $book->image()->delete();
            }

            if ($book->pdf) {
                Storage::disk('public')->delete(str_replace('storage/', '', $book->pdf->path));
                $book->pdf()->delete();
            }
        });
    }
    use HasFactory;
    protected $fillable = [
        'title',
        'author',
        'publisher',
        'publicationYear',
        'lang',
        'edition',
        'translator',
        'isbn',
        'description',
        'cat_id',
        'dep_id',
        'sec_id',
        'format',
        'borrow',
        'code',
    ];




    public function section()
    {
        return $this->belongsTo(Section::class, 'sec_id');
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class, 'fac_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'dep_id');
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'cat_id');
    }

    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function stock()
    {
        return $this->hasOne(Stock::class, 'book_id');
    }

    public function reserves()
    {
        return $this->hasMany(Reserve::class, 'book_id');
    }

    public function carts()
    {
        return $this->hasOne(Cart::class, 'book_id', 'id');
    }

    public function fines()
    {
        return $this->hasMany(Book::class, 'book_id');
    }

    public function pdf()
    {
        return $this->hasOne(Pdf::class);
    }
}
