<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
class CouponController extends Controller {
    public function validate(Request $request): JsonResponse {
        $request->validate(['code' => 'required|string', 'subtotal' => 'required|numeric']);
        $coupon = Coupon::where('code', strtoupper($request->code))->first();
        if (!$coupon || !$coupon->isValid()) return response()->json(['error' => 'Cupom inválido ou expirado.'], 422);
        return response()->json([
            'code' => $coupon->code, 'type' => $coupon->type, 'value' => $coupon->value,
            'discount' => $coupon->calculateDiscount($request->subtotal),
        ]);
    }
}
