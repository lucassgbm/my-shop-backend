<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ShippingController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\WebhookController;

// ── Auth ──────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
    Route::post('/logout',   [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/me',        [AuthController::class, 'me'])->middleware('auth:sanctum');
});

// ── Catálogo público ──────────────────────────────────────────────
Route::get('/categories',              [CategoryController::class, 'index']);
Route::get('/categories/{slug}',       [CategoryController::class, 'show']);
Route::get('/products',                [ProductController::class, 'index']);
Route::get('/products/featured',       [ProductController::class, 'featured']);
Route::get('/products/{slug}',         [ProductController::class, 'show']);

// ── Frete e cupom (público) ───────────────────────────────────────
Route::post('/shipping/calculate',     [ShippingController::class, 'calculate']);
Route::post('/coupons/validate',       [CouponController::class, 'validate']);

// ── Rotas autenticadas ────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Carrinho
    Route::prefix('cart')->group(function () {
        Route::get('/',              [CartController::class, 'index']);
        Route::post('/items',        [CartController::class, 'addItem']);
        Route::put('/items/{id}',    [CartController::class, 'updateItem']);
        Route::delete('/items/{id}', [CartController::class, 'removeItem']);
        Route::post('/coupon',       [CartController::class, 'applyCoupon']);
        Route::delete('/coupon',     [CartController::class, 'removeCoupon']);
        Route::get('/summary',       [CartController::class, 'summary']);
    });

    // Checkout
    Route::prefix('checkout')->group(function () {
        Route::post('/pix',  [CheckoutController::class, 'pix']);
        Route::post('/card', [CheckoutController::class, 'card']);
    });

    // Pedidos
    Route::get('/orders',        [OrderController::class, 'index']);
    Route::get('/orders/{id}',   [OrderController::class, 'show']);

    // Endereços
    Route::get('/addresses',           [AddressController::class, 'index']);
    Route::post('/addresses',          [AddressController::class, 'store']);
    Route::put('/addresses/{id}',      [AddressController::class, 'update']);
    Route::delete('/addresses/{id}',   [AddressController::class, 'destroy']);

    // Wishlist
    Route::get('/wishlist',             [WishlistController::class, 'index']);
    Route::post('/wishlist/{product}',  [WishlistController::class, 'toggle']);

    // Avaliações
    Route::post('/products/{product}/reviews', [ReviewController::class, 'store']);
});

// ── Webhooks ──────────────────────────────────────────────────────
Route::prefix('webhooks')->group(function () {
    Route::post('/mercadopago', [WebhookController::class, 'mercadoPago']);
    Route::post('/melhorenvio', [WebhookController::class, 'melhorEnvio']);
});
