<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
class WishlistController extends Controller {
    public function index(Request $request): JsonResponse {
        $items = Wishlist::with('product')->where('user_id', $request->user()->id)->get();
        return response()->json($items->map(fn($w) => [
            'id' => $w->id, 'product' => [
                'id' => $w->product->id, 'name' => $w->product->name, 'slug' => $w->product->slug,
                'price' => $w->product->price, 'primary_image' => $w->product->primary_thumb_url,
            ],
        ]));
    }
    public function toggle(Request $request, Product $product): JsonResponse {
        $existing = Wishlist::where('user_id', $request->user()->id)->where('product_id', $product->id)->first();
        if ($existing) { $existing->delete(); return response()->json(['in_wishlist' => false]); }
        Wishlist::create(['user_id' => $request->user()->id, 'product_id' => $product->id]);
        return response()->json(['in_wishlist' => true]);
    }
}
