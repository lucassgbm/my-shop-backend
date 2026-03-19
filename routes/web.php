<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn() => response()->json(['api' => 'StreetFit API', 'version' => '2.0']));

// ── Melhor Envio OAuth ────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/melhorenvio/auth', function () {
        $baseUrl = config('services.melhorenvio.base_url');
        $query = http_build_query([
            'client_id'     => config('services.melhorenvio.client_id'),
            'redirect_uri'  => config('services.melhorenvio.redirect_uri'),
            'response_type' => 'code',
            'scope'         => 'cart-read cart-write orders-read orders-write shipping-calculate shipping-tracking',
        ]);
        return redirect("{$baseUrl}/oauth/authorize?{$query}");
    })->name('melhorenvio.auth');

    Route::get('/melhorenvio/callback', function (\Illuminate\Http\Request $request) {
        if ($request->has('error')) {
            return response()->json(['error' => $request->error_description], 400);
        }
        $baseUrl = config('services.melhorenvio.base_url');
        $response = \Illuminate\Support\Facades\Http::asForm()->post("{$baseUrl}/oauth/token", [
            'grant_type'    => 'authorization_code',
            'client_id'     => config('services.melhorenvio.client_id'),
            'client_secret' => config('services.melhorenvio.client_secret'),
            'redirect_uri'  => config('services.melhorenvio.redirect_uri'),
            'code'          => $request->code,
        ]);
        if (!$response->successful()) {
            return response()->json(['error' => 'Erro ao obter token'], 400);
        }
        $data = $response->json();
        cache()->put('melhorenvio_token',        $data['access_token'],  now()->addSeconds($data['expires_in']));
        cache()->put('melhorenvio_refresh_token', $data['refresh_token'], now()->addDays(30));
        return response()->json(['message' => 'Melhor Envio conectado!']);
    })->name('melhorenvio.callback');
});
