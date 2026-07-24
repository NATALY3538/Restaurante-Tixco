@extends('layouts.app')

@section('title', 'Gestión de Roles y Permisos de Empleados')

@section('styles')
<style>
.roles-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: var(--sp-md);
    margin-bottom: var(--sp-xl);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: var(--sp-md);
    margin-bottom: var(--sp-xl);
}

.stat-card {
    background: var(--clr-surface-1);
    border: 1px solid var(--clr-border);
    border-radius: var(--radius-lg);
    padding: var(--sp-lg);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.stat-val {
    font-size: 1.8rem;
    font-weight: 700;
    font-family: var(--font-display);
    color: var(--clr-primary);
    margin-top: 4px;
}

.roles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
    gap: var(--sp-lg);
    margin-bottom: var(--sp-xl);
}

.role-card {
    background: var(--clr-surface-1);
    border: 1px solid var(--clr-border);
    border-radius: var(--radius-lg);
    padding: var(--sp-lg);
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.2s ease, border-color 0.2s ease;
}

.role-card:hover {
    transform: translateY(-3px);
    border-color: var(--clr-primary);
}

.perm-badge {
    background: var(--clr-surface-2);
    border: 1px solid var(--clr-border);
    color: var(--clr-text-muted);
    font-size: 0.72rem;
    padding: 3px 8px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.tab-btn {
    flex: 1;
    padding: 10px;
    font-size: 0.85rem;
    font-weight: 600;
    background: var(--clr-surface-2);
    color: var(--clr-text-muted);
    border: 1px solid var(--clr-border);
    border-radius: var(--radius-sm);
    cursor: pointer;
    transition: all 0.2s ease;
}

.tab-btn.active {
    background: var(--clr-primary);
    color: #ffffff;
    border-color: var(--clr-primary);
}
</style>
@endsection

@section('content')
<div class="container" style="padding-top: var(--sp-xl); padding-bottom: var(--sp-xxl);">
    <!-- Header -->
    <div class="roles-header">
        <div>
            <h1 style="font-family:var(--font-display); font-size:2rem; margin-bottom:0.25rem; color:var(--clr-primary);">
                🛡️ Gestión de Roles y Personal Asignado
            </h1>
            <p style="color:var(--clr-text-muted); font-size:0.95rem;">
                Administra perfiles de acceso (Chef, Recepcionista, Mesero, Cajero, Capitán, Administrador) y asigna empleados.
            </p>
        </div>
        <div style="display:flex; gap:var(--sp-sm); flex-wrap:wrap;">
            <button onclick="openRoleModal()" class="btn btn-primary" style="display:flex; align-items:center; gap:6px; background:var(--clr-primary); font-weight:700;">
                ➕ Nuevo Rol de Empleado
            </button>
            <a href="/admin" class="btn btn-secondary">
                ⬅️ Panel General
            </a>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <span style="font-size:0.85rem; color:var(--clr-text-muted);">Roles Configurados</span>
            <div class="stat-val" id="statTotalRoles">0</div>
        </div>
        <div class="stat-card">
            <span style="font-size:0.85rem; color:var(--clr-text-muted);">Roles Activos</span>
            <div class="stat-val" id="statActiveRoles" style="color:#34d399;">0</div>
        </div>
        <div class="stat-card">
            <span style="font-size:0.85rem; color:var(--clr-text-muted);">Empleados Asignados</span>
            <div class="stat-val" id="statTotalEmployees" style="color:#38bdf8;">0</div>
        </div>
    </div>

    <!-- Roles Grid Cards -->
    <div id="rolesGridContainer" class="roles-grid">
        <div style="grid-column:1/-1; text-align:center; padding:3rem; background:var(--clr-surface-1); border-radius:var(--radius-lg); color:var(--clr-text-muted);">
            Cargando perfiles y roles de empleados...
        </div>
    </div>
</div>

<!-- ═══ MODAL 1: ASIGNAR / AGREGAR PERSONAL (ModalAsignarPersonal) ═══ -->
<div id="assignEmployeeModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); backdrop-filter:blur(8px); z-index:9999; justify-content:center; align-items:center; padding:var(--sp-md); overflow-y:auto;">
    <div style="background:var(--clr-surface-1); border:1px solid var(--clr-primary); border-radius:var(--radius-lg); width:100%; max-width:540px; padding:var(--sp-lg); box-shadow:0 20px 40px rgba(249,115,22,0.3); max-height:90vh; overflow-y:auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--sp-md);">
            <div>
                <h3 id="assignModalRoleTitle" style="font-family:var(--font-display); font-size:1.3rem; color:var(--clr-primary); margin:0;">
                    👥 Asignar Personal a Rol
                </h3>
                <p id="assignModalRoleSubtitle" style="font-size:0.82rem; color:var(--clr-text-muted); margin-top:2px;">Rol destino: Recepcionista / Hostess</p>
            </div>
            <button onclick="closeAssignEmployeeModal()" style="background:none; border:none; color:var(--clr-text-muted); font-size:1.2rem; cursor:pointer;">✕</button>
        </div>

        <!-- Mode Toggle Tabs -->
        <div style="display:flex; gap:8px; margin-bottom:var(--sp-md);">
            <button type="button" id="tabBtnCreate" onclick="switchAssignTab('crear')" class="tab-btn active">
                ✨ Crear Nuevo Empleado
            </button>
            <button type="button" id="tabBtnExisting" onclick="switchAssignTab('existente')" class="tab-btn">
                🔗 Asignar Empleado Existente
            </button>
        </div>

        <form id="assignEmployeeForm" onsubmit="handleConfirmAssignEmployee(event)">
            <input type="hidden" id="assignRoleId" value="">
            <input type="hidden" id="assignMode" value="crear">

            <!-- TAB 1: CREAR NUEVO EMPLEADO -->
            <div id="tabContentCreate">
                <div style="margin-bottom:var(--sp-sm);">
                    <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Nombre Completo del Empleado *</label>
                    <input type="text" id="newEmpName" class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);" placeholder="Ej. Ana María Torres">
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--sp-sm); margin-bottom:var(--sp-sm);">
                    <div>
                        <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Correo Electrónico *</label>
                        <input type="email" id="newEmpEmail" class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);" placeholder="ana.torres@tixco.com">
                    </div>
                    <div>
                        <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Teléfono de Contacto</label>
                        <input type="text" id="newEmpPhone" class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);" placeholder="Ej. (555) 019-2831">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--sp-sm); margin-bottom:var(--sp-md);">
                    <div>
                        <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">PIN de Acceso Rápido (4 dígitos)</label>
                        <input type="text" id="newEmpPin" maxlength="6" value="1234" class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm); text-align:center; letter-spacing:2px; font-weight:700;">
                    </div>
                    <div>
                        <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Estado del Puesto</label>
                        <select id="newEmpActive" class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);">
                            <option value="1">🟢 Activo / Contratado</option>
                            <option value="0">🔴 Inactivo / Suspendido</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- TAB 2: ASIGNAR EMPLEADO EXISTENTE -->
            <div id="tabContentExisting" style="display:none; margin-bottom:var(--sp-lg);">
                <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:6px;">Seleccionar Empleado Registrado *</label>
                <select id="existingUserIdSelect" class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:10px 12px; border-radius:var(--radius-sm); font-size:0.95rem;"></select>
                <span style="font-size:0.75rem; color:var(--clr-text-muted); display:block; margin-top:4px;">
                    Muestra empleados registrados que no pertenecen actualmente a este rol.
                </span>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:var(--sp-sm);">
                <button type="button" onclick="closeAssignEmployeeModal()" class="btn btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-primary" style="background:var(--clr-primary); font-weight:700;">
                    ✅ Confirmar Asignación
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ═══ MODAL 2: VER LISTA DE PERSONAL ASIGNADO ═══ -->
<div id="viewEmployeesModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); backdrop-filter:blur(8px); z-index:9999; justify-content:center; align-items:center; padding:var(--sp-md); overflow-y:auto;">
    <div style="background:var(--clr-surface-1); border:1px solid var(--clr-border); border-radius:var(--radius-lg); width:100%; max-width:600px; padding:var(--sp-lg); box-shadow:0 20px 40px rgba(0,0,0,0.8); max-height:90vh; overflow-y:auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--sp-md);">
            <div>
                <h3 id="viewEmpRoleTitle" style="font-family:var(--font-display); font-size:1.3rem; color:var(--clr-text); margin:0;">
                    👥 Personal Asignado al Rol
                </h3>
                <p id="viewEmpRoleSubtitle" style="font-size:0.82rem; color:var(--clr-text-muted); margin-top:2px;">Lista de empleados pertenecientes a este perfil</p>
            </div>
            <button onclick="closeViewEmployeesModal()" style="background:none; border:none; color:var(--clr-text-muted); font-size:1.2rem; cursor:pointer;">✕</button>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--sp-md);">
            <strong id="viewEmpCountBadge" style="font-size:0.9rem; color:var(--clr-primary);">0 empleado(s) activos</strong>
            <button onclick="openAssignModalFromViewList()" class="btn btn-sm btn-primary" style="font-size:0.8rem;">
                ➕ Agregar Otro Empleado
            </button>
        </div>

        <div style="background:var(--clr-surface-2); border:1px solid var(--clr-border); border-radius:var(--radius-md); padding:var(--sp-md); max-height:280px; overflow-y:auto;">
            <div id="assignedEmployeesList" style="display:flex; flex-direction:column; gap:8px;">
                <!-- Employees injected dynamically -->
            </div>
        </div>

        <div style="display:flex; justify-content:flex-end; margin-top:var(--sp-lg);">
            <button onclick="closeViewEmployeesModal()" class="btn btn-secondary">Cerrar Ventana</button>
        </div>
    </div>
</div>

<!-- ═══ MODAL CREAR / EDITAR ROL (ModalRoleSave) ═══ -->
<div id="roleModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); backdrop-filter:blur(6px); z-index:9999; justify-content:center; align-items:center; padding:var(--sp-md); overflow-y:auto;">
    <div style="background:var(--clr-surface-1); border:1px solid var(--clr-border); border-radius:var(--radius-lg); width:100%; max-width:540px; padding:var(--sp-lg); box-shadow:0 20px 40px rgba(0,0,0,0.7); max-height:90vh; overflow-y:auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--sp-md);">
            <h3 id="roleModalTitle" style="font-family:var(--font-display); font-size:1.3rem; color:var(--clr-text); margin:0;">
                ➕ Crear Nuevo Rol de Empleado
            </h3>
            <button onclick="closeRoleModal()" style="background:none; border:none; color:var(--clr-text-muted); font-size:1.2rem; cursor:pointer;">✕</button>
        </div>

        <form id="roleForm" onsubmit="handleSaveRole(event)">
            <input type="hidden" id="roleId" value="">

            <div style="margin-bottom:var(--sp-sm);">
                <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Nombre del Rol *</label>
                <input type="text" id="roleDisplayName" required class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:10px 12px; border-radius:var(--radius-sm);" placeholder="Ej. Barman / Bartender, Capitán de Meseros">
            </div>

            <div style="margin-bottom:var(--sp-sm);">
                <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Identificador Slug (Opcional)</label>
                <input type="text" id="roleSlug" class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);" placeholder="Ej. barman, capitan">
            </div>

            <div style="margin-bottom:var(--sp-md);">
                <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Descripción del Puesto / Funciones</label>
                <textarea id="roleDescription" class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm); min-height:70px;" placeholder="Resumen de responsabilidades y operaciones a realizar..."></textarea>
            </div>

            <div style="margin-bottom:var(--sp-lg);">
                <label style="display:block; font-size:0.9rem; font-weight:600; color:var(--clr-text); margin-bottom:8px;">
                    🔒 Permisos y Módulos Autorizados
                </label>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; background:var(--clr-surface-2); padding:12px; border-radius:8px; border:1px solid var(--clr-border);">
                    <label style="display:flex; align-items:center; gap:8px; font-size:0.82rem; color:var(--clr-text); cursor:pointer;">
                        <input type="checkbox" name="permissions" value="salon" style="accent-color:var(--clr-primary); width:16px; height:16px;">
                        <span>🏛️ Plano 2D y Salón</span>
                    </label>
                    <label style="display:flex; align-items:center; gap:8px; font-size:0.82rem; color:var(--clr-text); cursor:pointer;">
                        <input type="checkbox" name="permissions" value="comandas" style="accent-color:var(--clr-primary); width:16px; height:16px;">
                        <span>🍽️ Comandas y Mesas</span>
                    </label>
                    <label style="display:flex; align-items:center; gap:8px; font-size:0.82rem; color:var(--clr-text); cursor:pointer;">
                        <input type="checkbox" name="permissions" value="cocina" style="accent-color:var(--clr-primary); width:16px; height:16px;">
                        <span>👨‍🍳 Cocina / KDS</span>
                    </label>
                    <label style="display:flex; align-items:center; gap:8px; font-size:0.82rem; color:var(--clr-text); cursor:pointer;">
                        <input type="checkbox" name="permissions" value="caja" style="accent-color:var(--clr-primary); width:16px; height:16px;">
                        <span>💵 Caja y Split Bill</span>
                    </label>
                    <label style="display:flex; align-items:center; gap:8px; font-size:0.82rem; color:var(--clr-text); cursor:pointer;">
                        <input type="checkbox" name="permissions" value="inventario" style="accent-color:var(--clr-primary); width:16px; height:16px;">
                        <span>📦 Stock e Inventario</span>
                    </label>
                    <label style="display:flex; align-items:center; gap:8px; font-size:0.82rem; color:var(--clr-text); cursor:pointer;">
                        <input type="checkbox" name="permissions" value="mermas" style="accent-color:var(--clr-primary); width:16px; height:16px;">
                        <span>🗑️ Registro de Mermas</span>
                    </label>
                    <label style="display:flex; align-items:center; gap:8px; font-size:0.82rem; color:var(--clr-text); cursor:pointer;">
                        <input type="checkbox" name="permissions" value="bitacora" style="accent-color:var(--clr-primary); width:16px; height:16px;">
                        <span>📋 Bitácora Auditoría</span>
                    </label>
                    <label style="display:flex; align-items:center; gap:8px; font-size:0.82rem; color:var(--clr-text); cursor:pointer;">
                        <input type="checkbox" name="permissions" value="roles" style="accent-color:var(--clr-primary); width:16px; height:16px;">
                        <span>🛡️ Roles y Permisos</span>
                    </label>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:var(--sp-sm);">
                <button type="button" onclick="closeRoleModal()" class="btn btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-primary" style="background:var(--clr-primary); font-weight:700;">Guardar Rol</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
let rolesData = [];
let activeTargetRole = null;
let currentRoleEmployees = [];

document.addEventListener('DOMContentLoaded', () => {
    loadRoles();
});

async function loadRoles() {
    try {
        const res = await fetch('/api/admin/roles');
        const data = await res.json();

        document.getElementById('statTotalRoles').innerText = data.stats.total_roles;
        document.getElementById('statActiveRoles').innerText = data.stats.active_roles;
        document.getElementById('statTotalEmployees').innerText = data.stats.total_employees;

        rolesData = data.roles || [];
        renderRolesGrid(rolesData);
    } catch (err) {
        console.error('Error cargando roles', err);
    }
}

function renderRolesGrid(roles) {
    const container = document.getElementById('rolesGridContainer');
    if (!roles || roles.length === 0) {
        container.innerHTML = `<div style="grid-column:1/-1; text-align:center; padding:3rem; background:var(--clr-surface-1); border-radius:var(--radius-lg); color:var(--clr-text-muted);">No hay roles registrados.</div>`;
        return;
    }

    const roleIcons = {
        'admin': '👑',
        'host': '👩‍💼',
        'waiter': '👔',
        'kitchen': '👨‍🍳',
        'cashier': '💵',
        'barman': '🍹'
    };

    const permLabels = {
        'salon': '🏛️ Salón',
        'comandas': '🍽️ Comandas',
        'cocina': '👨‍🍳 Cocina',
        'caja': '💵 Caja',
        'inventario': '📦 Stock',
        'mermas': '🗑️ Mermas',
        'bitacora': '📋 Bitácora',
        'roles': '🛡️ Roles'
    };

    container.innerHTML = roles.map(role => {
        const icon = roleIcons[role.name] || '👤';
        const perms = role.permissions_json || [];
        const permsBadgeHTML = perms.map(p => `<span class="perm-badge">${permLabels[p] || p}</span>`).join('');
        const userCount = role.users_count || 0;

        return `
        <div class="role-card" style="border-left: 4px solid ${role.is_active ? 'var(--clr-primary)' : '#64748b'};">
            <div>
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:var(--sp-sm);">
                    <div>
                        <h3 style="font-family:var(--font-display); font-size:1.25rem; color:var(--clr-text); margin:0; display:flex; align-items:center; gap:8px;">
                            <span>${icon}</span> ${escapeHtml(role.display_name || role.name)}
                        </h3>
                        <code style="font-size:0.75rem; color:var(--clr-primary); opacity:0.9;">slug: ${escapeHtml(role.slug || role.name)}</code>
                    </div>
                    <span class="badge ${role.is_active ? 'badge-success' : 'badge-danger'}" style="font-size:0.75rem;">
                        ${role.is_active ? '🟢 Activo' : '🔴 Inactivo'}
                    </span>
                </div>

                <p style="font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:var(--sp-md); line-height:1.4;">
                    ${escapeHtml(role.description || 'Sin descripción asignada.')}
                </p>

                <div style="margin-bottom:var(--sp-md);">
                    <span style="display:block; font-size:0.75rem; color:var(--clr-text-muted); margin-bottom:6px; font-weight:600;">Permisos de Módulo:</span>
                    <div style="display:flex; flex-wrap:wrap; gap:4px;">
                        ${permsBadgeHTML || '<span style="font-size:0.75rem; color:var(--clr-text-muted);">Sin módulos asignados</span>'}
                    </div>
                </div>
            </div>

            <div>
                <!-- Interactive Personal Count & View List Link -->
                <div style="background:var(--clr-surface-2); border:1px solid var(--clr-border); padding:8px 12px; border-radius:8px; font-size:0.82rem; margin-bottom:var(--sp-md); display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <span style="color:var(--clr-text-muted);">Personal Asignado:</span>
                        <strong style="color:var(--clr-primary); font-size:0.95rem; margin-left:4px;">${userCount} emp.</strong>
                    </div>
                    <button onclick="openViewEmployeesModal(${role.id})" class="btn btn-sm btn-secondary" style="font-size:0.75rem; padding:4px 8px;" title="Ver lista de empleados asignados">
                        👁️ Ver Lista
                    </button>
                </div>

                <!-- Action Buttons: + Asignar Empleado, Editar, Desactivar -->
                <div style="display:flex; flex-direction:column; gap:6px;">
                    <button onclick="openAssignEmployeeModal(${role.id})" class="btn btn-sm btn-primary" style="width:100%; display:flex; justify-content:center; align-items:center; gap:6px; background:var(--clr-primary); font-weight:600; font-size:0.82rem;">
                        👥 + Asignar / Agregar Empleado
                    </button>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px;">
                        <button onclick="openRoleModal(${role.id})" class="btn btn-sm btn-secondary" style="font-size:0.8rem;">
                            ✏️ Editar Rol
                        </button>
                        <button onclick="toggleRoleStatus(${role.id}, ${role.is_active})" class="btn btn-sm ${role.is_active ? 'btn-danger' : 'btn-success'}" style="font-size:0.8rem;">
                            ${role.is_active ? '🚫 Desactivar' : '✅ Activar'}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        `;
    }).join('');
}

/* ═════════════════════════════════════════════════════════════════════════════
   MODAL 1: ASIGNAR / CREAR PERSONAL EN ROL
   ═════════════════════════════════════════════════════════════════════════════ */
async function openAssignEmployeeModal(roleId) {
    activeTargetRole = rolesData.find(r => r.id === roleId);
    if (!activeTargetRole) return;

    document.getElementById('assignRoleId').value = roleId;
    document.getElementById('assignModalRoleTitle').innerText = `👥 Asignar Personal al Rol`;
    document.getElementById('assignModalRoleSubtitle').innerText = `Rol destino: ${activeTargetRole.display_name || activeTargetRole.name}`;

    switchAssignTab('crear');
    document.getElementById('assignEmployeeForm').reset();
    document.getElementById('newEmpPin').value = '1234';

    // Load unassigned employees for Tab 2
    try {
        const res = await fetch(`/api/admin/roles/${roleId}/empleados`);
        const data = await res.json();
        const unassigned = data.unassigned || [];
        const select = document.getElementById('existingUserIdSelect');

        if (unassigned.length === 0) {
            select.innerHTML = `<option value="">No hay empleados libres para asignar</option>`;
        } else {
            select.innerHTML = unassigned.map(u => `
                <option value="${u.id}">
                    👤 ${escapeHtml(u.name)} (${escapeHtml(u.email)}) ${u.role ? '— Actual: ' + escapeHtml(u.role.display_name) : '— Sin Rol'}
                </option>
            `).join('');
        }
    } catch (err) {
        console.error('Error cargando empleados unassigned', err);
    }

    document.getElementById('assignEmployeeModal').style.display = 'flex';
}

function closeAssignEmployeeModal() {
    document.getElementById('assignEmployeeModal').style.display = 'none';
}

function switchAssignTab(modo) {
    document.getElementById('assignMode').value = modo;
    const btnCreate = document.getElementById('tabBtnCreate');
    const btnExisting = document.getElementById('tabBtnExisting');
    const secCreate = document.getElementById('tabContentCreate');
    const secExisting = document.getElementById('tabContentExisting');

    if (modo === 'crear') {
        btnCreate.className = 'tab-btn active';
        btnExisting.className = 'tab-btn';
        secCreate.style.display = 'block';
        secExisting.style.display = 'none';
    } else {
        btnCreate.className = 'tab-btn';
        btnExisting.className = 'tab-btn active';
        secCreate.style.display = 'none';
        secExisting.style.display = 'block';
    }
}

async function handleConfirmAssignEmployee(e) {
    e.preventDefault();
    const roleId = document.getElementById('assignRoleId').value;
    const modo = document.getElementById('assignMode').value;

    let payload = { modo: modo };

    if (modo === 'existente') {
        const userId = document.getElementById('existingUserIdSelect').value;
        if (!userId) {
            showToast('Selecciona un empleado existente para vincular', 'error');
            return;
        }
        payload.user_id = parseInt(userId);
    } else {
        const name = document.getElementById('newEmpName').value.trim();
        const email = document.getElementById('newEmpEmail').value.trim();

        if (!name || !email) {
            showToast('Ingresa el nombre y correo del nuevo empleado', 'error');
            return;
        }

        payload.name = name;
        payload.email = email;
        payload.phone = document.getElementById('newEmpPhone').value.trim();
        payload.pin_code = document.getElementById('newEmpPin').value.trim() || '1234';
        payload.is_active = document.getElementById('newEmpActive').value === '1';
    }

    try {
        const res = await fetch(`/api/admin/roles/${roleId}/asignar-empleado`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        if (!res.ok) {
            showToast(data.message || 'Error al asignar empleado', 'error');
            return;
        }

        showToast(data.message);
        closeAssignEmployeeModal();
        loadRoles();
    } catch (err) {
        showToast('Error al conectar con el servidor', 'error');
    }
}

/* ═════════════════════════════════════════════════════════════════════════════
   MODAL 2: VER LISTA Y DESVINCULAR PERSONAL DEL ROL
   ═════════════════════════════════════════════════════════════════════════════ */
async function openViewEmployeesModal(roleId) {
    activeTargetRole = rolesData.find(r => r.id === roleId);
    if (!activeTargetRole) return;

    document.getElementById('viewEmpRoleTitle').innerText = `👥 Personal Asignado: ${activeTargetRole.display_name || activeTargetRole.name}`;
    document.getElementById('viewEmpRoleSubtitle').innerText = `Lista completa de empleados con permisos de ${activeTargetRole.display_name}`;

    await refreshAssignedEmployeesList(roleId);
    document.getElementById('viewEmployeesModal').style.display = 'flex';
}

function closeViewEmployeesModal() {
    document.getElementById('viewEmployeesModal').style.display = 'none';
}

function openAssignModalFromViewList() {
    if (!activeTargetRole) return;
    closeViewEmployeesModal();
    openAssignEmployeeModal(activeTargetRole.id);
}

async function refreshAssignedEmployeesList(roleId) {
    try {
        const res = await fetch(`/api/admin/roles/${roleId}/empleados`);
        const data = await res.json();
        currentRoleEmployees = data.assigned || [];

        document.getElementById('viewEmpCountBadge').innerText = `${currentRoleEmployees.length} empleado(s) asignados a este rol`;

        const container = document.getElementById('assignedEmployeesList');

        if (currentRoleEmployees.length === 0) {
            container.innerHTML = `
                <div style="text-align:center; padding:1.5rem; color:var(--clr-text-muted);">
                    No hay empleados asignados actualmente a este rol.
                </div>`;
            return;
        }

        container.innerHTML = currentRoleEmployees.map(emp => `
            <div style="background:var(--clr-surface-1); padding:10px 14px; border-radius:8px; border:1px solid var(--clr-border); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                <div>
                    <strong style="color:var(--clr-text); font-size:0.95rem;">👤 ${escapeHtml(emp.name)}</strong>
                    <div style="font-size:0.78rem; color:var(--clr-text-muted); display:flex; gap:12px; margin-top:2px;">
                        <span>✉️ ${escapeHtml(emp.email)}</span>
                        ${emp.phone ? '<span>📞 ' + escapeHtml(emp.phone) + '</span>' : ''}
                        <span>🔑 PIN: <code>${escapeHtml(emp.pin_code || '1234')}</code></span>
                    </div>
                </div>

                <div style="display:flex; align-items:center; gap:8px;">
                    <span class="badge ${emp.is_active ? 'badge-success' : 'badge-danger'}" style="font-size:0.7rem;">
                        ${emp.is_active ? '🟢 Activo' : '🔴 Inactivo'}
                    </span>
                    <button onclick="handleUnlinkEmployee(${roleId}, ${emp.id}, '${escapeHtml(emp.name)}')" class="btn btn-sm btn-danger" style="font-size:0.75rem; padding:4px 8px;" title="Quitar rol a este empleado">
                        ❌ Desvincular
                    </button>
                </div>
            </div>
        `).join('');
    } catch (err) {
        console.error('Error al actualizar lista de empleados', err);
    }
}

async function handleUnlinkEmployee(roleId, userId, userName) {
    if (!confirm(`¿Estás seguro de desvincular a '${userName}' de este rol?`)) return;

    try {
        const res = await fetch(`/api/admin/roles/${roleId}/desvincular-empleado/${userId}`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' }
        });

        const data = await res.json();
        if (!res.ok) {
            showToast(data.message || 'Error al desvincular empleado', 'error');
            return;
        }

        showToast(data.message);
        await refreshAssignedEmployeesList(roleId);
        loadRoles();
    } catch (err) {
        showToast('Error de conexión', 'error');
    }
}

/* ═════════════════════════════════════════════════════════════════════════════
   MODAL CONFIGURAR ROL
   ═════════════════════════════════════════════════════════════════════════════ */
function openRoleModal(id = null) {
    document.getElementById('roleForm').reset();
    document.getElementById('roleId').value = id || '';
    document.getElementById('roleModalTitle').innerText = id ? '✏️ Editar Rol de Empleado' : '➕ Crear Nuevo Rol de Empleado';

    if (id) {
        const role = rolesData.find(r => r.id === id);
        if (role) {
            document.getElementById('roleDisplayName').value = role.display_name || role.name;
            document.getElementById('roleSlug').value = role.slug || '';
            document.getElementById('roleDescription').value = role.description || '';

            const perms = role.permissions_json || [];
            document.querySelectorAll('input[name="permissions"]').forEach(cb => {
                cb.checked = perms.includes(cb.value);
            });
        }
    }

    document.getElementById('roleModal').style.display = 'flex';
}

function closeRoleModal() {
    document.getElementById('roleModal').style.display = 'none';
}

async function handleSaveRole(e) {
    e.preventDefault();
    const id = document.getElementById('roleId').value;
    const displayName = document.getElementById('roleDisplayName').value.trim();
    const slug = document.getElementById('roleSlug').value.trim();
    const description = document.getElementById('roleDescription').value.trim();

    const selectedPerms = Array.from(document.querySelectorAll('input[name="permissions"]:checked')).map(cb => cb.value);

    const payload = {
        display_name: displayName,
        slug: slug,
        description: description,
        permissions_json: selectedPerms
    };

    try {
        const url = id ? `/api/admin/roles/${id}` : '/api/admin/roles';
        const method = id ? 'PUT' : 'POST';

        const res = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        if (!res.ok) {
            showToast(data.message || 'Error al guardar rol', 'error');
            return;
        }

        showToast(data.message);
        closeRoleModal();
        loadRoles();
    } catch (err) {
        showToast('Error de conexión', 'error');
    }
}

async function toggleRoleStatus(id, currentStatus) {
    try {
        const res = await fetch(`/api/admin/roles/${id}/status`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ is_active: !currentStatus })
        });
        const data = await res.json();
        if (!res.ok) {
            showToast(data.message || 'No se puede modificar el estado del rol', 'error');
            return;
        }
        showToast(data.message);
        loadRoles();
    } catch (err) {
        showToast('Error de servidor', 'error');
    }
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>
@endsection
