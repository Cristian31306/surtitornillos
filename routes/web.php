<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Clientes
    Route::get('/clientes', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/clientes/nuevo', [ClientController::class, 'create'])->name('clients.create');
    Route::post('/clientes', [ClientController::class, 'store'])->name('clients.store');
    Route::get('/clientes/{client}/editar', [ClientController::class, 'edit'])->name('clients.edit');
    Route::put('/clientes/{client}', [ClientController::class, 'update'])->name('clients.update');

    // Facturas
    Route::get('/facturas', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/facturas/exportar', [InvoiceController::class, 'exportExcel'])->name('invoices.export');
    Route::get('/facturas/nueva', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/facturas', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/facturas/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/facturas/{invoice}/editar', [InvoiceController::class, 'edit'])->name('invoices.edit');
    Route::put('/facturas/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
    Route::patch('/facturas/{invoice}/estado', [InvoiceController::class, 'updateStatus'])->name('invoices.updateStatus');

    // Pagos (Abonos)
    Route::post('/facturas/{invoice}/abonos', [PaymentController::class, 'store'])->name('payments.store');
    Route::delete('/abonos/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

    // Ajustes y Devoluciones
    Route::post('/facturas/{invoice}/ajustes', [\App\Http\Controllers\AdjustmentController::class, 'store'])->name('adjustments.store');
    Route::delete('/ajustes/{adjustment}', [\App\Http\Controllers\AdjustmentController::class, 'destroy'])->name('adjustments.destroy');

    // Gestión de Usuarios (Admin únicamente)
    Route::get('/usuarios', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
    Route::post('/usuarios', [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
    Route::put('/usuarios/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');
    Route::delete('/usuarios/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');

    // Auditoría de Actividad (Admin únicamente)
    Route::get('/auditoria', [\App\Http\Controllers\AuditController::class, 'index'])->name('audit.index');

    // Configuración / Membresía (Admin únicamente)
    Route::post('/configuracion/membresia', [\App\Http\Controllers\SettingController::class, 'updateMembership'])->name('settings.membership');

    // Perfil / Cambio de contraseña
    Route::put('/perfil/contrasena', [\App\Http\Controllers\UserController::class, 'updateOwnPassword'])->name('profile.password.update');
});
