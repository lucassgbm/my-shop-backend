<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
class ReviewController extends Controller {
    public function store(Request $request, Product $product): JsonResponse {
        $data = $request->validate(['rating' => 'required|integer|min:1|max:5', 'title' => 'nullable|string|max:255', 'body' => 'nullable|string']);
        $review = $product->reviews()->create($data + ['user_id' => $request->user()->id, 'is_approved' => true]);
        return response()->json($review, 201);
    }
}
