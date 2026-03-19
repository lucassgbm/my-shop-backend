<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon  = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Produtos';
    protected static ?int    $navigationSort  = 1;

    public static function getNavigationGroup(): string { return 'Catálogo'; }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make()->tabs([

                Forms\Components\Tabs\Tab::make('Geral')->schema([
                    Forms\Components\TextInput::make('name')->label('Nome')->required()->live(onBlur: true)
                        ->afterStateUpdated(fn($state, Forms\Set $set) => $set('slug', Str::slug($state)))->columnSpanFull(),
                    Forms\Components\TextInput::make('slug')->label('Slug')->required()->columnSpanFull(),
                    Forms\Components\Select::make('category_id')->label('Categoria')->options(Category::pluck('name','id'))->required(),
                    Forms\Components\TextInput::make('price')->label('Preço')->numeric()->prefix('R$')->required(),
                    Forms\Components\TextInput::make('compare_price')->label('Preço original')->numeric()->prefix('R$'),
                    Forms\Components\Toggle::make('is_active')->label('Ativo')->default(true),
                    Forms\Components\Toggle::make('is_featured')->label('Destaque'),
                    Forms\Components\RichEditor::make('description')->label('Descrição')->columnSpanFull(),
                ])->columns(2),

                Forms\Components\Tabs\Tab::make('Dimensões')->schema([
                    Forms\Components\TextInput::make('weight')->label('Peso (kg)')->numeric()->default(0.3),
                    Forms\Components\TextInput::make('width')->label('Largura (cm)')->numeric()->default(20),
                    Forms\Components\TextInput::make('height')->label('Altura (cm)')->numeric()->default(5),
                    Forms\Components\TextInput::make('length')->label('Comprimento (cm)')->numeric()->default(30),
                ])->columns(2),

                Forms\Components\Tabs\Tab::make('Variantes')->schema([
                    Forms\Components\Repeater::make('variants')->relationship()->schema([
                        Forms\Components\Select::make('size')->label('Tamanho')->options(array_combine(\App\Models\ProductVariant::SIZES, \App\Models\ProductVariant::SIZES))->required(),
                        Forms\Components\TextInput::make('color')->label('Cor'),
                        Forms\Components\TextInput::make('sku')->label('SKU'),
                        Forms\Components\TextInput::make('stock')->label('Estoque')->numeric()->default(0)->required(),
                        Forms\Components\TextInput::make('price')->label('Preço')->numeric()->prefix('R$'),
                    ])->columns(5)->columnSpanFull(),
                ]),

                Forms\Components\Tabs\Tab::make('Imagens')->schema([
                    Forms\Components\SpatieMediaLibraryFileUpload::make('images')
                        ->collection('images')->multiple()->reorderable()->image()->maxFiles(10)->columnSpanFull()
                        ->helperText('Arraste para reordenar. Primeira imagem é a padrão.'),
                    Forms\Components\Select::make('primary_image_uuid')->label('Imagem Padrão')
                        ->options(fn($record) => $record ? $record->getMedia('images')->mapWithKeys(fn($m) => [$m->uuid => 'Imagem #'.$m->order_column])->toArray() : [])
                        ->visible(fn($record) => $record && $record->getMedia('images')->count() > 1)->live(),
                ]),

            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('images')->collection('images')->label('')->conversion('thumb')->width(50)->height(50),
                Tables\Columns\TextColumn::make('name')->label('Nome')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category.name')->label('Categoria')->badge(),
                Tables\Columns\TextColumn::make('price')->label('Preço')->money('BRL')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('Ativo')->boolean(),
                Tables\Columns\IconColumn::make('is_featured')->label('Destaque')->boolean(),
                Tables\Columns\TextColumn::make('variants_sum_stock')->label('Estoque')->sum('variants','stock')->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('category')->relationship('category','name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
            ])]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
