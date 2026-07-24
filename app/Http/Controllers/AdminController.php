<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductInventory;
use App\Models\InventoryMovement;
use App\Models\ServiceArea;
use App\Models\RestaurantTable;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    // GET /api/admin/productos
    public function getProductos()
    {
        $products = Product::with(['category', 'inventory'])->get();
        return response()->json($products);
    }

    // POST /api/admin/productos
    public function createProducto(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:160',
            'category_id' => 'required|integer',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string',
            'estimated_preparation_minutes' => 'nullable|integer',
            'is_vegetarian' => 'nullable|boolean',
            'is_spicy' => 'nullable|boolean',
            'is_gluten_free' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'stock' => 'nullable|integer|min:0'
        ]);

        return DB::transaction(function () use ($data) {
            $slug = Str::slug($data['name']);
            // Ensure unique slug
            $originalSlug = $slug;
            $count = 1;
            while (Product::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }

            $product = Product::create([
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'slug' => $slug,
                'description' => $data['description'] ?? null,
                'price' => $data['price'],
                'image_url' => $data['image_url'] ?? null,
                'estimated_preparation_minutes' => $data['estimated_preparation_minutes'] ?? 10,
                'is_vegetarian' => $data['is_vegetarian'] ?? false,
                'is_spicy' => $data['is_spicy'] ?? false,
                'is_gluten_free' => $data['is_gluten_free'] ?? false,
                'is_featured' => $data['is_featured'] ?? false,
                'is_active' => true,
            ]);

            $stock = $data['stock'] ?? 50;

            ProductInventory::create([
                'product_id' => $product->id,
                'stock' => $stock,
                'min_stock' => 5,
            ]);

            InventoryMovement::create([
                'product_id' => $product->id,
                'quantity_delta' => $stock,
                'type' => 'purchase',
                'notes' => 'Carga inicial del producto'
            ]);

            return response()->json($product->load('inventory'), 201);
        });
    }

    // PUT /api/admin/productos/{id}
    public function updateProducto(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:160',
            'category_id' => 'required|integer',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string',
            'estimated_preparation_minutes' => 'nullable|integer',
            'is_vegetarian' => 'nullable|boolean',
            'is_spicy' => 'nullable|boolean',
            'is_gluten_free' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'stock' => 'required|integer|min:0'
        ]);

        return DB::transaction(function () use ($request, $product, $data) {
            // Update product properties
            $product->update([
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'price' => $data['price'],
                'image_url' => $data['image_url'] ?? null,
                'estimated_preparation_minutes' => $data['estimated_preparation_minutes'] ?? 10,
                'is_vegetarian' => $data['is_vegetarian'] ?? false,
                'is_spicy' => $data['is_spicy'] ?? false,
                'is_gluten_free' => $data['is_gluten_free'] ?? false,
                'is_featured' => $data['is_featured'] ?? false,
                'is_active' => $data['is_active'] ?? true,
            ]);

            // Update inventory
            $inventory = ProductInventory::firstOrCreate(
                ['product_id' => $product->id],
                ['stock' => 0, 'min_stock' => 5]
            );

            $oldStock = $inventory->stock;
            $newStock = $data['stock'];

            if ($oldStock != $newStock) {
                $delta = $newStock - $oldStock;
                $inventory->stock = $newStock;
                $inventory->save();

                InventoryMovement::create([
                    'product_id' => $product->id,
                    'quantity_delta' => $delta,
                    'type' => 'adjustment',
                    'notes' => 'Ajuste manual del administrador'
                ]);
            }

            return response()->json($product->load('inventory'));
        });
    }

    // DELETE /api/admin/productos/{id}
    public function deleteProducto($id)
    {
        $product = Product::findOrFail($id);
        $product->is_active = false;
        $product->save();

        return response()->json(['message' => 'Producto dado de baja lógicamente']);
    }

    // GET /api/admin/inventario/movimientos
    public function getMovimientos()
    {
        $movs = InventoryMovement::with('product')
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($movs);
    }

    // ==========================================
    // GESTIÓN DINÁMICA DE ÁREAS Y MESAS
    // ==========================================

    // GET /api/admin/areas
    public function getAreas()
    {
        $areas = ServiceArea::with('tables')->withCount('tables')->get();
        return response()->json($areas);
    }

    // POST /api/admin/areas
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

        return response()->json($area, 201);
    }

    // PUT /api/admin/areas/{id}
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
        return response()->json($area);
    }

    // PATCH /api/admin/areas/{id}/status
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

        return response()->json(['message' => 'Estado del área actualizado correctamente', 'area' => $area]);
    }

    // POST /api/admin/areas/{id}/mesas
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
                    'qr_token' => $token,
                    'status' => 'available',
                    'is_active' => true
                ]);

                $createdTables[] = $table;
            }
        });

        return response()->json([
            'message' => 'Mesa(s) registrada(s) exitosamente',
            'tables' => $createdTables
        ], 201);
    }

    // PATCH /api/admin/mesas/{id}/status
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

        return response()->json(['message' => 'Estado de la mesa actualizado correctamente', 'table' => $table]);
    }

    // PUT /api/admin/mesas/{id}/reasignar
    public function reassignTableArea(Request $request, $id)
    {
        $table = RestaurantTable::findOrFail($id);
        $targetAreaId = $request->validate(['target_area_id' => 'required|exists:service_areas,id'])['target_area_id'];

        $targetArea = ServiceArea::findOrFail($targetAreaId);

        if ($targetArea->max_tables > 0 && $targetArea->tables()->count() >= $targetArea->max_tables) {
            return response()->json([
                'message' => "No se puede mover la mesa a '{$targetArea->name}' porque alcanzó su capacidad máxima de {$targetArea->max_tables} mesas."
            ], 422);
        }

        $table->service_area_id = $targetAreaId;
        $table->save();

        return response()->json(['message' => 'Mesa reasignada con éxito', 'table' => $table->load('serviceArea')]);
    }

    // POST /api/admin/mesas (Crear mesa única)
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

        return response()->json(['message' => 'Mesa creada exitosamente', 'table' => $table], 201);
    }

    // PUT /api/admin/mesas/{id} (Editar mesa)
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
        return response()->json(['message' => 'Mesa actualizada correctamente', 'table' => $table]);
    }

    // PATCH /api/admin/mesas/{id}/liberar (Desocupar / Liberar mesa)
    public function liberarMesa($id)
    {
        $table = RestaurantTable::findOrFail($id);
        $table->status = 'available';
        $table->save();

        return response()->json(['message' => "Mesa '{$table->name}' liberada y marcada como disponible (🟢)", 'table' => $table]);
    }
}
