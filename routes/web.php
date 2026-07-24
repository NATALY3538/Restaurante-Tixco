<?php

use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MesaController;
use App\Http\Controllers\WasteController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\InsumoController;
use Illuminate\Support\Facades\Route;

// ═════════════════════════════════════════════════════════════════════════════
// VISTAS WEB (BLADE TEMPLATES)
// ═════════════════════════════════════════════════════════════════════════════

Route::get('/', function () {
    return view('index');
});

Route::get('/menu', function () {
    return view('menu.index');
});

Route::get('/admin/inventario/insumos', [InsumoController::class, 'index']);

Route::get('/menu/detalle/{slug}', function ($slug) {
    return view('menu.detalle', ['slug' => $slug]);
});

Route::get('/carrito', function () {
    return view('carrito');
});

Route::get('/checkout', function () {
    return view('checkout');
});

Route::get('/pedido-confirmado', function () {
    return view('pedido_confirmado');
});

Route::get('/reservaciones', function () {
    return view('reservaciones');
});

// Ruta QR oficial para vista de cliente en mesa (/mesa/{token} o /menu/{token_mesa})
Route::get('/mesa/{token}', [MesaController::class, 'customerQrView']);
Route::get('/menu/{token_mesa}', [MesaController::class, 'customerQrView']);

Route::prefix('mi-cuenta')->group(function () {
    Route::get('/', function () {
        return view('mi_cuenta.index');
    });
    Route::get('/pedidos', function () {
        return view('mi_cuenta.pedidos');
    });
    Route::get('/reservaciones', function () {
        return view('mi_cuenta.reservaciones');
    });
    Route::get('/direcciones', function () {
        return view('mi_cuenta.direcciones');
    });
});

Route::prefix('admin')->group(function () {
    Route::get('/', function () {
        return view('admin.index');
    });
    Route::get('/productos/crear', function () {
        return view('admin.create');
    });
    Route::get('/productos/editar/{id}', function ($id) {
        return view('admin.edit', ['id' => $id]);
    });
    Route::get('/inventario', function () {
        return view('admin.inventory');
    });

    // CU-7.2.2: Registro de Mermas
    Route::get('/inventario/mermas', [WasteController::class, 'index'])->name('admin.mermas.index');

    // CU-10.2.1: Bitácora de Auditoría
    Route::get('/bitacora', [AuditLogController::class, 'index'])->name('admin.bitacora.index');

    // Gestión de Roles y Permisos de Empleados
    Route::get('/roles', [RoleController::class, 'index'])->name('admin.roles.index');
    Route::post('/admin/roles/{role}/asignar-empleado', [RoleController::class, 'asignarEmpleado']);
    Route::delete('/admin/roles/{role}/desvincular-empleado/{user}', [RoleController::class, 'desvincularEmpleado']);
    Route::resource('roles-resource', RoleController::class)->names('admin.roles');

    // Multi-Branch System - Gestión de Sucursales
    Route::get('/sucursales', [SucursalController::class, 'index'])->name('admin.sucursales.index');
    Route::resource('sucursales-resource', SucursalController::class)->names('admin.sucursales');

    // Rutas Oficiales Laravel — Gestión Dinámica de Áreas y Mesas (MesaController)
    Route::get('/areas-mesas', [MesaController::class, 'index'])->name('admin.mesas.index');
    Route::get('/infraestructura', [MesaController::class, 'index']);
    // Módulo de Gestión de Reservas (Admin)
    Route::get('/reservas', [ReservationController::class, 'index'])->name('admin.reservas.index');
});

// Admin Security Verification Direct Web & API Routes
Route::post('/admin/verify-credentials', [AdminAuthController::class, 'verifyCredentials'])->name('admin.verify');

// ═════════════════════════════════════════════════════════════════════════════
// ENDPOINTS DE API (RETORNAN JSON)
// ═════════════════════════════════════════════════════════════════════════════

Route::prefix('api')->group(function () {
    // Comensal API
    Route::get('/categories-products', [RestaurantController::class, 'categoriesProducts']);
    Route::get('/products', [RestaurantController::class, 'products']);
    Route::get('/products/{idOrSlug}', [RestaurantController::class, 'productDetail']);
    Route::get('/tables', [RestaurantController::class, 'tables']);
    Route::get('/tables/qr/{token}', [RestaurantController::class, 'getTableByQr']);
    Route::get('/payment-methods', [RestaurantController::class, 'paymentMethods']);
    Route::post('/pedidos', [RestaurantController::class, 'placeOrder']);
    Route::get('/orders/customer/{phone}', [RestaurantController::class, 'customerOrders']);
    Route::post('/reservations', [RestaurantController::class, 'makeReservation']);
    Route::get('/reservations/customer/{phone}', [RestaurantController::class, 'customerReservations']);
    Route::get('/customers/{phone}/addresses', [RestaurantController::class, 'getAddresses']);
    Route::post('/customers/{phone}/addresses', [RestaurantController::class, 'addAddress']);
    Route::delete('/customers/addresses/{id}', [RestaurantController::class, 'deleteAddress']);

    // Notificaciones de Reservas para Cliente
    Route::post('/reservaciones', [ReservationController::class, 'storeClientReservation']);
    Route::get('/cliente/notificaciones', [ReservationController::class, 'getClientNotifications']);

    // Admin Security Verification API
    Route::post('/admin/verify-credentials', [AdminAuthController::class, 'verifyCredentials']);
    Route::post('/admin/verify-access', [AdminAuthController::class, 'verifyAccess']);
    Route::post('/admin/logout-mode', [AdminAuthController::class, 'logoutMode']);

    // Admin API
    Route::get('/admin/productos', [AdminController::class, 'getProductos']);
    Route::post('/admin/productos', [AdminController::class, 'createProducto']);
    Route::put('/admin/productos/{id}', [AdminController::class, 'updateProducto']);
    Route::delete('/admin/productos/{id}', [AdminController::class, 'deleteProducto']);
    Route::get('/admin/inventario/movimientos', [AdminController::class, 'getMovimientos']);

    // Mermas API
    Route::get('/admin/mermas', [WasteController::class, 'getMermas']);
    Route::post('/admin/mermas', [WasteController::class, 'store']);

    // Audit Log API
    Route::get('/admin/audit-logs', [AuditLogController::class, 'getLogs']);
    Route::post('/admin/audit-logs', [AuditLogController::class, 'storeManualLog']);

    // Roles y Permisos API
    Route::get('/admin/roles', [RoleController::class, 'getRoles']);
    Route::post('/admin/roles', [RoleController::class, 'store']);
    Route::put('/admin/roles/{id}', [RoleController::class, 'update']);
    Route::patch('/admin/roles/{id}/status', [RoleController::class, 'toggleStatus']);
    Route::delete('/admin/roles/{id}', [RoleController::class, 'destroy']);
    Route::get('/admin/roles/{id}/empleados', [RoleController::class, 'getRoleEmployees']);
    Route::post('/admin/roles/{id}/asignar-empleado', [RoleController::class, 'asignarEmpleado']);
    Route::delete('/admin/roles/{role}/desvincular-empleado/{user}', [RoleController::class, 'desvincularEmpleado']);

    // Sucursales API (Multi-Branch System)
    Route::get('/admin/sucursales', [SucursalController::class, 'getSucursales']);
    Route::post('/admin/sucursales', [SucursalController::class, 'store']);
    Route::put('/admin/sucursales/{id}', [SucursalController::class, 'update']);
    Route::patch('/admin/sucursales/{id}/status', [SucursalController::class, 'toggleStatus']);
    Route::delete('/admin/sucursales/{id}', [SucursalController::class, 'destroy']);

    // Gestión de Reservas API (Admin)
    Route::get('/admin/reservas', [ReservationController::class, 'getReservations']);
    Route::post('/admin/reservas/{id}/accept', [ReservationController::class, 'acceptReservation']);
    Route::post('/admin/reservas/{id}/reject', [ReservationController::class, 'rejectReservation']);

    // Admin API — Gestión Dinámica de Áreas, Mesas, Fusiones y Split Bill (MesaController)
    Route::get('/admin/areas', [MesaController::class, 'getAreas']);
    Route::post('/admin/areas', [MesaController::class, 'createArea']);
    Route::put('/admin/areas/{id}', [MesaController::class, 'updateArea']);
    Route::patch('/admin/areas/{id}/status', [MesaController::class, 'toggleAreaStatus']);
    Route::post('/admin/areas/{id}/mesas', [MesaController::class, 'createTablesForArea']);
    Route::post('/admin/mesas', [MesaController::class, 'createSingleMesa']);
    Route::put('/admin/mesas/{id}', [MesaController::class, 'updateMesa']);
    Route::delete('/admin/mesas/{id}', [MesaController::class, 'deleteMesa']);
    Route::patch('/admin/mesas/{id}/status', [MesaController::class, 'toggleTableStatus']);
    Route::patch('/admin/mesas/{id}/liberar', [MesaController::class, 'liberarMesa']);

    // CU-2.1.4: Mover y Fusionar Mesas
    Route::post('/admin/mesas/mover', [MesaController::class, 'moverMesa']);
    Route::post('/admin/mesas/fusionar', [MesaController::class, 'fusionarMesas']);

    // CU-2.2.3: Anulación de Platillo con PIN
    Route::post('/admin/comandas/anular-item', [MesaController::class, 'anularItem']);

    // CU-5.2.3: Split Bill
    Route::post('/admin/mesas/cobrar-split', [MesaController::class, 'cobrarSplit']);

    // Insumos / Productos Base API (Admin)
    Route::get('/admin/insumos', [InsumoController::class, 'getInsumos']);
    Route::post('/admin/insumos', [InsumoController::class, 'store']);
    Route::put('/admin/insumos/{id}', [InsumoController::class, 'update']);
    Route::delete('/admin/insumos/{id}', [InsumoController::class, 'destroy']);
});
