<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Proxy reverso (Render, Railway, Cloudflare)
        Request::setTrustedProxies(['*'],
            Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO
        );

        // Força HTTPS em produção
        if (str_starts_with(config('app.url'), 'https') || config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Publica assets do Filament automaticamente se não existirem
        // (resolve 404 em ambientes com filesystem efêmero como Render)
        if (config('app.env') === 'production' && !file_exists(public_path('css/filament/filament/app.css'))) {
            try {
                Artisan::call('filament:assets');
            } catch (\Throwable $e) {
                // Silencia erro — não deve travar a aplicação
            }
        }
    }
}
