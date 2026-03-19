<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Category::where('is_active', true)
                ->withCount(['products' => fn($q) => $q->active()])
                ->orderBy('name')
                ->get()
                ->map(fn($c) => [
                    'id'             => $c->id,
                    'name'           => $c->name,
                    'slug'           => $c->slug,
                    'products_count' => $c->products_count,
                ])
        );
    }

    public function show(string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)->where('is_active', true)->firstOrFail();
        return response()->json([
            'id'          => $category->id,
            'name'        => $category->name,
            'slug'        => $category->slug,
            'description' => $category->description,
        ]);
    }
}
