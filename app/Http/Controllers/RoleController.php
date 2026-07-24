<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RoleController extends Controller
{
    /**
     * Display the Roles & Permissions Management Blade View
     */
    public function index()
    {
        return view('admin.roles.index');
    }

    /**
     * GET /api/admin/roles
     */
    public function getRoles()
    {
        $roles = Role::withCount('users')->orderBy('id', 'asc')->get();
        $totalUsers = User::count();
        $activeRoles = $roles->where('is_active', true)->count();

        return response()->json([
            'roles' => $roles,
            'stats' => [
                'total_roles' => $roles->count(),
                'active_roles' => $activeRoles,
                'total_employees' => $totalUsers,
            ]
        ]);
    }

    /**
     * POST /api/admin/roles
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'display_name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'permissions_json' => 'nullable|array',
            'is_active' => 'nullable|boolean'
        ]);

        $displayName = $data['display_name'];
        $slug = !empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($displayName);
        $name = Str::slug($displayName, '_');

        if (Role::where('slug', $slug)->orWhere('name', $name)->exists()) {
            return response()->json(['message' => "Ya existe un rol registrado con el nombre o identificador '{$displayName}'."], 422);
        }

        $role = Role::create([
            'name' => $name,
            'slug' => $slug,
            'display_name' => $displayName,
            'description' => $data['description'] ?? null,
            'permissions_json' => $data['permissions_json'] ?? [],
            'is_active' => $data['is_active'] ?? true,
        ]);

        AuditLog::record(
            'Creación de Rol',
            'Roles y Permisos',
            ['role_id' => $role->id, 'display_name' => $role->display_name, 'permissions' => $role->permissions_json]
        );

        return response()->json([
            'message' => "Rol '{$role->display_name}' registrado exitosamente",
            'role' => $role
        ], 201);
    }

    /**
     * PUT /api/admin/roles/{id}
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $data = $request->validate([
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'permissions_json' => 'nullable|array',
            'is_active' => 'nullable|boolean'
        ]);

        $role->update([
            'display_name' => $data['display_name'],
            'description' => $data['description'] ?? null,
            'permissions_json' => $data['permissions_json'] ?? [],
            'is_active' => $data['is_active'] ?? $role->is_active,
        ]);

        AuditLog::record(
            'Modificación de Rol',
            'Roles y Permisos',
            ['role_id' => $role->id, 'display_name' => $role->display_name, 'permissions' => $role->permissions_json]
        );

        return response()->json([
            'message' => "Rol '{$role->display_name}' actualizado correctamente",
            'role' => $role
        ]);
    }

    /**
     * PATCH /api/admin/roles/{id}/status
     */
    public function toggleStatus(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $newStatus = $request->input('is_active', !$role->is_active);

        if (!$newStatus && $role->users()->count() > 0) {
            return response()->json([
                'message' => "No se puede desactivar el rol '{$role->display_name}' porque tiene {$role->users()->count()} empleado(s) asignado(s). Reasigna los empleados primero."
            ], 409);
        }

        $role->is_active = $newStatus;
        $role->save();

        AuditLog::record(
            'Cambio Estado de Rol',
            'Roles y Permisos',
            ['role' => $role->display_name, 'new_status' => $newStatus]
        );

        return response()->json([
            'message' => "Estado del rol '{$role->display_name}' actualizado correctamente",
            'role' => $role
        ]);
    }

    /**
     * DELETE /api/admin/roles/{id}
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        if ($role->users()->count() > 0) {
            return response()->json([
                'message' => "No se puede eliminar el rol '{$role->display_name}' porque tiene {$role->users()->count()} empleado(s) asociado(s)."
            ], 409);
        }

        $roleName = $role->display_name;
        $role->delete();

        AuditLog::record(
            'Eliminación de Rol',
            'Roles y Permisos',
            ['role' => $roleName]
        );

        return response()->json(['message' => "Rol '{$roleName}' dado de baja exitosamente"]);
    }

    /* ═════════════════════════════════════════════════════════════════════════════
       GESTIÓN DE PERSONAL ASIGNADO Y EMPLEADOS POR ROL
       ═════════════════════════════════════════════════════════════════════════════ */

    /**
     * GET /api/admin/roles/{id}/empleados
     * Retrieves assigned employees for a role and unassigned/other employees
     */
    public function getRoleEmployees($id)
    {
        $role = Role::findOrFail($id);

        $assignedEmployees = User::where('role_id', $role->id)
            ->select('id', 'name', 'email', 'phone', 'pin_code', 'is_active', 'created_at')
            ->get();

        $unassignedEmployees = User::where(function ($query) use ($role) {
                $query->whereNull('role_id')
                      ->orWhere('role_id', '!=', $role->id);
            })
            ->with('role')
            ->select('id', 'name', 'email', 'phone', 'role_id', 'is_active')
            ->get();

        return response()->json([
            'role' => $role,
            'assigned' => $assignedEmployees,
            'unassigned' => $unassignedEmployees,
            'assigned_count' => $assignedEmployees->count()
        ]);
    }

    /**
     * POST /api/admin/roles/{id}/asignar-empleado
     * Handles Tab 1 (Crear Nuevo Empleado) and Tab 2 (Asignar Empleado Existente)
     */
    public function asignarEmpleado(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $modo = $request->input('modo', 'crear');

        if ($modo === 'existente') {
            $data = $request->validate([
                'user_id' => 'required|exists:users,id'
            ]);

            $user = User::findOrFail($data['user_id']);
            $user->role_id = $role->id;
            $user->save();

            AuditLog::record(
                'Asignación de Empleado Existente a Rol',
                'Roles y Permisos',
                ['employee_name' => $user->name, 'role_name' => $role->display_name]
            );

            return response()->json([
                'message' => "Empleado '{$user->name}' asignado exitosamente al rol '{$role->display_name}'",
                'user' => $user,
                'role_count' => $role->users()->count()
            ]);
        }

        // Modo: Crear Nuevo Empleado
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'pin_code' => 'nullable|string|max:10',
            'password' => 'nullable|string|min:4',
            'is_active' => 'nullable|boolean'
        ]);

        $user = User::create([
            'role_id' => $role->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'pin_code' => $data['pin_code'] ?? '1234',
            'password' => Hash::make($data['password'] ?? '1234'),
            'is_active' => $data['is_active'] ?? true,
        ]);

        AuditLog::record(
            'Registro y Asignación de Nuevo Empleado',
            'Roles y Permisos',
            ['employee_name' => $user->name, 'email' => $user->email, 'role_name' => $role->display_name]
        );

        return response()->json([
            'message' => "Nuevo empleado '{$user->name}' creado y asignado al rol '{$role->display_name}'",
            'user' => $user,
            'role_count' => $role->users()->count()
        ], 201);
    }

    /**
     * DELETE /api/admin/roles/{role}/desvincular-empleado/{user}
     */
    public function desvincularEmpleado($roleId, $userId)
    {
        $role = Role::findOrFail($roleId);
        $user = User::where('id', $userId)->where('role_id', $role->id)->firstOrFail();

        $userName = $user->name;
        $user->role_id = null;
        $user->save();

        AuditLog::record(
            'Desvinculación de Empleado de Rol',
            'Roles y Permisos',
            ['employee_name' => $userName, 'role_name' => $role->display_name]
        );

        return response()->json([
            'message' => "Empleado '{$userName}' desvinculado del rol '{$role->display_name}'",
            'role_count' => $role->users()->count()
        ]);
    }
}
