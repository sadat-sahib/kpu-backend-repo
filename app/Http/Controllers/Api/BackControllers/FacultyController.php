<?php

namespace App\Http\Controllers\Api\BackControllers;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\FacultyRequest;
use App\Http\Requests\UpdateFacultyRequest;
use App\Models\Faculty;
use Illuminate\Http\JsonResponse;

class FacultyController extends Controller
{

    public function index(): JsonResponse
    {
        $faculties = Faculty::latest()
            ->get(['id', 'name'])
            ->toArray();

        return response()->json(['data' => $faculties], Response::HTTP_OK);
    }


    public function store(FacultyRequest $request): JsonResponse
    {
        $data = $request->validated();
        $faculty = Faculty::create($data);

        return response()->json([
            'data' => $faculty->only('id', 'name')
        ], Response::HTTP_CREATED);
    }


    public function show(int $id): JsonResponse
    {
        $faculty = Faculty::find($id);
        if (!$faculty) {
            return response()->json([
                'message' => 'فاکولته پیدا نشد'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => $faculty->only('id', 'name')
        ], Response::HTTP_OK);
    }


    public function update(UpdateFacultyRequest $request, int $id): JsonResponse
    {
        $faculty = Faculty::find($id);
        if (!$faculty) {
            return response()->json([
                'message' => 'فاکولته پیدا نشد'
            ], Response::HTTP_NOT_FOUND);
        }

        $faculty->update($request->validated());

        return response()->json([
            'data' => $faculty->only('id', 'name')
        ], Response::HTTP_OK);
    }


    public function destroy(int $id): JsonResponse
    {
        $faculty = Faculty::find($id);
        if (! $faculty) {
            return response()->json([
                'message' => 'فاکولته پیدا نشد'
            ], Response::HTTP_NOT_FOUND);
        }

        $faculty->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
