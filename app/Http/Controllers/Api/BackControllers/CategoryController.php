<?php

namespace App\Http\Controllers\Api\BackControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::latest()
            ->get(['id', 'name']);

        return response()->json([
            'data' => $categories
        ], Response::HTTP_OK);
    }

    public function show(int $id): JsonResponse
    {
        $category = Category::find($id);

        if (! $category) {
            return response()->json([
                'message' => 'کتگوری پیدا نشد'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => $category->only('id', 'name')
        ], Response::HTTP_OK);
    }

    public function store(CategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());

        return response()->json([
            'data' => $category->only('id', 'name')
        ], Response::HTTP_CREATED);
    }

    public function update(CategoryRequest $request, int $id): JsonResponse
    {
        $category = Category::find($id);

        if (! $category) {
            return response()->json([
                'message' => 'کتگوری پیدا نشد'
            ], Response::HTTP_NOT_FOUND);
        }

        $category->update($request->validated());

        return response()->json([
            'data' => $category->only('id', 'name')
        ], Response::HTTP_OK);
    }

    public function destroy(int $id): JsonResponse
    {
        $category = Category::find($id);

        if (! $category) {
            return response()->json([
                'message' => 'کتگوری پیدا نشد'
            ], Response::HTTP_NOT_FOUND);
        }

        $category->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
