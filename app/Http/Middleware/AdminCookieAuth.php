<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Employee;

class AdminCookieAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->cookie('admin_token');

        if (!$token) {

            return response()->json(['message' => 'توکن وجود ندارد'], 401);
        }

        if (!str_contains($token, '|')) {
            return response()->json(['message' => 'توکن نامعتبر است'], 401);
        }

        [$id, $plainText] = explode('|', $token, 2);

        $admin = Employee::whereHas('tokens', function ($query) use ($plainText) {
            $query->where('token', hash('sha256', $plainText));
        })->first();

        if (!$admin) {

            return response()->json(['message' => 'توکن نامعتبر است'], 401);
        }

        Auth::guard('admin')->setUser($admin);



        return $next($request);
    }
}
