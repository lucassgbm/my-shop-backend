<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
class OrderController extends Controller {
    public function index(Request $request): JsonResponse {
        $orders = Order::with('items')->where('user_id', $request->user()->id)->orderByDesc('created_at')->paginate(10);
        return response()->json([
            'data' => $orders->getCollection()->map(fn($o) => [
                'id' => $o->id, 'status' => $o->status, 'status_label' => $o->status_label,
                'total' => $o->total, 'items_count' => $o->items->count(), 'created_at' => $o->created_at->format('d/m/Y'),
            ]),
            'meta' => ['current_page' => $orders->currentPage(), 'last_page' => $orders->lastPage(), 'total' => $orders->total()],
        ]);
    }
    public function show(Request $request, int $id): JsonResponse {
        $order = Order::with(['items.product','items.variant','payment'])->where('user_id', $request->user()->id)->findOrFail($id);
        return response()->json([
            'id' => $order->id, 'status' => $order->status, 'status_label' => $order->status_label,
            'subtotal' => $order->subtotal, 'discount' => $order->discount,
            'shipping_cost' => $order->shipping_cost, 'total' => $order->total,
            'shipping_address' => $order->shipping_address, 'shipping_service_name' => $order->shipping_service_name,
            'tracking_code' => $order->tracking_code, 'created_at' => $order->created_at->format('d/m/Y H:i'),
            'items' => $order->items->map(fn($i) => [
                'id' => $i->id, 'product_name' => $i->product?->name ?? $i->product_snapshot['name'],
                'size' => $i->variant?->size, 'quantity' => $i->quantity,
                'unit_price' => $i->unit_price, 'total_price' => $i->total_price,
                'image' => $i->product?->primary_thumb_url,
            ]),
        ]);
    }
}
