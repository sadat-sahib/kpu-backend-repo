<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proifle extends Model
{
    use HasFactory;

    protected $fillable = ['teacher_id','whatsApp','facebook','gitHub','linkedIn'];

    public function teacher() {
        return $this->belongsTo(Teacher::class,'teacher_id');
    }
}
