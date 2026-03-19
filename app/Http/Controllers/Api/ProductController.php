<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'variants'])
            ->active()
            ->withCount('reviews');

        if ($request->category) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }

        if ($request->search) {
            $query->where('name', 'ilike', "%{$request->search}%");
        }

        if ($request->min_price) $query->where('price', '>=', $request->min_price);
        if ($request->max_price) $query->where('price', '<=', $request->max_price);

        $sort = $request->sort ?? 'created_at';
        $dir  = $request->dir  ?? 'desc';
        $query->orderBy($sort, $dir);

        $products = $query->paginate($request->per_page ?? 12);

        return response()->json([
            'data' => $products->getCollection()->map(fn($p) => $this->productCard($p)),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'total'        => $products->total(),
            ],
        ]);
    }

    public function featured(): JsonResponse
    {
        $products = Product::with('category')->active()->featured()->take(8)->get();
        return response()->json($products->map(fn($p) => $this->productCard($p)));
    }

    public function show(string $slug): JsonResponse
    {
        $product = Product::with(['category', 'variants', 'reviews.user'])
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json([
            'id'               => $product->id,
            'name'             => $product->name,
            'slug'             => $product->slug,
            'description'      => $product->description,
            'price'            => $product->price,
            'compare_price'    => $product->compare_price,
            'is_on_sale'       => $product->is_on_sale,
            'discount_percent' => $product->discount_percent,
            'average_rating'   => $product->average_rating,
            'weight'           => $product->weight,
            'width'            => $product->width,
            'height'           => $product->height,
            'length'           => $product->length,
            'category'         => ['id' => $product->category->id, 'name' => $product->category->name, 'slug' => $product->category->slug],
            'variants'         => $product->variants->map(fn($v) => [
                'id'    => $v->id,
                'size'  => $v->size,
                'color' => $v->color,
                'stock' => $v->stock,
                'price' => $v->price,
            ]),
            'images'           => $product->all_images,
            'primary_image'    => $product->primary_image_url,
            'reviews'          => $product->reviews->where('is_approved', true)->map(fn($r) => [
                'id'         => $r->id,
                'rating'     => $r->rating,
                'title'      => $r->title,
                'body'       => $r->body,
                'user_name'  => $r->user->name,
                'created_at' => $r->created_at->format('d/m/Y'),
            ]),
        ]);
    }

    private function productCard(Product $p): array
    {
        return [
            'id'               => $p->id,
            'name'             => $p->name,
            'slug'             => $p->slug,
            'price'            => $p->price,
            'compare_price'    => $p->compare_price,
            'is_on_sale'       => $p->is_on_sale,
            'discount_percent' => $p->discount_percent,
            'primary_image'    => $p->primary_image_url,
            'primary_thumb'    => $p->primary_thumb_url,
            'category'         => $p->category?->name,
            'average_rating'   => $p->average_rating,
            'reviews_count'    => $p->reviews_count ?? 0,
        ];
    }
}
