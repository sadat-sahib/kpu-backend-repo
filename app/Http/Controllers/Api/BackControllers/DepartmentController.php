<?php

namespace App\Http\Controllers\Api\BackControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepartmentRequest;
use App\Models\Department;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;

class DepartmentController extends Controller
{
    public function index(): JsonResponse
    {
        $departments = Department::latest()
            ->get(['id', 'name']);

        return response()->json([
            'data' => $departments
        ], Response::HTTP_OK);
    }

    public function show(int $id): JsonResponse
    {
        $department = Department::find($id);

        if (! $department) {
            return response()->json([
                'message' => 'دیپارتمنت پیدا نشد'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => $department->only('id', 'name')
        ], Response::HTTP_OK);
    }

    public function store(DepartmentRequest $request): JsonResponse
    {
        $department = Department::create($request->validated());

        return response()->json([
            'data' => $department->only('id', 'name')
        ], Response::HTTP_CREATED);
    }

    public function update(DepartmentRequest $request, int $id): JsonResponse
    {
        $department = Department::find($id);

        if (! $department) {
            return response()->json([
                'message' => 'دیپارتمنت وجود ندارد'
            ], Response::HTTP_NOT_FOUND);
        }

        $department->update($request->validated());

        return response()->json([
            'data' => $department->only('id', 'name')
        ], Response::HTTP_OK);
    }

    public function destroy(int $id): JsonResponse
    {
        $department = Department::find($id);

        if (! $department) {
            return response()->json([
                'message' => 'دیپارتمنت وجود ندارد'
            ], Response::HTTP_NOT_FOUND);
        }

        $department->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
