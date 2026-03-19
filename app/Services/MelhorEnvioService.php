<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MelhorEnvioService
{
    private string $baseUrl;
    private string $token;

    public function __construct()
    {
        $this->baseUrl = config('services.melhorenvio.base_url');
        $this->token   = Cache::get('melhorenvio_token', '');
    }

    public function isConnected(): bool
    {
        return !empty($this->token);
    }

    private function http()
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'User-Agent'    => 'StreetFit/' . config('app.url'),
        ]);
    }

    public function calculateShipping(string $postalCodeTo, array $products = []): array
    {
        if (!$this->isConnected()) {
            return ['error' => 'Melhor Envio não autorizado.'];
        }

        $weight = collect($products)->sum(fn($p) => ($p['weight'] ?? 0.3) * ($p['quantity'] ?? 1));
        $width  = collect($products)->max(fn($p) => $p['width']  ?? 20) ?: 20;
        $height = collect($products)->sum(fn($p) => ($p['height'] ?? 5) * ($p['quantity'] ?? 1));
        $length = collect($products)->max(fn($p) => $p['length'] ?? 30) ?: 30;

        $response = $this->http()->post("{$this->baseUrl}/api/v2/me/shipment/calculate", [
            'from'    => ['postal_code' => preg_replace('/\D/', '', config('services.melhorenvio.postal_code'))],
            'to'      => ['postal_code' => preg_replace('/\D/', '', $postalCodeTo)],
            'package' => [
                'weight' => max(0.1, round($weight, 2)),
                'width'  => max(1, (int) $width),
                'height' => max(1, (int) $height),
                'length' => max(1, (int) $length),
            ],
            'options'  => ['receipt' => false, 'own_hand' => false, 'collect' => false],
            'services' => '1,2,3,4,17',
        ]);

        if (!$response->successful()) {
            Log::error('MelhorEnvio error', ['body' => $response->body()]);
            return ['error' => 'Erro ao calcular frete.'];
        }

        return collect($response->json())
            ->filter(fn($s) => !isset($s['error']))
            ->map(fn($s) => [
                'id'            => $s['id'],
                'name'          => $s['name'],
                'company'       => $s['company']['name'] ?? '',
                'price'         => $s['price'],
                'delivery_time' => $s['delivery_time'],
            ])
            ->values()
            ->toArray();
    }
}
