<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Render the Audit Log Blade view
     */
    public function index()
    {
        return view('admin.bitacora');
    }

    /**
     * GET /api/admin/audit-logs
     */
    public function getLogs(Request $request)
    {
        $query = AuditLog::query();

        if ($request->filled('module')) {
            $query->where('module', $request->input('module'));
        }

        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->input('action') . '%');
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('module', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%")
                  ->orWhere('details_json', 'like', "%{$search}%");
            });
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);

        // Stats summary
        $totalLogs = AuditLog::count();
        $cancellationsCount = AuditLog::where('module', 'Comandas')->count();
        $floorChangesCount = AuditLog::where('module', 'Salón')->count();
        $wasteCount = AuditLog::where('module', 'Inventario')->count();

        return response()->json([
            'logs' => $logs,
            'stats' => [
                'total' => $totalLogs,
                'comandas' => $cancellationsCount,
                'salon' => $floorChangesCount,
                'inventario' => $wasteCount,
            ]
        ]);
    }

    /**
     * POST /api/admin/audit-logs (Manual trigger e.g. Manual Cash Drawer Open)
     */
    public function storeManualLog(Request $request)
    {
        $data = $request->validate([
            'action' => 'required|string|max:100',
            'module' => 'required|string|max:50',
            'details' => 'nullable|array'
        ]);

        $log = AuditLog::record(
            $data['action'],
            $data['module'],
            $data['details'] ?? []
        );

        return response()->json([
            'message' => 'Evento de auditoría registrado correctamente',
            'log' => $log
        ], 201);
    }
}
