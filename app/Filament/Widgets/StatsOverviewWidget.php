<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $revenue = Order::where('status', Order::STATUS_PAID)
            ->whereMonth('created_at', now()->month)
            ->sum('total');

        $orders = Order::whereMonth('created_at', now()->month)->count();

        $customers = User::role('customer')->count();

        $lowStock = Product::active()
            ->whereIn('id', function ($q) {
                $q->select('product_id')
                    ->from('product_variants')
                    ->groupBy('product_id')
                    ->havingRaw('SUM(stock) <= 5');
            })
            ->count();

        return [
            Stat::make('Receita do mês', 'R$ ' . number_format($revenue, 2, ',', '.'))
                ->description('Pedidos pagos')
                ->color('success')
                ->icon('heroicon-o-currency-dollar'),

            Stat::make('Pedidos do mês', $orders)
                ->description('Total de pedidos')
                ->color('primary')
                ->icon('heroicon-o-shopping-cart'),

            Stat::make('Clientes', $customers)
                ->description('Clientes cadastrados')
                ->color('info')
                ->icon('heroicon-o-users'),

            Stat::make('Estoque crítico', $lowStock)
                ->description('Produtos com ≤5 unidades')
                ->color($lowStock > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-exclamation-triangle'),
        ];
    }
}
