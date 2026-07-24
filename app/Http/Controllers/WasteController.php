<?php

namespace App\Http\Controllers;

use App\Models\WasteRecord;
use App\Models\Product;
use App\Models\InventoryMovement;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WasteController extends Controller
{
    /**
     * Render the Waste Records Blade view
     */
    public function index()
    {
        return view('admin.inventario.mermas');
    }

    /**
     * GET /api/admin/mermas
     */
    public function getMermas()
    {
        $mermas = WasteRecord::with('product')->orderBy('created_at', 'desc')->get();
        $totalLoss = $mermas->sum('cost_total');
        $totalItems = $mermas->sum('quantity');

        return response()->json([
            'mermas' => $mermas,
            'summary' => [
                'total_loss' => (float)$totalLoss,
                'total_items' => (float)$totalItems,
                'count' => $mermas->count()
            ]
        ]);
    }

    /**
     * POST /api/admin/mermas
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|string|in:caducidad,accidente,error_preparacion,muestra,otro',
            'notes' => 'nullable|string|max:500'
        ]);

        $product = Product::findOrFail($data['product_id']);

        // 1. Extraer el Precio de Venta al Público (PVP) del producto como costo unitario base
        $costUnit = (float) ($product->price > 0 ? $product->price : $product->real_cost);

        // 2. Pérdida Total = Cantidad Descontada * PVP del Producto
        $costTotal = round($data['quantity'] * $costUnit, 2);

        return DB::transaction(function () use ($product, $data, $costUnit, $costTotal) {
            // 1. Registrar la Merma
            $waste = WasteRecord::create([
                'product_id' => $product->id,
                'quantity' => $data['quantity'],
                'cost_unit' => $costUnit,
                'cost_total' => $costTotal,
                'reason' => $data['reason'],
                'notes' => $data['notes'] ?? null,
                'registered_by' => auth()->user()->name ?? 'Admin / Gerente'
            ]);

            // 2. Descontar del inventario
            InventoryMovement::create([
                'product_id' => $product->id,
                'quantity_delta' => -$data['quantity'],
                'type' => 'waste',
                'notes' => "Merma por {$data['reason']}: " . ($data['notes'] ?? 'Sin notas')
            ]);

            // 3. Auditoría de Seguridad
            AuditLog::record(
                'Registro de Merma con PVP',
                'Inventario',
                [
                    'product_name' => $product->name,
                    'quantity' => $data['quantity'],
                    'pvp_unit' => $costUnit,
                    'cost_total_loss' => $costTotal,
                    'reason' => $data['reason'],
                    'notes' => $data['notes'] ?? null
                ]
            );

            return response()->json([
                'message' => "Merma de {$data['quantity']} x {$product->name} registrada exitosamente (PVP Unitario: $" . number_format($costUnit, 2) . ", Pérdida Total: $" . number_format($costTotal, 2) . ")",
                'waste' => $waste->load('product')
            ], 201);
        });
    }
}
