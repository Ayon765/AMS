<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\CashController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CrudController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StockController;
use App\Http\Middleware\Permission;
use App\Support\DocType;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Generic master-data CRUD (branches, employees, products, suppliers, users ...)
    Route::prefix('m/{slug}')->name('crud.')->controller(CrudController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    Route::get('/stock', [StockController::class, 'index'])->name('stock.index')->middleware(Permission::class . ':stock');

    // Cash book
    Route::get('/cash/{type}', [CashController::class, 'index'])->whereIn('type', ['in', 'out'])->name('cash.index');
    Route::post('/cash/{type}', [CashController::class, 'store'])->whereIn('type', ['in', 'out'])->name('cash.store');
    Route::delete('/cash-entry/{entry}', [CashController::class, 'destroy'])->name('cash.destroy');

    // Approvals
    Route::middleware(Permission::class . ':approval')->group(function () {
        Route::get('/approval/{type}', [ApprovalController::class, 'index'])->whereIn('type', ['purchase', 'delivery', 'adjustment', 'damage'])->name('approval.index');
        Route::post('/approval-doc/{doc}/approve', [ApprovalController::class, 'approve'])->name('approval.approve');
        Route::post('/approval-doc/{doc}/reject', [ApprovalController::class, 'reject'])->name('approval.reject');
    });

    // Reports
    Route::get('/reports/{type}', [ReportController::class, 'show'])
        ->whereIn('type', ['purchase', 'delivery', 'adjustment', 'damage', 'cash_in', 'cash_out'])
        ->name('reports.show')->middleware(Permission::class . ':reports');

    // Settings
    Route::middleware(Permission::class . ':company')->group(function () {
        Route::get('/company', [CompanyController::class, 'edit'])->name('company.edit');
        Route::put('/company', [CompanyController::class, 'update'])->name('company.update');
    });
    Route::resource('roles', RoleController::class)->except('show')->middleware(Permission::class . ':roles');
    Route::middleware(Permission::class . ':backup')->prefix('backup')->name('backup.')->group(function () {
        Route::get('/', [BackupController::class, 'index'])->name('index');
        Route::get('/download', [BackupController::class, 'download'])->name('download');
        Route::post('/restore', [BackupController::class, 'restore'])->name('restore');
    });

    // Stock documents: purchase, delivery, adjustment, damage, transfer, proposal, invoice
    Route::prefix('{type}')->where(['type' => implode('|', DocType::keys())])->name('doc.')
        ->controller(DocumentController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{doc}', 'show')->name('show');
            Route::delete('/{doc}', 'destroy')->name('destroy');
            Route::post('/{doc}/convert', 'convert')->name('convert');
            Route::post('/{doc}/pay', 'pay')->name('pay');
        });
});
