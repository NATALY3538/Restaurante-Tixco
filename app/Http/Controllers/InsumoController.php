<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Insumo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InsumoController extends Controller
{
    /**
     * Display main Blade view for Insumos Management
     */
    public function index()
    {
        return view('admin.insumos.index');
    }

    /**
     * GET /api/admin/insumos
     * Fetch list of insumos & inventory stats summary
     */
    public function getInsumos()
    {
        $insumos = Insumo::orderBy('category')->orderBy('name')->get();

        $totalItems = $insumos->count();
        $lowStockCount = $insumos->filter(fn($i) => $i->stock_status !== 'ok')->count();
        $totalInventoryValue = $insumos->sum(fn($i) => $i->total_value);

        $categories = $insumos->pluck('category')->unique()->values();

        return response()->json([
            'insumos' => $insumos->map(function ($i) {
                return [
                    'id' => $i->id,
                    'code' => $i->code,
                    'name' => $i->name,
                    'category' => $i->category,
                    'unit_of_measure' => $i->unit_of_measure,
                    'stock_quantity' => (float)$i->stock_quantity,
                    'min_stock_alert' => (float)$i->min_stock_alert,
                    'unit_cost' => (float)$i->unit_cost,
                    'total_value' => $i->total_value,
                    'stock_status' => $i->stock_status,
                    'description' => $i->description,
                    'is_active' => (bool)$i->is_active,
                    'created_at' => $i->created_at->format('Y-m-d H:i')
                ];
            }),
            'stats' => [
                'total_items' => $totalItems,
                'low_stock_count' => $lowStockCount,
                'total_inventory_value' => round($totalInventoryValue, 2)
            ],
            'categories' => $categories
        ]);
    }

    /**
     * POST /api/admin/insumos
     * Store new insumo / ingredient
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'nullable|string|max:50|unique:insumos,code',
            'name' => 'required|string|max:150',
            'category' => 'required|string|max:50',
            'unit_of_measure' => 'required|string|max:20',
            'stock_quantity' => 'required|numeric|min:0',
            'min_stock_alert' => 'nullable|numeric|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean'
        ]);

        if (empty($data['code'])) {
            $prefix = strtoupper(substr(Str::slug($data['category'] ?: 'INS'), 0, 3));
            $data['code'] = 'INS-' . $prefix . '-' . str_pad((Insumo::max('id') + 1), 3, '0', STR_PAD_LEFT);
        }

        $insumo = Insumo::create([
            'code' => $data['code'],
            'name' => $data['name'],
            'category' => $data['category'],
            'unit_of_measure' => $data['unit_of_measure'],
            'stock_quantity' => $data['stock_quantity'],
            'min_stock_alert' => $data['min_stock_alert'] ?? 5.000,
            'unit_cost' => $data['unit_cost'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true
        ]);

        AuditLog::record('Crear Insumo', 'Inventario Insumos', [
            'code' => $insumo->code,
            'name' => $insumo->name,
            'stock' => $insumo->stock_quantity,
            'cost' => $insumo->unit_cost
        ]);

        return response()->json([
            'message' => "Insumo '{$insumo->name}' registrado exitosamente.",
            'insumo' => $insumo
        ], 201);
    }

    /**
     * PUT /api/admin/insumos/{id}
     * Update existing insumo / ingredient
     */
    public function update(Request $request, $id)
    {
        $insumo = Insumo::findOrFail($id);

        $data = $request->validate([
            'code' => 'nullable|string|max:50|unique:insumos,code,' . $id,
            'name' => 'required|string|max:150',
            'category' => 'required|string|max:50',
            'unit_of_measure' => 'required|string|max:20',
            'stock_quantity' => 'required|numeric|min:0',
            'min_stock_alert' => 'nullable|numeric|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean'
        ]);

        $insumo->update($data);

        AuditLog::record('Editar Insumo', 'Inventario Insumos', [
            'code' => $insumo->code,
            'name' => $insumo->name,
            'stock' => $insumo->stock_quantity,
            'cost' => $insumo->unit_cost
        ]);

        return response()->json([
            'message' => "Insumo '{$insumo->name}' actualizado correctamente.",
            'insumo' => $insumo
        ]);
    }

    /**
     * DELETE /api/admin/insumos/{id}
     * Safely delete insumo
     */
    public function destroy($id)
    {
        $insumo = Insumo::findOrFail($id);
        $name = $insumo->name;
        $code = $insumo->code;

        $insumo->delete();

        AuditLog::record('Eliminar Insumo', 'Inventario Insumos', [
            'code' => $code,
            'name' => $name
        ]);

        return response()->json([
            'message' => "El insumo '{$name}' (#{$code}) ha sido eliminado del catálogo."
        ]);
    }
}
