<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon  = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Usuários';
    protected static ?int    $navigationSort  = 1;

    public static function getNavigationGroup(): string { return 'Administração'; }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Dados')->schema([
                Forms\Components\TextInput::make('name')->label('Nome')->required(),
                Forms\Components\TextInput::make('email')->label('E-mail')->email()->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('phone')->label('Telefone'),
            ])->columns(2),
            Forms\Components\Section::make('Acesso')->schema([
                Forms\Components\TextInput::make('password')->label('Senha')->password()
                    ->dehydrateStateUsing(fn($s) => filled($s) ? Hash::make($s) : null)
                    ->dehydrated(fn($s) => filled($s))
                    ->required(fn(string $operation) => $operation === 'create')->minLength(8)
                    ->helperText('Deixe em branco para manter.'),
                Forms\Components\Select::make('roles')->relationship('roles','name')->multiple()->preload()->label('Perfil'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nome')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('roles.name')->label('Perfil')->badge(),
                Tables\Columns\TextColumn::make('orders_count')->label('Pedidos')->counts('orders'),
                Tables\Columns\TextColumn::make('created_at')->label('Cadastro')->date('d/m/Y')->sortable(),
            ])
            ->defaultSort('created_at','desc')
            ->filters([Tables\Filters\SelectFilter::make('roles')->relationship('roles','name')])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->hidden(fn(User $r) => $r->id === auth()->id()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
