<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Address;
use App\Models\Coupon;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function __construct(private MercadoPagoService $mp) {}

    public function pix(Request $request): JsonResponse
    {
        $data = $request->validate([
            'address_id'          => 'required|exists:addresses,id',
            'shipping_service_id' => 'required|integer',
            'shipping_cost'       => 'required|numeric|min:0',
            'shipping_name'       => 'required|string',
            'coupon_code'         => 'nullable|string',
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|integer',
            'items.*.variant_id'  => 'nullable|integer',
            'items.*.quantity'    => 'required|integer|min:1',
        ]);

        $order = $this->createOrder($request, $data);

        $pix = $this->mp->createPixPayment($order);

        if (isset($pix['error'])) {
            return response()->json(['error' => $pix['error']], 422);
        }

        return response()->json([
            'order_id'     => $order->id,
            'pix_code'     => $pix['point_of_interaction']['transaction_data']['qr_code'] ?? null,
            'pix_qr_base64'=> $pix['point_of_interaction']['transaction_data']['qr_code_base64'] ?? null,
            'expires_at'   => $pix['date_of_expiration'] ?? null,
        ]);
    }

    public function card(Request $request): JsonResponse
    {
        $data = $request->validate([
            'address_id'          => 'required|exists:addresses,id',
            'shipping_service_id' => 'required|integer',
            'shipping_cost'       => 'required|numeric|min:0',
            'shipping_name'       => 'required|string',
            'token'               => 'required|string',
            'installments'        => 'required|integer|min:1|max:12',
            'payment_method_id'   => 'required|string',
            'coupon_code'         => 'nullable|string',
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|integer',
            'items.*.variant_id'  => 'nullable|integer',
            'items.*.quantity'    => 'required|integer|min:1',
        ]);

        $order = $this->createOrder($request, $data);

        $payment = $this->mp->processCardPayment($order, [
            'token'             => $data['token'],
            'installments'      => $data['installments'],
            'payment_method_id' => $data['payment_method_id'],
        ]);

        if (isset($payment['error'])) {
            $order->delete();
            return response()->json(['error' => $payment['error']], 422);
        }

        if ($payment['status'] === 'approved') {
            $order->update(['status' => Order::STATUS_PAID]);
        }

        return response()->json([
            'order_id' => $order->id,
            'status'   => $payment['status'],
            'message'  => $payment['status'] === 'approved' ? 'Pagamento aprovado!' : 'Pagamento em processamento.',
        ]);
    }

    private function createOrder(Request $request, array $data): Order
    {
        $user    = $request->user();
        $address = Address::where('id', $data['address_id'])->where('user_id', $user->id)->firstOrFail();

        // Itens vêm do frontend (Zustand)
        $rawItems = $data['items'] ?? [];

        if (empty($rawItems)) {
            abort(422, 'Carrinho vazio.');
        }

        // Valida preços no servidor
        $items    = [];
        $subtotal = 0;

        foreach ($rawItems as $raw) {
            $product = \App\Models\Product::find($raw['product_id']);
            if (!$product) continue;
            $variant  = !empty($raw['variant_id']) ? \App\Models\ProductVariant::find($raw['variant_id']) : null;
            $price    = (float) ($variant?->price ?? $product->price);
            $qty      = (int) $raw['quantity'];

            $items[] = [
                'product_id'       => $product->id,
                'variant_id'       => $variant?->id,
                'quantity'         => $qty,
                'unit_price'       => $price,
                'total_price'      => $price * $qty,
                'product_snapshot' => [
                    'name'  => $product->name,
                    'size'  => $variant?->size,
                    'image' => $product->primary_thumb_url,
                    'price' => $price,
                ],
            ];
            $subtotal += $price * $qty;
        }

        $discount = 0;
        $coupon   = null;

        if (!empty($data['coupon_code'])) {
            $coupon   = Coupon::where('code', strtoupper($data['coupon_code']))->first();
            $discount = ($coupon && $coupon->isValid()) ? $coupon->calculateDiscount($subtotal) : 0;
        }

        $total = $subtotal - $discount + $data['shipping_cost'];

        return DB::transaction(function () use ($user, $address, $items, $subtotal, $discount, $total, $data, $coupon) {
            $order = Order::create([
                'user_id'              => $user->id,
                'status'               => Order::STATUS_PENDING,
                'subtotal'             => $subtotal,
                'discount'             => $discount,
                'shipping_cost'        => $data['shipping_cost'],
                'total'                => $total,
                'shipping_address'     => $address->toArray(),
                'shipping_service'     => $data['shipping_service_id'],
                'shipping_service_name'=> $data['shipping_name'],
                'coupon_id'            => $coupon?->id,
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id'         => $order->id,
                    'product_id'       => $item['product_id'],
                    'variant_id'       => $item['variant_id'],
                    'quantity'         => $item['quantity'],
                    'unit_price'       => $item['unit_price'],
                    'total_price'      => $item['total_price'],
                    'product_snapshot' => $item['product_snapshot'],
                ]);
            }

            if ($coupon) $coupon->increment('used_count');

            session()->forget('cart');

            return $order;
        });
    }
}
