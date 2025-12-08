<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class permission extends Model
{
    use HasFactory;
    
    public function employees() {
        return $this->belongsToMany(Employee::class,'employee_permiision');
    }
}

