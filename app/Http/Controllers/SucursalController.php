<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SucursalController extends Controller
{
    /**
     * Display the Multi-Branch Management Blade View
     */
    public function index()
    {
        return view('admin.sucursales.index');
    }

    /**
     * GET /api/admin/sucursales
     */
    public function getSucursales()
    {
        $sucursales = Sucursal::orderBy('is_matriz', 'desc')->orderBy('id', 'asc')->get();
        $matriz = $sucursales->firstWhere('is_matriz', true);

        return response()->json([
            'sucursales' => $sucursales,
            'stats' => [
                'total' => $sucursales->count(),
                'activas' => $sucursales->where('is_active', true)->count(),
                'matriz_nombre' => $matriz ? $matriz->nombre : 'Sin Matriz Asignada',
            ]
        ]);
    }

    /**
     * POST /api/admin/sucursales
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:120',
            'direccion_calle' => 'required|string|max:255',
            'colonia_ciudad' => 'required|string|max:150',
            'codigo_postal' => 'required|string|max:20',
            'telefono_contacto' => 'required|string|max:30',
            'email_contacto' => 'required|email|max:120',
            'rfc_identificacion_fiscal' => 'nullable|string|max:30',
            'horario_apertura' => 'required|string|max:10',
            'horario_cierre' => 'required|string|max:10',
            'dias_operacion' => 'nullable|array',
            'is_matriz' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        if (!empty($data['is_matriz']) && $data['is_matriz']) {
            Sucursal::query()->update(['is_matriz' => false]);
        }

        $sucursal = Sucursal::create([
            'nombre' => $data['nombre'],
            'direccion_calle' => $data['direccion_calle'],
            'colonia_ciudad' => $data['colonia_ciudad'],
            'codigo_postal' => $data['codigo_postal'],
            'telefono_contacto' => $data['telefono_contacto'],
            'email_contacto' => $data['email_contacto'],
            'rfc_identificacion_fiscal' => $data['rfc_identificacion_fiscal'] ?? null,
            'horario_apertura' => $data['horario_apertura'],
            'horario_cierre' => $data['horario_cierre'],
            'dias_operacion' => $data['dias_operacion'] ?? ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'],
            'is_matriz' => $data['is_matriz'] ?? false,
            'is_active' => $data['is_active'] ?? true,
        ]);

        AuditLog::record(
            'Registro de Sucursal',
            'Multi-Branch',
            ['sucursal_id' => $sucursal->id, 'nombre' => $sucursal->nombre, 'is_matriz' => $sucursal->is_matriz]
        );

        return response()->json([
            'message' => "Sucursal '{$sucursal->nombre}' registrada exitosamente",
            'sucursal' => $sucursal
        ], 201);
    }

    /**
     * PUT /api/admin/sucursales/{id}
     */
    public function update(Request $request, $id)
    {
        $sucursal = Sucursal::findOrFail($id);

        $data = $request->validate([
            'nombre' => 'required|string|max:120',
            'direccion_calle' => 'required|string|max:255',
            'colonia_ciudad' => 'required|string|max:150',
            'codigo_postal' => 'required|string|max:20',
            'telefono_contacto' => 'required|string|max:30',
            'email_contacto' => 'required|email|max:120',
            'rfc_identificacion_fiscal' => 'nullable|string|max:30',
            'horario_apertura' => 'required|string|max:10',
            'horario_cierre' => 'required|string|max:10',
            'dias_operacion' => 'nullable|array',
            'is_matriz' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        if (!empty($data['is_matriz']) && $data['is_matriz']) {
            Sucursal::where('id', '!=', $id)->update(['is_matriz' => false]);
        }

        $sucursal->update([
            'nombre' => $data['nombre'],
            'direccion_calle' => $data['direccion_calle'],
            'colonia_ciudad' => $data['colonia_ciudad'],
            'codigo_postal' => $data['codigo_postal'],
            'telefono_contacto' => $data['telefono_contacto'],
            'email_contacto' => $data['email_contacto'],
            'rfc_identificacion_fiscal' => $data['rfc_identificacion_fiscal'] ?? null,
            'horario_apertura' => $data['horario_apertura'],
            'horario_cierre' => $data['horario_cierre'],
            'dias_operacion' => $data['dias_operacion'] ?? $sucursal->dias_operacion,
            'is_matriz' => $data['is_matriz'] ?? $sucursal->is_matriz,
            'is_active' => $data['is_active'] ?? $sucursal->is_active,
        ]);

        AuditLog::record(
            'Actualización de Sucursal',
            'Multi-Branch',
            ['sucursal_id' => $sucursal->id, 'nombre' => $sucursal->nombre]
        );

        return response()->json([
            'message' => "Sucursal '{$sucursal->nombre}' actualizada correctamente",
            'sucursal' => $sucursal
        ]);
    }

    /**
     * PATCH /api/admin/sucursales/{id}/status
     */
    public function toggleStatus(Request $request, $id)
    {
        $sucursal = Sucursal::findOrFail($id);
        $newStatus = $request->input('is_active', !$sucursal->is_active);

        if (!$newStatus && $sucursal->is_matriz) {
            return response()->json([
                'message' => "No se puede desactivar la Sucursal Matriz Principal '{$sucursal->nombre}'. Asigna otra Matriz antes de desactivarla."
            ], 409);
        }

        $sucursal->is_active = $newStatus;
        $sucursal->save();

        AuditLog::record(
            'Cambio Estado Sucursal',
            'Multi-Branch',
            ['sucursal' => $sucursal->nombre, 'is_active' => $newStatus]
        );

        return response()->json([
            'message' => "Estado de la sucursal '{$sucursal->nombre}' actualizado",
            'sucursal' => $sucursal
        ]);
    }

    /**
     * DELETE /api/admin/sucursales/{id}
     */
    public function destroy($id)
    {
        $sucursal = Sucursal::findOrFail($id);

        if ($sucursal->is_matriz) {
            return response()->json([
                'message' => "No se puede eliminar la Sucursal Matriz Principal. Asigna primero otra matriz."
            ], 409);
        }

        $nombre = $sucursal->nombre;
        $sucursal->delete();

        AuditLog::record(
            'Eliminación de Sucursal',
            'Multi-Branch',
            ['sucursal' => $nombre]
        );

        return response()->json(['message' => "Sucursal '{$nombre}' eliminada exitosamente"]);
    }
}
