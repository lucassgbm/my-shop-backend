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

    // Converte o objeto do SDK v3 para array
    private function toArray(object $obj): array
    {
        return json_decode(json_encode($obj), true) ?? [];
    }

    public function createPixPayment(Order $order): array
    {
        try {
            $client = new PaymentClient();
            $user   = $order->user;

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

            return $this->toArray($payment);
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

            return $this->toArray($payment);
        } catch (\Exception $e) {
            Log::error('MP Card error', ['msg' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    public function getPayment(string $id): ?array
    {
        try {
            $client = new PaymentClient();
            return $this->toArray($client->get((int) $id));
        } catch (\Exception $e) {
            Log::error('MP getPayment error', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    public function refund(string $paymentId): array
    {
        try {
            $client = new PaymentClient();
            return $this->toArray($client->refund((int) $paymentId));
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
