<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;

class MercadoPagoService
{
    public function __construct()
    {
        MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));
    }

    public function createPixPayment(Order $order): array
    {
        try {
            $client  = new PaymentClient();
            $user    = $order->user;
            $payment = $client->create([
                'transaction_amount' => (float) $order->total,
                'payment_method_id'  => 'pix',
                'external_reference' => (string) $order->id,
                'payer'              => [
                    'email'      => $user->email,
                    'first_name' => explode(' ', $user->name)[0],
                    'last_name'  => explode(' ', $user->name, 2)[1] ?? '',
                ],
            ]);
            return $payment->toArray();
        } catch (\Exception $e) {
            Log::error('MP PIX error', ['msg' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    public function processCardPayment(Order $order, array $cardData): array
    {
        try {
            $client  = new PaymentClient();
            $user    = $order->user;
            $payment = $client->create([
                'transaction_amount' => (float) $order->total,
                'token'              => $cardData['token'],
                'installments'       => $cardData['installments'],
                'payment_method_id'  => $cardData['payment_method_id'],
                'external_reference' => (string) $order->id,
                'payer'              => ['email' => $user->email],
            ]);
            return $payment->toArray();
        } catch (\Exception $e) {
            Log::error('MP Card error', ['msg' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    public function getPayment(string $id): ?array
    {
        try {
            $client = new PaymentClient();
            return $client->get((int) $id)->toArray();
        } catch (\Exception $e) {
            Log::error('MP getPayment error', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    public function refund(string $paymentId): array
    {
        try {
            $client = new PaymentClient();
            return $client->refund((int) $paymentId)->toArray();
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
