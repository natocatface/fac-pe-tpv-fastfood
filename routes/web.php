<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\ComprobanteElectronicoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\TpvController;
use App\Http\Controllers\PedidoController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Configuración
    Route::get('/configuracion', [ConfiguracionController::class, 'edit'])->name('configuracion.edit');
    Route::put('/configuracion', [ConfiguracionController::class, 'update'])->name('configuracion.update');

    // Categorías
    Route::resource('categorias', CategoriaController::class)->except('show');

    // Productos
    Route::resource('productos', ProductoController::class);

    // Clientes
    Route::resource('clientes', ClienteController::class);

    // TPV
    Route::get('/tpv', [TpvController::class, 'index'])->name('tpv.index');
    Route::get('/tpv/buscar-cliente', [TpvController::class, 'buscarCliente'])->name('tpv.buscar-cliente');
    Route::post('/tpv/pedido', [TpvController::class, 'guardarPedido'])->name('tpv.pedido.guardar');
    Route::get('/tpv/ticket/{pedido}', [TpvController::class, 'ticket'])->name('tpv.ticket');

    // Pedidos
    Route::get('/pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
    Route::get('/pedidos/{pedido}', [PedidoController::class, 'show'])->name('pedidos.show');
    Route::patch('/pedidos/{pedido}/estado', [PedidoController::class, 'cambiarEstado'])->name('pedidos.estado');

    // Facturación electrónica Perú (SUNAT)
    Route::prefix('comprobantes')->name('comprobantes.')->group(function () {
        Route::get('/',                              [ComprobanteElectronicoController::class, 'index'])->name('index');
        Route::get('/emitir/{pedido}',               [ComprobanteElectronicoController::class, 'formularioEmision'])->name('emitir');
        Route::post('/emitir/{pedido}',              [ComprobanteElectronicoController::class, 'emitir'])->name('emitir.store');
        Route::get('/{comprobante}',                 [ComprobanteElectronicoController::class, 'show'])->name('show');
        Route::post('/{comprobante}/procesar',       [ComprobanteElectronicoController::class, 'procesar'])->name('procesar');
        Route::get('/{comprobante}/descargar/{tipo}',[ComprobanteElectronicoController::class, 'descargar'])->name('descargar');
        Route::get('/{comprobante}/pdf',             [ComprobanteElectronicoController::class, 'pdf'])->name('pdf');
        Route::post('/{comprobante}/regenerar-pdf',  [ComprobanteElectronicoController::class, 'regenerarPdf'])->name('regenerar-pdf');
        Route::post('/{comprobante}/anular',         [ComprobanteElectronicoController::class, 'anular'])->name('anular');
    });

    // Backup / Sistema
    Route::prefix('backup')->name('backup.')->group(function () {
        Route::get('/',                      [BackupController::class, 'index'])->name('index');
        Route::post('/crear',                [BackupController::class, 'crear'])->name('crear');
        Route::get('/descargar-directo',     [BackupController::class, 'descargarDirecto'])->name('descargar-directo');
        Route::get('/descargar/{filename}',  [BackupController::class, 'descargar'])->name('descargar');
        Route::delete('/{filename}',         [BackupController::class, 'eliminar'])->name('eliminar');
        Route::post('/restaurar',            [BackupController::class, 'restaurar'])->name('restaurar');
        Route::post('/restaurar/{filename}', [BackupController::class, 'restaurarGuardado'])->name('restaurar-guardado');
        Route::post('/resetear',             [BackupController::class, 'resetear'])->name('resetear');
    });
});
