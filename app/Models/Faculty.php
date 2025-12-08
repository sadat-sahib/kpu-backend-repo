<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Department;
use App\Models\User;

class Faculty extends Model
{
    use HasFactory;

    protected $fillable = ['name'];
    public function departments() {
        return $this->hasMany(Department::class,'fac_id');
    }

    public function users() {
        return $this->hasMany(User::class,'fac_id');
    }

    public function books() {
        return $this->hasMany(Book::class ,'fac_id');
    }
}
