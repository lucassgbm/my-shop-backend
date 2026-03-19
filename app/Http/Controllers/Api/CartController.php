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
    /**
     * Valida e recalcula o carrinho recebido do frontend.
     * Carrinho é gerenciado pelo Zustand — backend só valida preços e cupons.
     */
    public function summary(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items'              => 'required|array',
            'items.*.key'        => 'required|string',
            'items.*.product_id' => 'required|integer',
            'items.*.variant_id' => 'nullable|integer',
            'items.*.quantity'   => 'required|integer|min:1',
            'coupon_code'        => 'nullable|string',
        ]);

        $items    = [];
        $subtotal = 0;

        foreach ($data['items'] as $item) {
            $product = Product::find($item['product_id']);
            if (!$product || !$product->is_active) continue;

            $variant = $item['variant_id'] ? ProductVariant::find($item['variant_id']) : null;
            $price   = (float) ($variant?->price ?? $product->price);
            $qty     = (int) $item['quantity'];

            $items[] = [
                'key'        => $item['key'],
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'name'       => $product->name,
                'size'       => $variant?->size,
                'image'      => $product->primary_thumb_url,
                'price'      => $price,
                'quantity'   => $qty,
            ];

            $subtotal += $price * $qty;
        }

        $discount = 0;
        $coupon   = null;

        if (!empty($data['coupon_code'])) {
            $couponModel = Coupon::where('code', strtoupper($data['coupon_code']))->first();
            if ($couponModel && $couponModel->isValid()) {
                $discount = $couponModel->calculateDiscount($subtotal);
                $coupon   = [
                    'id'    => $couponModel->id,
                    'code'  => $couponModel->code,
                    'type'  => $couponModel->type,
                    'value' => $couponModel->value,
                ];
            }
        }

        return response()->json([
            'items'    => $items,
            'coupon'   => $coupon,
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'total'    => round($subtotal - $discount, 2),
            'count'    => collect($items)->sum('quantity'),
        ]);
    }

    public function validateCoupon(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string', 'subtotal' => 'required|numeric']);

        $coupon = Coupon::where('code', strtoupper($request->code))->first();

        if (!$coupon || !$coupon->isValid()) {
            return response()->json(['error' => 'Cupom inválido ou expirado.'], 422);
        }

        return response()->json([
            'code'     => $coupon->code,
            'type'     => $coupon->type,
            'value'    => $coupon->value,
            'discount' => $coupon->calculateDiscount($request->subtotal),
        ]);
    }
}
