<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Employee;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $admins = config('admins');

        foreach ($admins as $kind => $cfg) {
            Employee::updateOrCreate(
                [
                    'email' => $cfg['email'],
                    'type'  => $cfg['type'],
                ],
                [
                    'name'     => ucfirst($kind) . ' Admin',
                    'role'     => $cfg['role'],
                    'password' => Hash::make($cfg['password']),
                ]
            );
        }
    }
}
