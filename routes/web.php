<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductModelController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : redirect()->route('login'));

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role:owner,admin')->group(function () {
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/payments', [OrderController::class, 'recordPayment'])->name('orders.payments.store');
        Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
        Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
        Route::get('/orders/{order}/wa', [OrderController::class, 'wa'])->name('orders.wa');

        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/stock-in', [InventoryController::class, 'stockInForm'])->name('inventory.stock-in.create');
        Route::post('/inventory/stock-in', [InventoryController::class, 'stockIn'])->name('inventory.stock-in.store');
        Route::get('/inventory/stock-out', [InventoryController::class, 'stockOutForm'])->name('inventory.stock-out.create');
        Route::post('/inventory/stock-out', [InventoryController::class, 'stockOut'])->name('inventory.stock-out.store');
        Route::get('/inventory/opname', [InventoryController::class, 'opnameIndex'])->name('inventory.opname.index');
        Route::get('/inventory/opname/create', [InventoryController::class, 'opnameCreate'])->name('inventory.opname.create');
        Route::post('/inventory/opname', [InventoryController::class, 'opnameStore'])->name('inventory.opname.store');
        Route::get('/inventory/opname/{opname}', [InventoryController::class, 'opnameShow'])->name('inventory.opname.show');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');
        Route::get('/reports/excel', [ReportController::class, 'exportExcel'])->name('reports.excel');

        Route::resource('product-models', ProductModelController::class)
            ->parameters(['product-models' => 'productModel']);
        Route::post('/product-models/{productModel}/bom', [ProductModelController::class, 'storeBom'])->name('product-models.bom.store');
        Route::delete('/product-models/{productModel}/bom/{bom}', [ProductModelController::class, 'destroyBom'])->name('product-models.bom.destroy');

        Route::resource('materials', MaterialController::class);
        Route::resource('products', ProductController::class);
        Route::post('/products/{product}/adjust-stock', [ProductController::class, 'adjustStock'])->name('products.adjust-stock');

        Route::get('/master/{resource}', [MasterDataController::class, 'index'])->name('master.index');
        Route::get('/master/{resource}/create', [MasterDataController::class, 'create'])->name('master.create');
        Route::post('/master/{resource}', [MasterDataController::class, 'store'])->name('master.store');
        Route::get('/master/{resource}/{id}/edit', [MasterDataController::class, 'edit'])->name('master.edit');
        Route::put('/master/{resource}/{id}', [MasterDataController::class, 'update'])->name('master.update');
        Route::delete('/master/{resource}/{id}', [MasterDataController::class, 'destroy'])->name('master.destroy');
    });

    Route::middleware('role:owner')->group(function () {
        Route::post('/orders/{order}/approve', [OrderController::class, 'approve'])->name('orders.approve');
        Route::post('/orders/{order}/transfer', [OrderController::class, 'transfer'])->name('orders.transfer');
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    });

    Route::get('/production', [ProductionController::class, 'index'])->name('production.index');
    Route::post('/production/{orderItem}/status', [ProductionController::class, 'updateStatus'])->name('production.status');
});
