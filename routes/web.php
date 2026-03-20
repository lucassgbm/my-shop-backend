<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

// ── API info ──────────────────────────────────────────────────────
Route::get('/', fn() => response()->json(['api' => 'StreetFit API', 'version' => '2.0']));

// ── Rota login (alias obrigatório para o middleware do Laravel) ───
Route::get('/admin/login', function () {
    if (Auth::check() && Auth::user()->hasRole('admin')) {
        return redirect('/admin');
    }
    return view('filament.pages.auth.login');
})->name('login')->middleware('web');

Route::post('/admin/login', function (Request $request) {
    $credentials = $request->validate([
        'email'    => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (!Auth::attempt($credentials, $request->boolean('remember'))) {
        return back()->withInput($request->only('email'))
            ->withErrors(['email' => 'Credenciais inválidas.']);
    }

    $user = Auth::user();

    if (!$user->hasRole('admin')) {
        Auth::logout();
        return back()->withInput($request->only('email'))
            ->withErrors(['email' => 'Sem permissão para acessar o painel.']);
    }

    $request->session()->regenerate();
    return redirect()->intended('/admin');
})->name('filament.admin.auth.login')->middleware('web');

// ── Livewire JS (fallback) ─────────────────────────────────────────
Route::get('/livewire/livewire.js', function () {
    foreach ([
        public_path('livewire/livewire.esm.js'),
        base_path('vendor/livewire/livewire/dist/livewire.esm.js'),
        base_path('vendor/livewire/livewire/dist/livewire.js'),
    ] as $path) {
        if (file_exists($path)) {
            return response()->file($path, ['Content-Type' => 'application/javascript']);
        }
    }
    abort(404);
});

Route::get('/livewire/livewire.min.js.map', function () {
    foreach ([
        public_path('livewire/livewire.min.js.map'),
        base_path('vendor/livewire/livewire/dist/livewire.min.js.map'),
    ] as $path) {
        if (file_exists($path)) {
            return response()->file($path, ['Content-Type' => 'application/json']);
        }
    }
    abort(404);
});

// ── Melhor Envio OAuth (requer sessão de admin) ────────────────────
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/melhorenvio/auth', function () {
        $baseUrl = config('services.melhorenvio.base_url');
        $query   = http_build_query([
            'client_id'     => config('services.melhorenvio.client_id'),
            'redirect_uri'  => config('services.melhorenvio.redirect_uri'),
            'response_type' => 'code',
            'scope'         => 'cart-read cart-write orders-read orders-write shipping-calculate shipping-tracking',
        ]);
        return redirect("{$baseUrl}/oauth/authorize?{$query}");
    })->name('melhorenvio.auth');

    Route::get('/melhorenvio/callback', function (Request $request) {
        if ($request->has('error')) {
            return response()->json(['error' => $request->error_description], 400);
        }

        $baseUrl  = config('services.melhorenvio.base_url');
        $response = Http::asForm()->post("{$baseUrl}/oauth/token", [
            'grant_type'    => 'authorization_code',
            'client_id'     => config('services.melhorenvio.client_id'),
            'client_secret' => config('services.melhorenvio.client_secret'),
            'redirect_uri'  => config('services.melhorenvio.redirect_uri'),
            'code'          => $request->code,
        ]);

        if (!$response->successful()) {
            return response()->json(['error' => 'Erro ao obter token: ' . $response->body()], 400);
        }

        $data = $response->json();
        cache()->put('melhorenvio_token',        $data['access_token'],  now()->addSeconds($data['expires_in']));
        cache()->put('melhorenvio_refresh_token', $data['refresh_token'], now()->addDays(30));

        return response()->json(['message' => '✅ Melhor Envio conectado com sucesso!']);
    })->name('melhorenvio.callback');
});
