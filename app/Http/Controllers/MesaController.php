<?php

namespace App\Http\Controllers;

use App\Models\ServiceArea;
use App\Models\RestaurantTable;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MesaController extends Controller
{
    /**
     * Display the 2D Floor Plan Admin View
     */
    public function index()
    {
        return view('admin.mesas.index');
    }

    /**
     * Customer QR View (/menu/{token_mesa} or /mesa/{token})
     */
    public function customerQrView($token)
    {
        return view('mesa', ['token' => $token]);
    }

    /**
     * GET /api/admin/areas
     */
    public function getAreas()
    {
        $query = ServiceArea::withCount('tables');
        if (\Illuminate\Support\Facades\Schema::hasTable('orders')) {
            $query->with(['tables.activeOrders.items']);
        } else {
            $query->with(['tables']);
        }

        $areas = $query->get();
        return response()->json($areas);
    }

    /**
     * POST /api/admin/areas
     */
    public function createArea(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'max_tables' => 'nullable|integer|min:0',
            'max_capacity' => 'nullable|integer|min:0',
            'allows_smoking' => 'nullable|boolean',
            'is_vip' => 'nullable|boolean'
        ]);

        $area = ServiceArea::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'max_tables' => $data['max_tables'] ?? 0,
            'max_capacity' => $data['max_capacity'] ?? 0,
            'allows_smoking' => $data['allows_smoking'] ?? false,
            'is_vip' => $data['is_vip'] ?? false,
            'is_active' => true
        ]);

        AuditLog::record('Crear Área', 'Salón', ['name' => $area->name]);

        return response()->json($area, 201);
    }

    /**
     * PUT /api/admin/areas/{id}
     */
    public function updateArea(Request $request, $id)
    {
        $area = ServiceArea::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'max_tables' => 'nullable|integer|min:0',
            'max_capacity' => 'nullable|integer|min:0',
            'allows_smoking' => 'nullable|boolean',
            'is_vip' => 'nullable|boolean',
            'is_active' => 'nullable|boolean'
        ]);

        if (isset($data['max_tables']) && $data['max_tables'] > 0) {
            $currentTablesCount = $area->tables()->count();
            if ($currentTablesCount > $data['max_tables']) {
                return response()->json([
                    'message' => "No se puede reducir el límite a {$data['max_tables']} mesas porque el área actualmente tiene {$currentTablesCount} mesas registradas."
                ], 422);
            }
        }

        $area->update($data);
        AuditLog::record('Editar Área', 'Salón', ['area_id' => $area->id, 'name' => $area->name]);

        return response()->json($area);
    }

    /**
     * PATCH /api/admin/areas/{id}/status
     */
    public function toggleAreaStatus(Request $request, $id)
    {
        $area = ServiceArea::findOrFail($id);
        $newStatus = $request->input('is_active', !$area->is_active);

        if (!$newStatus) {
            $tableIds = $area->tables()->pluck('id');
            $activeOrders = Order::whereIn('restaurant_table_id', $tableIds)
                ->whereNotIn('status', ['delivered', 'cancelled'])
                ->exists();

            $occupiedTables = $area->tables()->where('status', 'occupied')->exists();

            if ($activeOrders || $occupiedTables) {
                return response()->json([
                    'message' => "No se puede dar de baja el área '{$area->name}' porque tiene mesas ocupadas o comandas activas pendientes de cobro."
                ], 409);
            }
        }

        $area->is_active = $newStatus;
        $area->save();

        AuditLog::record('Desactivar/Activar Área', 'Salón', ['area' => $area->name, 'new_status' => $newStatus]);

        return response()->json(['message' => 'Estado del área actualizado correctamente', 'area' => $area]);
    }

    /**
     * POST /api/admin/mesas (Single Mesa Creation)
     */
    public function createSingleMesa(Request $request)
    {
        $data = $request->validate([
            'service_area_id' => 'required|exists:service_areas,id',
            'table_code' => 'required|string|max:20',
            'name' => 'nullable|string|max:50',
            'capacity' => 'required|integer|min:1',
            'shape' => 'nullable|string|in:round,rect'
        ]);

        $area = ServiceArea::findOrFail($data['service_area_id']);
        if ($area->max_tables > 0 && $area->tables()->count() >= $area->max_tables) {
            return response()->json([
                'message' => "No se puede crear la mesa porque el área '{$area->name}' alcanzó su límite máximo de {$area->max_tables} mesas."
            ], 422);
        }

        $code = $data['table_code'];
        $token = Str::slug("mesa-{$area->id}-{$code}-" . Str::random(5));

        $table = RestaurantTable::create([
            'service_area_id' => $area->id,
            'table_code' => $code,
            'name' => $data['name'] ?? "Mesa {$code}",
            'capacity' => $data['capacity'],
            'shape' => $data['shape'] ?? (($data['capacity'] <= 5 || $data['capacity'] % 2 !== 0) ? 'round' : 'rect'),
            'qr_token' => $token,
            'status' => 'available',
            'is_active' => true
        ]);

        AuditLog::record('Crear Mesa', 'Salón', ['code' => $table->table_code, 'area' => $area->name]);

        return response()->json(['message' => 'Mesa creada exitosamente', 'table' => $table], 201);
    }

    /**
     * PUT /api/admin/mesas/{id} (Single Mesa Edit)
     */
    public function updateMesa(Request $request, $id)
    {
        $table = RestaurantTable::findOrFail($id);

        $data = $request->validate([
            'service_area_id' => 'nullable|exists:service_areas,id',
            'table_code' => 'nullable|string|max:20',
            'name' => 'nullable|string|max:50',
            'capacity' => 'nullable|integer|min:1',
            'shape' => 'nullable|string|in:round,rect',
            'status' => 'nullable|string|in:available,occupied,reserved'
        ]);

        $table->update($data);
        AuditLog::record('Editar Mesa', 'Salón', ['table_id' => $table->id, 'code' => $table->table_code]);

        return response()->json(['message' => 'Mesa actualizada correctamente', 'table' => $table]);
    }

    /**
     * PATCH /api/admin/mesas/{id}/liberar
     */
    public function liberarMesa($id)
    {
        $table = RestaurantTable::findOrFail($id);
        $table->status = 'available';
        $table->save();

        AuditLog::record('Liberar Mesa Manual', 'Salón', ['table' => $table->name, 'code' => $table->table_code]);

        return response()->json(['message' => "Mesa '{$table->name}' liberada y marcada como disponible (🟢)", 'table' => $table]);
    }

    /**
     * PATCH /api/admin/mesas/{id}/status
     */
    public function toggleTableStatus(Request $request, $id)
    {
        $table = RestaurantTable::findOrFail($id);
        $newStatus = $request->input('is_active', !$table->is_active);

        if (!$newStatus) {
            $activeOrder = Order::where('restaurant_table_id', $table->id)
                ->whereNotIn('status', ['delivered', 'cancelled'])
                ->exists();

            if ($table->status === 'occupied' || $activeOrder) {
                return response()->json([
                    'message' => "No se puede dar de baja la mesa '{$table->name}' porque está actualmente ocupada o tiene una comanda activa sin cobrar."
                ], 409);
            }
        }

        $table->is_active = $newStatus;
        $table->save();

        AuditLog::record('Cambiar Estado Activo Mesa', 'Salón', ['table' => $table->name, 'new_status' => $newStatus]);

        return response()->json(['message' => 'Estado de la mesa actualizado correctamente', 'table' => $table]);
    }

    /**
     * DELETE /api/admin/mesas/{id}
     * Safely delete a single table from a salon area
     */
    public function deleteMesa($id)
    {
        $table = RestaurantTable::findOrFail($id);

        if (\Illuminate\Support\Facades\Schema::hasTable('orders')) {
            $activeOrder = Order::where('restaurant_table_id', $table->id)
                ->whereNotIn('status', ['delivered', 'cancelled'])
                ->exists();

            if ($table->status === 'occupied' || $activeOrder) {
                return response()->json([
                    'message' => "No se puede eliminar la mesa '{$table->name}' porque está actualmente ocupada o tiene una comanda activa sin cobrar."
                ], 422);
            }
        }

        $tableName = $table->name;
        $areaName = $table->serviceArea ? $table->serviceArea->name : 'Salón';

        $table->delete();

        AuditLog::record('Eliminar Mesa', 'Salón', [
            'table_name' => $tableName,
            'area' => $areaName
        ]);

        return response()->json([
            'message' => "La mesa '{$tableName}' ha sido eliminada exitosamente del salón '{$areaName}'."
        ]);
    }

    /**
     * POST /api/admin/areas/{id}/mesas (Bulk Table Creation)
     */
    public function createTablesForArea(Request $request, $id)
    {
        $area = ServiceArea::findOrFail($id);

        $data = $request->validate([
            'modo' => 'required|string|in:individual,masivo',
            'table_code' => 'nullable|string|max:20',
            'name' => 'nullable|string|max:50',
            'capacity' => 'nullable|integer|min:1',
            'prefijo' => 'nullable|string|max:10',
            'cantidad' => 'nullable|integer|min:1|max:50',
            'capacidad_por_mesa' => 'nullable|integer|min:1'
        ]);

        $currentCount = $area->tables()->count();
        $tablesToCreate = ($data['modo'] === 'masivo') ? ($data['cantidad'] ?? 1) : 1;

        if ($area->max_tables > 0 && ($currentCount + $tablesToCreate) > $area->max_tables) {
            return response()->json([
                'message' => "Acción denegada: La creación de {$tablesToCreate} mesa(s) excede el límite máximo de {$area->max_tables} mesas permitidas para el área '{$area->name}' (Actuales: {$currentCount})."
            ], 422);
        }

        $createdTables = [];

        DB::transaction(function () use ($area, $data, &$createdTables, $currentCount) {
            if ($data['modo'] === 'masivo') {
                $prefijo = $data['prefijo'] ?? 'M';
                $cantidad = $data['cantidad'] ?? 1;
                $capacidad = $data['capacidad_por_mesa'] ?? 4;

                for ($i = 1; $i <= $cantidad; $i++) {
                    $secuencia = $currentCount + $i;
                    $code = "{$prefijo}-{$secuencia}";
                    $token = Str::slug("mesa-{$area->id}-{$code}-" . Str::random(5));

                    $table = RestaurantTable::create([
                        'service_area_id' => $area->id,
                        'table_code' => $code,
                        'name' => "Mesa {$code}",
                        'capacity' => $capacidad,
                        'shape' => ($capacidad <= 5 || $capacidad % 2 !== 0) ? 'round' : 'rect',
                        'qr_token' => $token,
                        'status' => 'available',
                        'is_active' => true
                    ]);

                    $createdTables[] = $table;
                }
            } else {
                $code = $data['table_code'] ?? ('M-' . ($currentCount + 1));
                $token = Str::slug("mesa-{$area->id}-{$code}-" . Str::random(5));

                $table = RestaurantTable::create([
                    'service_area_id' => $area->id,
                    'table_code' => $code,
                    'name' => $data['name'] ?? "Mesa {$code}",
                    'capacity' => $data['capacity'] ?? 4,
                    'shape' => ($data['capacity'] <= 5 || $data['capacity'] % 2 !== 0) ? 'round' : 'rect',
                    'qr_token' => $token,
                    'status' => 'available',
                    'is_active' => true
                ]);

                $createdTables[] = $table;
            }
        });

        AuditLog::record('Creación Masiva de Mesas', 'Salón', ['area' => $area->name, 'quantity' => count($createdTables)]);

        return response()->json([
            'message' => 'Mesa(s) registrada(s) exitosamente',
            'tables' => $createdTables
        ], 201);
    }

    /* ═════════════════════════════════════════════════════════════════════════════
       CU-2.1.4: MOVER Y FUSIONAR MESAS EN PLANO 2D
       ═════════════════════════════════════════════════════════════════════════════ */

    /**
     * POST /api/admin/mesas/mover
     */
    public function moverMesa(Request $request)
    {
        $data = $request->validate([
            'source_table_id' => 'required|exists:restaurant_tables,id',
            'target_table_id' => 'required|exists:restaurant_tables,id|different:source_table_id'
        ]);

        $sourceTable = RestaurantTable::findOrFail($data['source_table_id']);
        $targetTable = RestaurantTable::findOrFail($data['target_table_id']);

        if (!$targetTable->is_active) {
            return response()->json(['message' => "La mesa de destino '{$targetTable->name}' está inactiva."], 422);
        }

        if ($targetTable->status === 'occupied') {
            return response()->json(['message' => "La mesa de destino '{$targetTable->name}' ya está ocupada. Utiliza la opción 'Fusionar Mesas' para unir las comandas."], 422);
        }

        return DB::transaction(function () use ($sourceTable, $targetTable) {
            // Reassign active orders
            Order::where('restaurant_table_id', $sourceTable->id)
                ->whereNotIn('status', ['delivered', 'cancelled'])
                ->update(['restaurant_table_id' => $targetTable->id]);

            $targetTable->status = 'occupied';
            $targetTable->save();

            $sourceTable->status = 'available';
            $sourceTable->save();

            AuditLog::record(
                'Mover Mesa',
                'Salón',
                [
                    'source_table' => $sourceTable->name,
                    'target_table' => $targetTable->name,
                    'source_code' => $sourceTable->table_code,
                    'target_code' => $targetTable->table_code
                ]
            );

            return response()->json([
                'message' => "Comanda y mesa transferidas exitosamente de '{$sourceTable->name}' a '{$targetTable->name}'.",
                'source_table' => $sourceTable,
                'target_table' => $targetTable
            ]);
        });
    }

    /**
     * POST /api/admin/mesas/fusionar
     */
    public function fusionarMesas(Request $request)
    {
        $data = $request->validate([
            'source_table_id' => 'required|exists:restaurant_tables,id',
            'target_table_id' => 'required|exists:restaurant_tables,id|different:source_table_id'
        ]);

        $sourceTable = RestaurantTable::findOrFail($data['source_table_id']);
        $targetTable = RestaurantTable::findOrFail($data['target_table_id']);

        if (!$targetTable->is_active) {
            return response()->json(['message' => "La mesa de destino '{$targetTable->name}' está inactiva."], 422);
        }

        return DB::transaction(function () use ($sourceTable, $targetTable) {
            $sourceOrders = Order::where('restaurant_table_id', $sourceTable->id)
                ->whereNotIn('status', ['delivered', 'cancelled'])
                ->get();

            $targetOrder = Order::where('restaurant_table_id', $targetTable->id)
                ->whereNotIn('status', ['delivered', 'cancelled'])
                ->first();

            if ($targetOrder) {
                foreach ($sourceOrders as $sOrder) {
                    foreach ($sOrder->items as $item) {
                        $item->order_id = $targetOrder->id;
                        $item->save();
                    }
                    $sOrder->status = 'cancelled';
                    $sOrder->customer_notes = ($sOrder->customer_notes ? $sOrder->customer_notes . ' ' : '') . "[Fusionada con comanda {$targetOrder->order_number}]";
                    $sOrder->save();
                }

                $subtotal = $targetOrder->items()->sum('total');
                $modifiersTotal = $targetOrder->items()->sum('modifiers_total');
                $targetOrder->subtotal = $subtotal;
                $targetOrder->modifiers_total = $modifiersTotal;
                $targetOrder->total = $subtotal;
                $targetOrder->save();
            } else {
                foreach ($sourceOrders as $sOrder) {
                    $sOrder->restaurant_table_id = $targetTable->id;
                    $sOrder->save();
                }
            }

            $targetTable->status = 'occupied';
            $targetTable->save();

            $sourceTable->status = 'available';
            $sourceTable->save();

            AuditLog::record(
                'Fusionar Mesas',
                'Salón',
                [
                    'source_table' => $sourceTable->name,
                    'target_table' => $targetTable->name,
                    'orders_merged' => $sourceOrders->count()
                ]
            );

            return response()->json([
                'message' => "Cuentas de '{$sourceTable->name}' y '{$targetTable->name}' fusionadas exitosamente bajo la mesa '{$targetTable->name}'.",
                'source_table' => $sourceTable,
                'target_table' => $targetTable
            ]);
        });
    }

    /* ═════════════════════════════════════════════════════════════════════════════
       CU-2.2.3: CANCELACIÓN DE PLATILLOS CON PIN DE SEGURIDAD
       ═════════════════════════════════════════════════════════════════════════════ */

    /**
     * POST /api/admin/comandas/anular-item
     */
    public function anularItem(Request $request)
    {
        $data = $request->validate([
            'order_item_id' => 'required|exists:order_items,id',
            'pin' => 'required|string',
            'reason' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        $validPins = ['1234', '9999', '0000', '4321'];
        if (!in_array($data['pin'], $validPins)) {
            return response()->json(['message' => 'PIN de autorización de Gerencia / Capitán incorrecto.'], 403);
        }

        $item = OrderItem::with('order.table')->findOrFail($data['order_item_id']);
        $order = $item->order;

        return DB::transaction(function () use ($item, $order, $data) {
            $productName = $item->product_name;
            $qty = $item->quantity;
            $itemTotal = $item->total;

            $item->delete();

            $remainingItems = OrderItem::where('order_id', $order->id)->get();
            if ($remainingItems->isEmpty()) {
                $order->status = 'cancelled';
                $order->total = 0;
                $order->save();

                if ($order->restaurant_table_id) {
                    $table = RestaurantTable::find($order->restaurant_table_id);
                    if ($table) {
                        $table->status = 'available';
                        $table->save();
                    }
                }
            } else {
                $subtotal = $remainingItems->sum('total');
                $modifiersTotal = $remainingItems->sum('modifiers_total');
                $order->subtotal = $subtotal;
                $order->modifiers_total = $modifiersTotal;
                $order->total = $subtotal;
                $order->save();
            }

            AuditLog::record(
                'Anulación de Platillo',
                'Comandas',
                [
                    'order_number' => $order->order_number,
                    'table_name' => $order->table ? $order->table->name : 'N/A',
                    'product_name' => $productName,
                    'quantity' => $qty,
                    'amount_annulled' => $itemTotal,
                    'reason' => $data['reason'],
                    'notes' => $data['notes'] ?? null,
                    'pin_authorized' => true
                ]
            );

            return response()->json([
                'message' => "Platillo '{$productName}' anulado correctamente por Gerencia.",
                'order' => $order->fresh(['items'])
            ]);
        });
    }

    /* ═════════════════════════════════════════════════════════════════════════════
       CU-5.2.3: DIVISIÓN DE CUENTAS AL MOMENTO DE COBRAR (SPLIT BILL)
       ═════════════════════════════════════════════════════════════════════════════ */

    /**
     * POST /api/admin/mesas/cobrar-split
     */
    public function cobrarSplit(Request $request)
    {
        $data = $request->validate([
            'table_id' => 'required|exists:restaurant_tables,id',
            'mode' => 'required|string|in:equal,items',
            'payments' => 'required|array|min:1',
            'payments.*.amount' => 'required|numeric|min:0.01',
            'payments.*.payment_method_id' => 'nullable|integer'
        ]);

        $table = RestaurantTable::findOrFail($data['table_id']);
        $order = Order::where('restaurant_table_id', $table->id)
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->first();

        if (!$order) {
            return response()->json(['message' => 'No se encontró una comanda activa para cobrar en esta mesa.'], 404);
        }

        return DB::transaction(function () use ($table, $order, $data) {
            $totalPaidInTx = 0;

            foreach ($data['payments'] as $pData) {
                $amt = (float)$pData['amount'];
                $totalPaidInTx += $amt;

                Payment::create([
                    'order_id' => $order->id,
                    'payment_method_id' => $pData['payment_method_id'] ?? 1,
                    'amount' => $amt,
                    'status' => 'completed',
                    'transaction_reference' => 'SPLIT-' . strtoupper(Str::random(6))
                ]);
            }

            $allPaymentsTotal = Payment::where('order_id', $order->id)->where('status', 'completed')->sum('amount');
            $remaining = round($order->total - $allPaymentsTotal, 2);

            $isFullyPaid = $remaining <= 0.05;

            if ($isFullyPaid) {
                $order->payment_status = 'paid';
                $order->status = 'delivered';
                $order->delivered_at = now();
                $order->save();

                $table->status = 'available';
                $table->save();
            }

            AuditLog::record(
                'Cobro Split Bill',
                'Caja',
                [
                    'order_number' => $order->order_number,
                    'table_name' => $table->name,
                    'mode' => $data['mode'],
                    'parts_paid' => count($data['payments']),
                    'total_order' => $order->total,
                    'total_paid_accumulated' => $allPaymentsTotal,
                    'is_fully_paid' => $isFullyPaid
                ]
            );

            return response()->json([
                'message' => $isFullyPaid 
                    ? "¡Cuenta cubierta al 100%! Mesa '{$table->name}' desocupada y marcada como Disponible (🟢)." 
                    : "Pago parcial de $" . number_format($totalPaidInTx, 2) . " registrado. Saldo pendiente: $" . number_format(max(0, $remaining), 2),
                'is_fully_paid' => $isFullyPaid,
                'remaining' => max(0, $remaining),
                'order' => $order,
                'table' => $table
            ]);
        });
    }
}
