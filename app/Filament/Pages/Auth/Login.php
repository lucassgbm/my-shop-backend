<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\View\View;

class Login extends BaseLogin
{
    /**
     * Usa view HTML pura sem Livewire para evitar problemas
     * de carregamento do livewire.js em produção.
     */
    public function render(): View
    {
        return view('filament.pages.auth.login');
    }
}
