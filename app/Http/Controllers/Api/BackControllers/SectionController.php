<?php

namespace App\Http\Controllers\Api\BackControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\SectionRequest;
use App\Models\Section;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SectionController extends Controller
{
    public function index(): JsonResponse
    {
        $sections = Section::latest()
            ->select(['id', 'section'])->get();

        return response()->json([
            'data' => $sections
        ], Response::HTTP_OK);
    }

    public function show(int $id): JsonResponse
    {
        $section = Section::find($id);

        if (! $section) {
            return response()->json([
                'message' => 'الماری پیدا نشد'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => $section->only('id', 'section')
        ], Response::HTTP_OK);
    }

    public function store(SectionRequest $request): JsonResponse
    {
        $section = Section::create($request->validated());

        return response()->json([
            'data' => $section->only('id', 'section')
        ], Response::HTTP_CREATED);
    }

    public function update(SectionRequest $request, int $id): JsonResponse
    {
        $section = Section::find($id);

        if (!$section) {
            return response()->json([
                'message' => 'الماری پیدا نشد'
            ], Response::HTTP_NOT_FOUND);
        }

        $section->update($request->validated());

        return response()->json([
            'data' => $section->only('id', 'section')
        ], Response::HTTP_OK);
    }

    public function destroy(int $id): JsonResponse
    {
        $section = Section::find($id);

        if (! $section) {
            return response()->json([
                'message' => 'الماری وجود ندارد'
            ], Response::HTTP_NOT_FOUND);
        }

        $section->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
