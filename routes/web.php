<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SupportController;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

Route::get('/', [ShopController::class, 'home'])->name('home');
Route::get('/shop/{kind}', [ShopController::class, 'show'])->name('shop.show');
Route::get('/product-images/{product}', function (Product $product) {
    abort_unless($product->kind === 'souvenir' && $product->is_active && $product->image_path, 404);
    return Storage::disk('private')->response($product->image_path, 'product-image', ['X-Content-Type-Options' => 'nosniff', 'Content-Security-Policy' => 'sandbox']);
})->name('products.image')->middleware('throttle:120,1');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
});
Route::get('/auth/{provider}', [AuthController::class, 'redirect'])->name('social.redirect')->middleware('throttle:10,1');
Route::get('/auth/{provider}/callback', [AuthController::class, 'callback'])->name('social.callback')->middleware('throttle:10,1');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/account', [OrderController::class, 'account'])->name('account');
    Route::post('/account/admin-proof', function (Request $request) {
        abort_if(User::where('role', 'super_admin')->exists(), 403);
        $code = strtoupper(Str::random(16));
        Cache::put('admin_proof_'.$request->user()->id, hash('sha256', $code), now()->addMinutes(10));
        return redirect()->route('account')->with('admin_proof', $code);
    })->name('account.adminProof')->middleware('throttle:2,1');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store')->middleware('throttle:10,1');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/slip', [OrderController::class, 'slip'])->name('orders.slip')->middleware('throttle:5,1');
    Route::get('/orders/{order}/slip', [OrderController::class, 'slipFile'])->name('orders.slipFile');
    Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
    Route::post('/finance/{order}/review', [FinanceController::class, 'review'])->name('finance.review')->middleware('throttle:20,1');
    Route::get('/scan', [ScanController::class, 'index'])->name('scan.index');
    Route::get('/scan/{code}', [ScanController::class, 'show'])->name('scan.show');
    Route::post('/scan/{code}/redeem', [ScanController::class, 'redeem'])->name('scan.redeem')->middleware('throttle:20,1');
    Route::get('/assist', [SupportController::class, 'index'])->name('assist.index');
    Route::post('/assist/orders', [SupportController::class, 'store'])->name('assist.store')->middleware('throttle:10,1');
});

Route::get('/internal/expire', function (Request $request, OrderService $service) {
    abort_unless(config('app.cron_secret') && hash_equals(config('app.cron_secret'), (string) $request->bearerToken()), 403);
    return response()->json(['expired' => $service->expire()]);
})->middleware('throttle:5,1');
