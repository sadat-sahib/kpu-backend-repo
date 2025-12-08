<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Reserve;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    protected $fillable = [
        'email',
        'password',
        'status',
        'type'
    ];
    protected static function booted()
    {
        static::creating(function ($user) {
            $date = now()->format('Y-m-d');
            $increment = 0;

            DB::transaction(function () use ($date, &$increment) {
                $counter = DB::table('id_generators')
                    ->where('date', $date)
                    ->lockForUpdate()
                    ->first();

                if ($counter) {
                    $increment = $counter->last_increment + 1;
                    DB::table('id_generators')
                        ->where('date', $date)
                        ->update(['last_increment' => $increment]);
                } else {
                    $increment = 1;
                    DB::table('id_generators')->insert([
                        'date' => $date,
                        'last_increment' => $increment
                    ]);
                }
            });

            $year = now()->format('Y');
            $month = now()->format('n');
            $day = now()->format('j');
            $user->user_id = sprintf("KPU-%d-%d-%d-%05d", $year, $month, $day, $increment);
        });
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function userable()
    {
        return $this->morphTo();
    }

    public function reserves()
    {
        return $this->hasMany(Reserve::class, 'user_id');
    }

    public function carts()
    {
        return $this->hasMany(Cart::class, 'user_id');
    }

    public function fines()
    {
        return $this->hasMany(Fine::class, 'user_id');
    }
}
