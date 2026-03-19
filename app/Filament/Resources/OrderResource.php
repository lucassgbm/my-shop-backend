<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon  = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Pedidos';
    protected static ?int    $navigationSort  = 2;

    public static function getNavigationGroup(): string { return 'Vendas'; }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('status')
                ->options([
                    Order::STATUS_PENDING   => 'Aguardando pagamento',
                    Order::STATUS_PAID      => 'Pago',
                    Order::STATUS_SHIPPED   => 'Enviado',
                    Order::STATUS_DELIVERED => 'Entregue',
                    Order::STATUS_CANCELLED => 'Cancelado',
                ])->required(),
            Forms\Components\TextInput::make('tracking_code')->label('Código de rastreio'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Cliente')->searchable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn($state) => match($state) {
                    'paid' => 'success', 'shipped' => 'info', 'delivered' => 'success',
                    'cancelled' => 'danger', default => 'warning',
                }),
                Tables\Columns\TextColumn::make('total')->money('BRL')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Data')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    Order::STATUS_PENDING => 'Pendente', Order::STATUS_PAID => 'Pago',
                    Order::STATUS_SHIPPED => 'Enviado', Order::STATUS_DELIVERED => 'Entregue',
                    Order::STATUS_CANCELLED => 'Cancelado',
                ]),
            ])
            ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'edit'  => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
