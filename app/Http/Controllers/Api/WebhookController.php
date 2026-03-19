<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class WebhookController extends Controller {
    public function mercadoPago(Request $request): \Illuminate\Http\JsonResponse {
        Log::info('MP Webhook', $request->all());
        $id = $request->input('data.id');
        if ($request->type === 'payment' && $id) {
            $mp      = new MercadoPagoService();
            $payment = $mp->getPayment($id);
            if ($payment && $payment['status'] === 'approved') {
                $order = Order::where('id', $payment['external_reference'])->first();
                if ($order && !$order->isPaid()) {
                    $order->update(['status' => Order::STATUS_PAID]);
                    Payment::updateOrCreate(['order_id' => $order->id], [
                        'provider' => 'mercadopago', 'provider_id' => $id,
                        'method' => $payment['payment_method_id'], 'status' => 'approved',
                        'amount' => $payment['transaction_amount'],
                    ]);
                }
            }
        }
        return response()->json(['ok' => true]);
    }
    public function melhorEnvio(Request $request): \Illuminate\Http\JsonResponse {
        Log::info('ME Webhook', $request->all());
        return response()->json(['ok' => true]);
    }
}
