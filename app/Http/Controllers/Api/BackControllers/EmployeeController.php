<?php

namespace App\Http\Controllers\Api\BackControllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\AdminUpdateRequest;
use App\Http\Requests\StoreEmployeeRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Http\Requests\AdminLoginRequest;
use App\Models\Employee;
use App\Models\Permission;

class EmployeeController extends Controller
{
    public function getAllEmployees()
    {
        $employees = Employee::select('id', 'name', 'email', 'role', 'type')
            ->where('type', 'employee')
            ->get()
            ->toArray();

        return response()->json(['data' => $employees], Response::HTTP_OK);
    }

    public function createEmployee(StoreEmployeeRequest $request)
    {
        $data = $request->only('name', 'email', 'password');
        $data['password'] = Hash::make($data['password']);
        $data['role'] = 'simple';
        $data['type'] = 'employee';

        $employee = Employee::create($data);

        return response()->json([
            'message' => 'کارمند با موفقیت اضافه شد',
            'data'    => $employee->only('id', 'name', 'email', 'role', 'type')
        ], Response::HTTP_CREATED);
    }

    public function setPermission(Request $request)
    {
        $employee = Employee::find($request->employeeId);
        if (! $employee) {
            return response()->json(['message' => 'کارمند پیدا نشد'], Response::HTTP_NOT_FOUND);
        }

        $employee->permissions()->sync($request->permissions);

        return response()->json(['message' => 'دسترسی‌ها با موفقیت بروزرسانی شد'], Response::HTTP_OK);
    }

public function login(AdminLoginRequest $request)
{
    $user = Employee::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['message' => 'کاربری با این ایمیل پیدا نشد'], 404);
    }

    if (!Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'رمز عبور اشتباه است'], 401);
    }

    // create token
    $token = $user->createToken('admin_token')->plainTextToken;

    // determine cookie settings
    $cookieDomain = env('APP_ENV') === 'production' 
        ? '.kpu-backend-repo.onrender.com' // your backend domain
        : null;

    $cookieSameSite = env('APP_ENV') === 'production' ? 'None' : 'Lax';
    $cookieSecure = env('APP_ENV') === 'production';

    $cookie = cookie(
        'admin_token',
        $token,
        60 * 24,
        '/',
        $cookieDomain,
        $cookieSecure,
        true,  // httpOnly
        false,
        $cookieSameSite
    );

    return response()->json([
        'message' => 'ورود با موفقیت انجام شد',
        'data' => $user->only('id', 'name', 'email', 'role', 'type'),
    ], 200)->withCookie($cookie);
}


    public function logout(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        if ($admin) {
            $admin->tokens()->delete();
        }

        $cookie = Cookie::forget('admin_token');

        return response()->json([
            'message' => 'خروج با موفقیت انجام شد'
        ])->withCookie($cookie);
    }
    
    public function update(AdminUpdateRequest $request, $id)
    {
        $employee = Employee::find($id);
        if (! $employee) {
            return response()->json(['message' => 'کارمند پیدا نشد'], Response::HTTP_NOT_FOUND);
        }

        $updateData = $request->only('name', 'email', 'password');
        $updateData['password'] = Hash::make($updateData['password']);

        $employee->update($updateData);

        return response()->json(['message' => 'کارمند با موفقیت بروزرسانی شد'], Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json(['message' => 'کارمند پیدا نشد'], Response::HTTP_NOT_FOUND);
        }

        $employee->delete();
        return response()->json(['message' => 'کارمند با موفقیت پاک شد'], Response::HTTP_NO_CONTENT);
    }
}
