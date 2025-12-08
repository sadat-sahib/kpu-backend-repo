<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Faculty;
use App\Models\User;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name','fac_id'];

    public function faculty() {
        return $this->belongsTo(Faculty::class,'fac_id');
    }

    public function users() {
        return $this->hasMany(User::class,'dep_id');
    }

    public function books() {
        return $this->hasMany(Book::class, 'dep_id');
    }

}
