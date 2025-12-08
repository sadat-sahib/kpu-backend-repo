<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Duration extends Model
{
    use HasFactory;
    protected $fillable = ['res_id','borrowed_at','return_by'];

    public function reserve() {
        return $this->belongsTo(Reserve::class,'res_id');
    }
}
