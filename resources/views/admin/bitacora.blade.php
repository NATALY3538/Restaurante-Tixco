@extends('layouts.app')

@section('title', 'Bitácora de Auditoría y Seguridad')

@section('styles')
<style>
.audit-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: var(--sp-md);
    margin-bottom: var(--sp-xl);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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

.filter-bar {
    background: var(--clr-surface-1);
    border: 1px solid var(--clr-border);
    border-radius: var(--radius-md);
    padding: var(--sp-md);
    margin-bottom: var(--sp-lg);
    display: flex;
    gap: var(--sp-md);
    align-items: center;
    flex-wrap: wrap;
}

.audit-table-box {
    background: var(--clr-surface-1);
    border: 1px solid var(--clr-border);
    border-radius: var(--radius-lg);
    padding: var(--sp-lg);
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    overflow-x: auto;
}

table.custom-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
}

table.custom-table th {
    background: var(--clr-surface-2);
    color: var(--clr-text-muted);
    text-align: left;
    padding: 12px;
    border-bottom: 1px solid var(--clr-border);
    font-weight: 600;
}

table.custom-table td {
    padding: 12px;
    border-bottom: 1px solid var(--clr-border);
    color: var(--clr-text);
}

.badge-module {
    padding: 4px 8px;
    border-radius: var(--radius-sm);
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

.mod-salon { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid #10b981; }
.mod-comandas { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid #ef4444; }
.mod-caja { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid #f59e0b; }
.mod-inventario { background: rgba(168, 85, 247, 0.2); color: #c084fc; border: 1px solid #a855f7; }
.mod-menu { background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid #3b82f6; }
</style>
@endsection

@section('content')
<div class="container" style="padding-top: var(--sp-xl); padding-bottom: var(--sp-xxl);">
    <!-- Header -->
    <div class="audit-header">
        <div>
            <h1 style="font-family:var(--font-display); font-size:2rem; margin-bottom:0.25rem; color:var(--clr-primary);">
                📋 Bitácora de Auditoría y Eventos de Seguridad
            </h1>
            <p style="color:var(--clr-text-muted); font-size:0.95rem;">
                Registro de acciones delicadas en salón, anulaciones con PIN, aperturas de caja y mermas.
            </p>
        </div>
        <div style="display:flex; gap:var(--sp-sm);">
            <button onclick="triggerCashDrawerLog()" class="btn btn-secondary" style="display:flex; align-items:center; gap:6px;">
                💵 Simular Apertura Cajón
            </button>
            <a href="/admin" class="btn btn-secondary">
                ⬅️ Panel Admin
            </a>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <span style="font-size:0.85rem; color:var(--clr-text-muted);">Eventos Registrados</span>
            <div class="stat-val" id="statTotal">0</div>
        </div>
        <div class="stat-card">
            <span style="font-size:0.85rem; color:var(--clr-text-muted);">Anulaciones Comandas</span>
            <div class="stat-val" id="statComandas" style="color:#ef4444;">0</div>
        </div>
        <div class="stat-card">
            <span style="font-size:0.85rem; color:var(--clr-text-muted);">Movimientos Salón</span>
            <div class="stat-val" id="statSalon" style="color:#34d399;">0</div>
        </div>
        <div class="stat-card">
            <span style="font-size:0.85rem; color:var(--clr-text-muted);">Mermas Registradas</span>
            <div class="stat-val" id="statInventario" style="color:#c084fc;">0</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <div style="flex:1; min-width:200px;">
            <input type="text" id="auditSearchInput" oninput="loadAuditLogs()" class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);" placeholder="🔍 Buscar por acción, usuario o detalles...">
        </div>
        <div style="min-width:180px;">
            <select id="auditModuleSelect" onchange="loadAuditLogs()" class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);">
                <option value="">📂 Todos los Módulos</option>
                <option value="Salón">🏛️ Salón</option>
                <option value="Comandas">🍽️ Comandas</option>
                <option value="Caja">💵 Caja</option>
                <option value="Inventario">📦 Inventario</option>
                <option value="Menú">📜 Menú</option>
            </select>
        </div>
    </div>

    <!-- Audit Table -->
    <div class="audit-table-box">
        <h3 style="font-family:var(--font-display); font-size:1.2rem; margin-bottom:var(--sp-md); color:var(--clr-text);">
            🔒 Histórico de Eventos de Auditoría
        </h3>
        <table class="custom-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha y Hora</th>
                    <th>Módulo</th>
                    <th>Acción Realizada</th>
                    <th>Usuario Autorizó</th>
                    <th>Dirección IP</th>
                    <th>Detalles JSON</th>
                </tr>
            </thead>
            <tbody id="auditTableBody">
                <tr>
                    <td colspan="7" style="text-align:center; padding:2rem; color:var(--clr-text-muted);">
                        Cargando bitácora de eventos...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ═══ MODAL INSPECTOR DE DETALLES JSON ═══ -->
<div id="detailsModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); backdrop-filter:blur(6px); z-index:9999; justify-content:center; align-items:center; padding:var(--sp-md);">
    <div style="background:var(--clr-surface-1); border:1px solid var(--clr-border); border-radius:var(--radius-lg); width:100%; max-width:500px; padding:var(--sp-lg); box-shadow:0 20px 40px rgba(0,0,0,0.8);">
        <h3 style="font-family:var(--font-display); font-size:1.2rem; color:var(--clr-text); margin-bottom:var(--sp-md);">
            🔍 Inspector de Detalles del Evento
        </h3>
        <pre id="jsonDetailsContent" style="background:#0f172a; color:#38bdf8; padding:16px; border-radius:8px; font-size:0.85rem; overflow-x:auto; border:1px solid var(--clr-border); max-height:300px;"></pre>
        <div style="display:flex; justify-content:flex-end; margin-top:var(--sp-md);">
            <button onclick="closeDetailsModal()" class="btn btn-secondary">Cerrar</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    loadAuditLogs();
});

async function loadAuditLogs() {
    const search = document.getElementById('auditSearchInput').value.trim();
    const module = document.getElementById('auditModuleSelect').value;

    const queryParams = new URLSearchParams();
    if (search) queryParams.append('search', search);
    if (module) queryParams.append('module', module);

    try {
        const res = await fetch(`/api/admin/audit-logs?${queryParams.toString()}`);
        const data = await res.json();

        document.getElementById('statTotal').innerText = data.stats.total;
        document.getElementById('statComandas').innerText = data.stats.comandas;
        document.getElementById('statSalon').innerText = data.stats.salon;
        document.getElementById('statInventario').innerText = data.stats.inventario;

        const tbody = document.getElementById('auditTableBody');
        const logs = data.logs.data || data.logs;

        if (!logs || logs.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:2rem; color:var(--clr-text-muted);">No se encontraron eventos en la bitácora.</td></tr>`;
            return;
        }

        const modClassMap = {
            'Salón': 'mod-salon',
            'Comandas': 'mod-comandas',
            'Caja': 'mod-caja',
            'Inventario': 'mod-inventario',
            'Menú': 'mod-menu'
        };

        tbody.innerHTML = logs.map(log => {
            const dateStr = new Date(log.created_at).toLocaleString('es-MX', { dateStyle: 'short', timeStyle: 'medium' });
            const detailsStr = escapeHtml(JSON.stringify(log.details_json || {}));
            const modClass = modClassMap[log.module] || 'mod-salon';

            return `
            <tr>
                <td>#${log.id}</td>
                <td>${dateStr}</td>
                <td><span class="badge-module ${modClass}">${escapeHtml(log.module)}</span></td>
                <td><strong>${escapeHtml(log.action)}</strong></td>
                <td>${escapeHtml(log.user_name || 'Admin / Gerente')}</td>
                <td><code style="font-size:0.75rem; color:var(--clr-primary);">${log.ip_address || '127.0.0.1'}</code></td>
                <td>
                    <button onclick="viewDetails('${detailsStr}')" class="btn btn-sm btn-secondary" style="font-size:0.75rem;">
                        👁️ Ver Detalles
                    </button>
                </td>
            </tr>
            `;
        }).join('');
    } catch (err) {
        console.error('Error cargando auditoría', err);
    }
}

function viewDetails(jsonStr) {
    try {
        const obj = JSON.parse(jsonStr);
        document.getElementById('jsonDetailsContent').innerText = JSON.stringify(obj, null, 2);
    } catch (e) {
        document.getElementById('jsonDetailsContent').innerText = jsonStr;
    }
    document.getElementById('detailsModal').style.display = 'flex';
}

function closeDetailsModal() {
    document.getElementById('detailsModal').style.display = 'none';
}

async function triggerCashDrawerLog() {
    try {
        const res = await fetch('/api/admin/audit-logs', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'Apertura Manual de Cajón de Dinero',
                module: 'Caja',
                details: { motivo: 'Apertura manual por gerencia para cambio', saldo_inicial: 2500 }
            })
        });
        const data = await res.json();
        showToast(data.message);
        loadAuditLogs();
    } catch (err) {
        showToast('Error al registrar evento', 'error');
    }
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>
@endsection
