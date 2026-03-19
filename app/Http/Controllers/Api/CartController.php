<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    private function getCart(Request $request): array
    {
        return session('cart', ['items' => [], 'coupon' => null]);
    }

    private function saveCart(Request $request, array $cart): void
    {
        session(['cart' => $cart]);
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->buildSummary($this->getCart($request)));
    }

    public function addItem(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id'  => 'required|exists:products,id',
            'variant_id'  => 'nullable|exists:product_variants,id',
            'quantity'    => 'required|integer|min:1|max:10',
        ]);

        $product = Product::findOrFail($data['product_id']);
        $variant = $data['variant_id'] ? ProductVariant::findOrFail($data['variant_id']) : null;

        $cart  = $this->getCart($request);
        $key   = $data['product_id'] . '-' . ($data['variant_id'] ?? '0');
        $price = $variant?->price ?? $product->price;

        if (isset($cart['items'][$key])) {
            $cart['items'][$key]['quantity'] += $data['quantity'];
        } else {
            $cart['items'][$key] = [
                'key'        => $key,
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'name'       => $product->name,
                'size'       => $variant?->size,
                'image'      => $product->primary_thumb_url,
                'price'      => $price,
                'quantity'   => $data['quantity'],
            ];
        }

        $this->saveCart($request, $cart);
        return response()->json($this->buildSummary($cart));
    }

    public function updateItem(Request $request, string $id): JsonResponse
    {
        $data = $request->validate(['quantity' => 'required|integer|min:0|max:10']);
        $cart = $this->getCart($request);

        if ($data['quantity'] === 0) {
            unset($cart['items'][$id]);
        } elseif (isset($cart['items'][$id])) {
            $cart['items'][$id]['quantity'] = $data['quantity'];
        }

        $this->saveCart($request, $cart);
        return response()->json($this->buildSummary($cart));
    }

    public function removeItem(Request $request, string $id): JsonResponse
    {
        $cart = $this->getCart($request);
        unset($cart['items'][$id]);
        $this->saveCart($request, $cart);
        return response()->json($this->buildSummary($cart));
    }

    public function applyCoupon(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string']);
        $coupon = Coupon::where('code', strtoupper($request->code))->first();

        if (!$coupon || !$coupon->isValid()) {
            return response()->json(['error' => 'Cupom inválido ou expirado.'], 422);
        }

        $cart = $this->getCart($request);
        $cart['coupon'] = ['id' => $coupon->id, 'code' => $coupon->code, 'type' => $coupon->type, 'value' => $coupon->value];
        $this->saveCart($request, $cart);

        return response()->json($this->buildSummary($cart));
    }

    public function removeCoupon(Request $request): JsonResponse
    {
        $cart = $this->getCart($request);
        $cart['coupon'] = null;
        $this->saveCart($request, $cart);
        return response()->json($this->buildSummary($cart));
    }

    public function summary(Request $request): JsonResponse
    {
        return response()->json($this->buildSummary($this->getCart($request)));
    }

    public function buildSummary(array $cart): array
    {
        $items    = array_values($cart['items'] ?? []);
        $subtotal = collect($items)->sum(fn($i) => $i['price'] * $i['quantity']);
        $discount = 0;

        if (!empty($cart['coupon'])) {
            $coupon   = Coupon::find($cart['coupon']['id']);
            $discount = $coupon ? $coupon->calculateDiscount($subtotal) : 0;
        }

        return [
            'items'    => $items,
            'coupon'   => $cart['coupon'] ?? null,
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'total'    => round($subtotal - $discount, 2),
            'count'    => collect($items)->sum('quantity'),
        ];
    }
}
