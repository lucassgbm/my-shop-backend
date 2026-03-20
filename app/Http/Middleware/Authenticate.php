<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) return null;

        // Rotas do Filament redirecionam para o login do admin
        if (str_starts_with($request->path(), 'admin')) {
            return route('filament.admin.auth.login.show');
        }

        // Outras rotas (API) não redirecionam
        return null;
    }
}
