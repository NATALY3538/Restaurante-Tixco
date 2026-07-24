@extends('layouts.app')

@section('title', 'Inventario de Insumos - Tixco Admin')

@section('styles')
<style>
.insumos-header {
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

.insumos-box {
    background: var(--clr-surface-1);
    border: 1px solid var(--clr-border);
    border-radius: var(--radius-lg);
    padding: var(--sp-lg);
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    overflow-x: auto;
}

.toolbar-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: var(--sp-md);
    margin-bottom: var(--sp-md);
    flex-wrap: wrap;
}

table.custom-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
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
    vertical-align: middle;
}

.badge-status {
    padding: 4px 10px;
    border-radius: var(--radius-full);
    font-size: 0.78rem;
    font-weight: 600;
    display: inline-block;
}

.status-ok { background: rgba(34, 197, 94, 0.2); color: #4ade80; border: 1px solid #22c55e; }
.status-low { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid #f59e0b; }
.status-out { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid #ef4444; }

.btn-action-edit {
    background: var(--clr-surface-2);
    color: var(--clr-text);
    border: 1px solid var(--clr-border);
    padding: 6px 12px;
    border-radius: var(--radius-sm);
    font-weight: 600;
    cursor: pointer;
    font-size: 0.82rem;
    transition: all 0.15s;
}

.btn-action-edit:hover {
    border-color: var(--clr-primary);
    color: var(--clr-primary);
}

.btn-action-delete {
    background: rgba(239, 68, 68, 0.15);
    color: #f87171;
    border: 1px solid #ef4444;
    padding: 6px 12px;
    border-radius: var(--radius-sm);
    font-weight: 600;
    cursor: pointer;
    font-size: 0.82rem;
    transition: all 0.15s;
}

.btn-action-delete:hover {
    background: #dc2626;
    color: #fff;
}
</style>
@endsection

@section('content')
<div class="container" style="padding-top: var(--sp-xl); padding-bottom: var(--sp-xxl);">
    <!-- Header -->
    <div class="insumos-header">
        <div>
            <h1 style="font-family:var(--font-display); font-size:2rem; margin-bottom:0.25rem; color:var(--clr-primary);">
                📦 Insumos y Productos Base
            </h1>
            <p style="color:var(--clr-text-muted); font-size:0.95rem;">
                Gestión de componentes, ingredientes, costos unitarios y control de stock mínimo.
            </p>
        </div>
        <div style="display:flex; gap:10px;">
            <button onclick="openInsumoModal()" class="btn btn-primary" style="font-weight:600; display:flex; align-items:center; gap:6px;">
                ➕ Registrar Insumo
            </button>
            <a href="/admin/inventario/mermas" class="btn btn-secondary" style="font-weight:600;">
                🗑️ Ver Mermas
            </a>
        </div>
    </div>

    <!-- Stats Summary -->
    <div class="stats-grid">
        <div class="stat-card">
            <span style="font-size:0.85rem; color:var(--clr-text-muted);">Total de Insumos</span>
            <div class="stat-val" id="statTotalItems">0</div>
        </div>
        <div class="stat-card">
            <span style="font-size:0.85rem; color:var(--clr-text-muted);">⚠️ Alerta de Stock Bajo</span>
            <div class="stat-val" id="statLowStock" style="color:#fbbf24;">0</div>
        </div>
        <div class="stat-card">
            <span style="font-size:0.85rem; color:var(--clr-text-muted);">💰 Valor Total Inventario</span>
            <div class="stat-val" id="statTotalValue" style="color:#34d399;">$0.00</div>
        </div>
    </div>

    <!-- Main Insumos Table Box -->
    <div class="insumos-box">
        <div class="toolbar-flex">
            <h3 style="font-family:var(--font-display); font-size:1.2rem; color:var(--clr-text);">
                📋 Catálogo de Insumos Registrados
            </h3>
            
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <input type="text" id="searchInput" oninput="filterInsumos()" placeholder="🔍 Buscar insumo o código..." class="form-control" style="width:240px; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:6px 12px; border-radius:var(--radius-sm); font-size:0.85rem;">
                
                <select id="categoryFilter" onchange="filterInsumos()" class="form-control" style="background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:6px 12px; border-radius:var(--radius-sm); font-size:0.85rem;">
                    <option value="all">Todas las Categorías</option>
                </select>
            </div>
        </div>

        <table class="custom-table">
            <thead>
                <tr>
                    <th>Código / Insumo</th>
                    <th>Categoría</th>
                    <th>Stock Actual</th>
                    <th>Costo Unitario</th>
                    <th>Valor Total</th>
                    <th>Estado Stock</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="insumosTableBody">
                <tr>
                    <td colspan="7" style="text-align:center; padding:2rem; color:var(--clr-text-muted);">
                        Cargando inventario de insumos...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ═══ MODAL CREAR / EDITAR INSUMO ═══ -->
<div id="insumoModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); backdrop-filter:blur(6px); z-index:9999; justify-content:center; align-items:center; padding:var(--sp-md);">
    <div style="background:var(--clr-surface-1); border:1px solid var(--clr-border); border-radius:var(--radius-lg); width:100%; max-width:540px; padding:var(--sp-lg); box-shadow:0 20px 40px rgba(0,0,0,0.7);">
        <h3 id="modalInsumoTitle" style="font-family:var(--font-display); font-size:1.3rem; color:var(--clr-primary); margin-bottom:var(--sp-md);">
            📦 Registrar Nuevo Insumo
        </h3>

        <form id="formInsumo" onsubmit="handleSaveInsumo(event)">
            <input type="hidden" id="insumoId">

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--sp-sm); margin-bottom:var(--sp-sm);">
                <div>
                    <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Código (Opcional)</label>
                    <input type="text" id="insumoCode" class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);" placeholder="Ej. INS-CAR-001">
                </div>
                <div>
                    <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Nombre del Insumo *</label>
                    <input type="text" id="insumoName" required class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);" placeholder="Ej. Carne de Res Arrachera">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--sp-sm); margin-bottom:var(--sp-sm);">
                <div>
                    <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Categoría *</label>
                    <select id="insumoCategory" required class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);">
                        <option value="Carnes">Carnes</option>
                        <option value="Lácteos">Lácteos</option>
                        <option value="Verduras">Verduras</option>
                        <option value="Bebidas Base">Bebidas Base</option>
                        <option value="Panadería">Panadería</option>
                        <option value="Especias y Salsas">Especias y Salsas</option>
                        <option value="Empaques">Empaques</option>
                        <option value="General">General</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Unidad de Medida *</label>
                    <select id="insumoUnit" required class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);">
                        <option value="kg">Kilogramos (kg)</option>
                        <option value="gr">Gramos (gr)</option>
                        <option value="lt">Litros (lt)</option>
                        <option value="ml">Mililitros (ml)</option>
                        <option value="pza">Piezas (pza)</option>
                        <option value="cja">Cajas (cja)</option>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:var(--sp-sm); margin-bottom:var(--sp-sm);">
                <div>
                    <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Stock Actual *</label>
                    <input type="number" id="insumoStock" step="0.001" min="0" required class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);" placeholder="10.5">
                </div>
                <div>
                    <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Alerta Mínima *</label>
                    <input type="number" id="insumoMinAlert" step="0.001" min="0" required class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);" placeholder="3.0">
                </div>
                <div>
                    <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Costo Unit. ($) *</label>
                    <input type="number" id="insumoUnitCost" step="0.01" min="0" required class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);" placeholder="150.00">
                </div>
            </div>

            <div style="margin-bottom:var(--sp-lg);">
                <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Descripción / Notas (Opcional)</label>
                <textarea id="insumoDescription" class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm); min-height:60px;" placeholder="Detalles de proveedor, marca o almacenamiento..."></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:var(--sp-sm);">
                <button type="button" onclick="closeInsumoModal()" class="btn btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-primary" style="font-weight:600;">
                    💾 Guardar Insumo
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
let allInsumos = [];
let allCategories = [];

document.addEventListener('DOMContentLoaded', () => {
    loadInsumos();
});

async function loadInsumos() {
    try {
        const res = await fetch('/api/admin/insumos');
        const data = await res.json();

        allInsumos = data.insumos || [];
        allCategories = data.categories || [];

        // Stats
        document.getElementById('statTotalItems').innerText = data.stats.total_items || 0;
        document.getElementById('statLowStock').innerText = data.stats.low_stock_count || 0;
        document.getElementById('statTotalValue').innerText = `$${parseFloat(data.stats.total_inventory_value || 0).toLocaleString('es-MX', {minimumFractionDigits:2, maximumFractionDigits:2})}`;

        populateCategoryFilter();
        renderInsumosTable(allInsumos);
    } catch (err) {
        console.error('Error cargando insumos', err);
    }
}

function populateCategoryFilter() {
    const select = document.getElementById('categoryFilter');
    const currVal = select.value;
    let html = `<option value="all">Todas las Categorías</option>`;
    allCategories.forEach(cat => {
        html += `<option value="${escapeHtml(cat)}">${escapeHtml(cat)}</option>`;
    });
    select.innerHTML = html;
    select.value = currVal;
}

function filterInsumos() {
    const q = document.getElementById('searchInput').value.toLowerCase().trim();
    const cat = document.getElementById('categoryFilter').value;

    let filtered = allInsumos.filter(i => {
        const matchQ = (i.name || '').toLowerCase().includes(q) || (i.code || '').toLowerCase().includes(q) || (i.category || '').toLowerCase().includes(q);
        const matchCat = cat === 'all' || i.category === cat;
        return matchQ && matchCat;
    });

    renderInsumosTable(filtered);
}

function renderInsumosTable(list) {
    const tbody = document.getElementById('insumosTableBody');
    if (list.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:2rem; color:var(--clr-text-muted);">No se encontraron insumos registrados.</td></tr>`;
        return;
    }

    tbody.innerHTML = list.map(i => {
        let badge = `<span class="badge-status status-ok">🟢 Suficiente</span>`;
        if (i.stock_status === 'low') badge = `<span class="badge-status status-low">⚠️ Stock Bajo</span>`;
        if (i.stock_status === 'out') badge = `<span class="badge-status status-out">🔴 Agotado</span>`;

        const valTotalFormatted = `$${parseFloat(i.total_value).toLocaleString('es-MX', {minimumFractionDigits:2, maximumFractionDigits:2})}`;
        const costFormatted = `$${parseFloat(i.unit_cost).toFixed(2)}`;

        return `
        <tr>
            <td>
                <span style="font-size:0.75rem; color:var(--clr-primary); font-family:monospace; display:block;">#${escapeHtml(i.code)}</span>
                <strong style="font-size:0.95rem; color:var(--clr-text);">${escapeHtml(i.name)}</strong>
                ${i.description ? `<br><span style="font-size:0.78rem; color:var(--clr-text-muted);">${escapeHtml(i.description)}</span>` : ''}
            </td>
            <td>
                <span style="background:var(--clr-surface-2); padding:3px 8px; border-radius:4px; font-size:0.8rem; border:1px solid var(--clr-border);">
                    ${escapeHtml(i.category)}
                </span>
            </td>
            <td>
                <strong style="font-size:1rem;">${i.stock_quantity}</strong>
                <span style="font-size:0.8rem; color:var(--clr-text-muted);">${escapeHtml(i.unit_of_measure)}</span>
                <div style="font-size:0.72rem; color:var(--clr-text-muted);">Mín: ${i.min_stock_alert} ${escapeHtml(i.unit_of_measure)}</div>
            </td>
            <td>
                <strong>${costFormatted}</strong> / ${escapeHtml(i.unit_of_measure)}
            </td>
            <td>
                <strong style="color:#34d399;">${valTotalFormatted}</strong>
            </td>
            <td>${badge}</td>
            <td>
                <div style="display:flex; gap:6px;">
                    <button onclick="openInsumoModal(${i.id})" class="btn-action-edit" title="Editar Insumo">
                        ✏️ Editar
                    </button>
                    <button onclick="deleteInsumo(${i.id}, '${escapeHtml(i.name)}')" class="btn-action-delete" title="Eliminar Insumo">
                        🗑️ Eliminar
                    </button>
                </div>
            </td>
        </tr>
        `;
    }).join('');
}

function openInsumoModal(id = null) {
    document.getElementById('formInsumo').reset();
    document.getElementById('insumoId').value = id || '';

    if (id) {
        const item = allInsumos.find(i => i.id === id);
        if (item) {
            document.getElementById('modalInsumoTitle').innerText = '✏️ Editar Insumo';
            document.getElementById('insumoCode').value = item.code || '';
            document.getElementById('insumoName').value = item.name || '';
            document.getElementById('insumoCategory').value = item.category || 'General';
            document.getElementById('insumoUnit').value = item.unit_of_measure || 'kg';
            document.getElementById('insumoStock').value = item.stock_quantity;
            document.getElementById('insumoMinAlert').value = item.min_stock_alert;
            document.getElementById('insumoUnitCost').value = item.unit_cost;
            document.getElementById('insumoDescription').value = item.description || '';
        }
    } else {
        document.getElementById('modalInsumoTitle').innerText = '📦 Registrar Nuevo Insumo';
    }

    document.getElementById('insumoModal').style.display = 'flex';
}

function closeInsumoModal() {
    document.getElementById('insumoModal').style.display = 'none';
}

async function handleSaveInsumo(e) {
    e.preventDefault();
    const id = document.getElementById('insumoId').value;
    const payload = {
        code: document.getElementById('insumoCode').value.trim(),
        name: document.getElementById('insumoName').value.trim(),
        category: document.getElementById('insumoCategory').value,
        unit_of_measure: document.getElementById('insumoUnit').value,
        stock_quantity: parseFloat(document.getElementById('insumoStock').value) || 0,
        min_stock_alert: parseFloat(document.getElementById('insumoMinAlert').value) || 0,
        unit_cost: parseFloat(document.getElementById('insumoUnitCost').value) || 0,
        description: document.getElementById('insumoDescription').value.trim()
    };

    try {
        const url = id ? `/api/admin/insumos/${id}` : '/api/admin/insumos';
        const method = id ? 'PUT' : 'POST';

        const res = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await res.json();

        if (!res.ok) {
            showToast(data.message || 'Error al guardar insumo', 'error');
            return;
        }

        showToast(data.message);
        closeInsumoModal();
        loadInsumos();
    } catch (err) {
        showToast('Error al conectar con el servidor', 'error');
    }
}

async function deleteInsumo(id, name) {
    if (!confirm(`¿Estás seguro de eliminar el insumo "${name}" del inventario?`)) {
        return;
    }

    try {
        const res = await fetch(`/api/admin/insumos/${id}`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' }
        });

        const data = await res.json();
        if (!res.ok) {
            showToast(data.message || 'Error al eliminar insumo', 'error');
            return;
        }

        showToast(data.message);
        loadInsumos();
    } catch (err) {
        showToast('Error al conectar con el servidor', 'error');
    }
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>
@endsection
