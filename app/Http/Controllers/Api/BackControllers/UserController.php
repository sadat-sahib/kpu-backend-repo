<?php

namespace App\Http\Controllers\Api\BackControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Faculty;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends Controller
{
    public function getAllUsers(): JsonResponse
    {
        $users = User::with(['userable.department', 'userable.faculty'])->latest()->get();

        $data = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'firstName' => $user->userable->firstName ?? null,
                'lastName' => $user->userable->lastName ?? null,
                'email' => $user->email,
                'phone' => $user->userable->phone ?? null,
                'nin' => $user->userable->nin ?? null,
                'nic' => $user->userable->nic ?? null,
                'current_residence' => $user->userable->current_residence ?? null,
                'original_residence' => $user->userable->original_residence ?? null,
                'faculty' => $user->userable->faculty->name ?? null,
                'department' => $user->userable->department->name ?? null,
                'status' => $user->status,
                'type' => $user->type,
                'created_at' => $user->userable->created_at ?? null,
            ];
        });

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    public function userCreate(RegisterRequest $request): JsonResponse
    {
        Faculty::findOrFail($request->fac_id);
        Department::findOrFail($request->dep_id);

        $profile = match (strtolower($request->type)) {
            'teacher' => Teacher::create($request->only([
                'firstName',
                'lastName',
                'phone',
                'nin',
                'nic',
                'current_residence',
                'original_residence',
                'fac_id',
                'dep_id'
            ])),
            'student' => Student::create($request->only([
                'firstName',
                'lastName',
                'phone',
                'nin',
                'nic',
                'current_residence',
                'original_residence',
                'fac_id',
                'dep_id'
            ])),
            default => null
        };

        if (!$profile) {
            return response()->json(['message' => 'Invalid user type'], Response::HTTP_BAD_REQUEST);
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images/users', 'public');
            $profile->image()->create(['image' => "storage/{$path}"]);
        }

        $status = 'inactive';
        if (strtolower($request->type) === 'student') {
            $status = $request->has('status') ? $request->status : 'inactive';
        }

        $user = $profile->user()->create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => $status,
            'type' => $request->type,
        ]);

        return response()->json([
            'data' => [
                'id' => $user->id,
                'userId' => $user->user_id,
                'firstName' => $profile->firstName,
                'lastName' => $profile->lastName,
                'email' => $user->email,
            ]
        ], Response::HTTP_CREATED);
    }



    public function userEdit(string $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['message' => 'یوسر پیدا نشد'], Response::HTTP_NOT_FOUND);
        }

        $user->load(['userable.department', 'userable.faculty']);

        return response()->json(['data' => [
            'id' => $user->id,
            'firstName' => $user->userable->firstName ?? null,
            'lastName' => $user->userable->lastName ?? null,
            'email' => $user->email,
            'phone' => $user->userable->phone ?? null,
            'nin' => $user->userable->nin ?? null,
            'nic' => $user->userable->nic ?? null,
            'current_residence' => $user->userable->current_residence ?? null,
            'original_residence' => $user->userable->original_residence ?? null,
            'faculty' => $user->userable->faculty->name ?? null,
            'department' => $user->userable->department->name ?? null,
            'status' => $user->status,
            'type' => $user->type,
            'created_at' => $user->userable->created_at ?? null,
        ]], Response::HTTP_OK);
    }


    public function userUpdate(UpdateUserRequest $request, string $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'یوسر پیدا نشد'], Response::HTTP_NOT_FOUND);
        }

        if (strtolower($request->type) !== strtolower($user->type)) {
            return response()->json(['message' => 'تغییر نوع کاربر مجاز نیست'], Response::HTTP_BAD_REQUEST);
        }

        if (! Faculty::where('id', $request->fac_id)->exists()) {
            return response()->json(['message' => 'Faculty not found'], Response::HTTP_NOT_FOUND);
        }

        if (! Department::where('id', $request->dep_id)->exists()) {
            return response()->json(['message' => 'Department not found'], Response::HTTP_NOT_FOUND);
        }

        $user->userable->update($request->validated());

        if ($request->hasFile('image')) {
            $old = $user->userable->image;
            if ($old) {
                Storage::disk('public')->delete(str_replace('storage/', '', $old->image));
                $old->delete();
            }

            $path = $request->file('image')->store('images/users', 'public');
            $user->userable->image()->create(['image' => "storage/{$path}"]);
        }

        $user->update(['email' => $request->email]);

        return response()->json([
            'data' => [
                'id' => $user->id,
                'userId' => $user->user_id,
                'firstName' => $user->userable->firstName ?? null,
                'lastName' => $user->userable->lastName ?? null,
                'email' => $user->email,
            ]
        ], Response::HTTP_OK);
    }


    public function destroy(string $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'یوسر پیدا نشد'], Response::HTTP_NOT_FOUND);
        }
        if ($user->userable) {
            if ($user->userable->image) {
                Storage::disk('public')->delete(
                    str_replace('storage/', '', $user->userable->image->image)
                );
            }
            $user->userable()->delete();
        }
        $user->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function getInactivatedStudents(): JsonResponse
    {
        $students = User::with(['userable.department', 'userable.faculty'])
            ->where('status', 'inactive')->where('type', 'student')->get();

        $data = $students->map(function ($user) {
            return [
                'id' => $user->id,
                'firstName' => $user->userable->firstName ?? null,
                'lastName' => $user->userable->lastName ?? null,
                'email' => $user->email,
                'status' => $user->status,
                'type' => $user->type,
            ];
        });

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    public function getInactivatedTeachers(): JsonResponse
    {
        $teachers = User::with(['userable.department', 'userable.faculty'])
            ->where('status', 'inactive')->where('type', 'teacher')->get();

        $data = $teachers->map(function ($user) {
            return [
                'id' => $user->id,
                'firstName' => $user->userable->firstName ?? null,
                'lastName' => $user->userable->lastName ?? null,
                'email' => $user->email,
                'status' => $user->status,
                'type' => $user->type,
            ];
        });

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    public function getInactivatedUserDetail(string $id): JsonResponse
    {
        $user = User::with(['userable.department', 'userable.faculty'])
            ->where('id', $id)
            ->where('status', 'inactive')
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'یوسر غیر فعال پیدا نشد '
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => [
            'id' => $user->id,
            'firstName' => $user->userable->firstName ?? null,
            'lastName' => $user->userable->lastName ?? null,
            'email' => $user->email,
            'phone' => $user->userable->phone ?? null,
            'nin' => $user->userable->nin ?? null,
            'nic' => $user->userable->nic ?? null,
            'current_residence' => $user->userable->current_residence ?? null,
            'original_residence' => $user->userable->original_residence ?? null,
            'faculty' => $user->userable->faculty->name ?? null,
            'department' => $user->userable->department->name ?? null,
            'status' => $user->status,
            'type' => $user->type,
            'created_at' => $user->userable->created_at ?? null,
        ]], Response::HTTP_OK);
    }



    public function activateUserById(string $id): JsonResponse
    {
        $user = User::with(['userable.department', 'userable.faculty'])
            ->where('id', $id)
            ->where('status', 'inactive')
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'یوسر قبلا اکتیو شده است'
            ], Response::HTTP_NOT_FOUND);
        }

        $user->status = 'active';
        $user->save();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'firstName' => $user->userable->firstName ?? null,
                'lastName' => $user->userable->lastName ?? null,
                'email' => $user->email,
                'phone' => $user->userable->phone ?? null,
                'nin' => $user->userable->nin ?? null,
                'nic' => $user->userable->nic ?? null,
                'status' => $user->status,
                'type' => $user->type,
            ]
        ], Response::HTTP_OK);
    }



    public function getActivatedStudents(): JsonResponse
    {
        $students = User::with(['userable.department', 'userable.faculty'])
            ->where('status', 'active')->where('type', 'student')->get();

        $data = $students->map(function ($user) {
            return [
                'id' => $user->id,
                'firstName' => $user->userable->firstName ?? null,
                'lastName' => $user->userable->lastName ?? null,
                'email' => $user->email,

                'status' => $user->status,
                'type' => $user->type,

            ];
        });

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    public function getActivatedTeachers(): JsonResponse
    {
        $teachers = User::with(['userable.department', 'userable.faculty'])
            ->where('status', 'active')->where('type', 'teacher')->get();

        $data = $teachers->map(function ($user) {
            return [
                'id' => $user->id,
                'firstName' => $user->userable->firstName ?? null,
                'lastName' => $user->userable->lastName ?? null,
                'email' => $user->email,
                'phone' => $user->userable->phone ?? null,
                'nin' => $user->userable->nin ?? null,
                'nic' => $user->userable->nic ?? null,
                'current_residence' => $user->userable->current_residence ?? null,
                'original_residence' => $user->userable->original_residence ?? null,
                'faculty' => $user->userable->faculty->name ?? null,
                'department' => $user->userable->department->name ?? null,
                'status' => $user->status,
                'type' => $user->type,
                'created_at' => $user->userable->created_at ?? null,
            ];
        });

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    public function getActivatedUserById(string $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user || $user->status !== 'active') {
            return response()->json([
                'message' => 'یوسر یا اکتیو نیست یا وجود ندارد'
            ], Response::HTTP_NOT_FOUND);
        }

        $user->load(['userable.department', 'userable.faculty']);

        return response()->json(['data' => [
            'id' => $user->id,
            'firstName' => $user->userable->firstName ?? null,
            'lastName' => $user->userable->lastName ?? null,
            'email' => $user->email,
            'phone' => $user->userable->phone ?? null,
            'nin' => $user->userable->nin ?? null,
            'nic' => $user->userable->nic ?? null,
            'current_residence' => $user->userable->current_residence ?? null,
            'original_residence' => $user->userable->original_residence ?? null,
            'faculty' => $user->userable->faculty->name ?? null,
            'department' => $user->userable->department->name ?? null,
            'status' => $user->status,
            'type' => $user->type,
            'created_at' => $user->userable->created_at ?? null,
        ]], Response::HTTP_OK);
    }
}
