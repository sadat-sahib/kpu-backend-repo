<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Image;
use App\Models\Faculty;
use App\Models\Department;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['firstName', 'lastName', 'phone', 'nin', 'nic', 'current_residence', 'original_residence', 'fac_id', 'dep_id', 'status'];
    protected static function booted()
    {
        static::deleting(function ($model) {
            if ($model->user && !$model->user->trashed()) {
                $model->user->delete();
            }
        });

        static::restoring(function ($model) {
            if ($model->user && $model->user->trashed()) {
                $model->user->restore();
            }
        });
    }
    public function user()
    {
        return $this->morphOne(User::class, 'userable');
    }

    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class, 'fac_id');
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'dep_id');
    }
}
