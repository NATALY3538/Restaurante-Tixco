@extends('layouts.app')

@section('title', 'Registro de Mermas y Pérdidas de Alimentos')

@section('styles')
<style>
.waste-header {
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

.waste-table-box {
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
}

.badge-reason {
    padding: 4px 8px;
    border-radius: var(--radius-sm);
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

.reason-caducidad { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid #ef4444; }
.reason-accidente { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid #f59e0b; }
.reason-error_preparacion { background: rgba(168, 85, 247, 0.2); color: #c084fc; border: 1px solid #a855f7; }
.reason-muestra { background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid #3b82f6; }
.reason-otro { background: rgba(100, 116, 139, 0.2); color: #94a3b8; border: 1px solid #64748b; }
</style>
@endsection

@section('content')
<div class="container" style="padding-top: var(--sp-xl); padding-bottom: var(--sp-xxl);">
    <!-- Header -->
    <div class="waste-header">
        <div>
            <h1 style="font-family:var(--font-display); font-size:2rem; margin-bottom:0.25rem; color:var(--clr-primary);">
                🗑️ Registro de Mermas y Pérdidas de Alimentos
            </h1>
            <p style="color:var(--clr-text-muted); font-size:0.95rem;">
                Control de insumos dañados, caducados o muestras. Pérdidas calculadas a valor PVP (Precio de Venta al Público).
            </p>
        </div>
        <div style="display:flex; gap:var(--sp-sm);">
            <button onclick="openWasteModal()" class="btn btn-primary" style="display:flex; align-items:center; gap:6px; background:var(--clr-primary); font-weight:600;">
                ➕ Registrar Merma / Pérdida
            </button>
            <a href="/admin/inventario" class="btn btn-secondary">
                ⬅️ Inventario General
            </a>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <span style="font-size:0.85rem; color:var(--clr-text-muted);">Pérdida Económica Total (PVP)</span>
            <div class="stat-val" id="statTotalLoss" style="color:#ef4444;">$0.00</div>
        </div>
        <div class="stat-card">
            <span style="font-size:0.85rem; color:var(--clr-text-muted);">Insumos Mermados</span>
            <div class="stat-val" id="statTotalItems">0 unid.</div>
        </div>
        <div class="stat-card">
            <span style="font-size:0.85rem; color:var(--clr-text-muted);">Registros Totales</span>
            <div class="stat-val" id="statRecordsCount">0</div>
        </div>
    </div>

    <!-- Waste Table -->
    <div class="waste-table-box">
        <h3 style="font-family:var(--font-display); font-size:1.2rem; margin-bottom:var(--sp-md); color:var(--clr-text);">
            📜 Histórico de Registros de Mermas
        </h3>
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Fecha y Hora</th>
                    <th>Producto / Insumo</th>
                    <th>Cantidad Descontada</th>
                    <th>Costo Unit. (PVP)</th>
                    <th>Pérdida Total</th>
                    <th>Motivo de Merma</th>
                    <th>Notas</th>
                    <th>Registrado Por</th>
                </tr>
            </thead>
            <tbody id="wasteTableBody">
                <tr>
                    <td colspan="8" style="text-align:center; padding:2rem; color:var(--clr-text-muted);">
                        Cargando registro de mermas...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ═══ MODAL REGISTRAR MERMA ═══ -->
<div id="wasteModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); backdrop-filter:blur(6px); z-index:9999; justify-content:center; align-items:center; padding:var(--sp-md);">
    <div style="background:var(--clr-surface-1); border:1px solid var(--clr-border); border-radius:var(--radius-lg); width:100%; max-width:520px; padding:var(--sp-lg); box-shadow:0 20px 40px rgba(0,0,0,0.7);">
        <h3 style="font-family:var(--font-display); font-size:1.3rem; color:var(--clr-text); margin-bottom:var(--sp-md);">
            🗑️ Registrar Merma o Pérdida
        </h3>
        <form id="wasteForm" onsubmit="handleSaveWaste(event)">
            <div style="margin-bottom:var(--sp-sm);">
                <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Seleccionar Producto / Insumo *</label>
                <select id="wasteProductId" onchange="updateWasteCostPreview()" required class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:10px 12px; border-radius:var(--radius-sm);"></select>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--sp-sm); margin-bottom:var(--sp-sm);">
                <div>
                    <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Cantidad a Descontar *</label>
                    <input type="number" id="wasteQuantity" oninput="updateWasteCostPreview()" step="0.01" min="0.01" required value="1" class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);">
                </div>
                <div>
                    <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Motivo de Merma *</label>
                    <select id="wasteReason" required class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);">
                        <option value="caducidad">⏳ Caducidad / Vencimiento</option>
                        <option value="accidente">💥 Accidente / Caída</option>
                        <option value="error_preparacion">👨‍🍳 Error de Preparación</option>
                        <option value="muestra">🍷 Muestra / Degustación</option>
                        <option value="otro">📌 Otro</option>
                    </select>
                </div>
            </div>

            <!-- Preview Card for PVP Unitario & Pérdida Total -->
            <div style="background:var(--clr-surface-2); border:1px solid var(--clr-border); border-radius:8px; padding:12px; margin-bottom:var(--sp-md); display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div>
                    <span style="font-size:0.75rem; color:var(--clr-text-muted); display:block;">Costo Unit. (PVP):</span>
                    <strong id="previewCostUnit" style="font-size:1.1rem; color:var(--clr-primary);">$0.00</strong>
                </div>
                <div>
                    <span style="font-size:0.75rem; color:var(--clr-text-muted); display:block;">Pérdida Total (PVP x Cantidad):</span>
                    <strong id="previewCostTotal" style="font-size:1.1rem; color:#ef4444;">$0.00</strong>
                </div>
            </div>

            <div style="margin-bottom:var(--sp-lg);">
                <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Notas o Explicación</label>
                <textarea id="wasteNotes" class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm); min-height:60px;" placeholder="Detalles de cómo ocurrió o lote del producto..."></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:var(--sp-sm);">
                <button type="button" onclick="closeWasteModal()" class="btn btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-primary" style="background:var(--clr-primary); font-weight:600;">Confirmar Merma</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
let productsList = [];

document.addEventListener('DOMContentLoaded', () => {
    loadProducts();
    loadMermas();
});

async function loadProducts() {
    try {
        const res = await fetch('/api/products');
        productsList = await res.json();
        const select = document.getElementById('wasteProductId');

        select.innerHTML = productsList.map(p => {
            const pvpPrice = parseFloat(p.price || 0);
            const prodCost = parseFloat(p.real_cost || p.costo_produccion || p.cost || (pvpPrice * 0.35));
            return `<option value="${p.id}" data-pvp="${pvpPrice}">
                ${escapeHtml(p.name)} — PVP: $${pvpPrice.toFixed(2)} (Costo Producción: $${prodCost.toFixed(2)})
            </option>`;
        }).join('');

        updateWasteCostPreview();
    } catch (err) {
        console.error('Error cargando productos', err);
    }
}

function updateWasteCostPreview() {
    const select = document.getElementById('wasteProductId');
    const selectedOption = select.options[select.selectedIndex];
    const qty = parseFloat(document.getElementById('wasteQuantity').value) || 0;

    let pvpUnit = 0;
    if (selectedOption) {
        pvpUnit = parseFloat(selectedOption.getAttribute('data-pvp')) || 0;
    }

    const totalLoss = qty * pvpUnit;

    document.getElementById('previewCostUnit').innerText = `$${pvpUnit.toFixed(2)}`;
    document.getElementById('previewCostTotal').innerText = `$${totalLoss.toFixed(2)}`;
}

async function loadMermas() {
    try {
        const res = await fetch('/api/admin/mermas');
        const data = await res.json();
        
        document.getElementById('statTotalLoss').innerText = `$${parseFloat(data.summary.total_loss).toFixed(2)}`;
        document.getElementById('statTotalItems').innerText = `${parseFloat(data.summary.total_items).toFixed(1)} unid.`;
        document.getElementById('statRecordsCount').innerText = data.summary.count;

        const tbody = document.getElementById('wasteTableBody');
        if (!data.mermas || data.mermas.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding:2rem; color:var(--clr-text-muted);">No se han registrado mermas de inventario.</td></tr>`;
            return;
        }

        const reasonLabels = {
            caducidad: '⏳ Caducidad',
            accidente: '💥 Accidente',
            error_preparacion: '👨‍🍳 Error Prep.',
            muestra: '🍷 Muestra',
            otro: '📌 Otro'
        };

        tbody.innerHTML = data.mermas.map(m => {
            const dateStr = new Date(m.created_at).toLocaleString('es-MX', { dateStyle: 'short', timeStyle: 'short' });
            return `
            <tr>
                <td>${dateStr}</td>
                <td><strong>${escapeHtml(m.product ? m.product.name : 'Insumo')}</strong></td>
                <td><span style="color:#ef4444; font-weight:700;">-${parseFloat(m.quantity).toFixed(2)}</span></td>
                <td>$${parseFloat(m.cost_unit).toFixed(2)}</td>
                <td style="color:#ef4444; font-weight:700;">$${parseFloat(m.cost_total).toFixed(2)}</td>
                <td><span class="badge-reason reason-${m.reason}">${reasonLabels[m.reason] || m.reason}</span></td>
                <td style="font-size:0.8rem; color:var(--clr-text-muted);">${escapeHtml(m.notes || 'N/A')}</td>
                <td>${escapeHtml(m.registered_by)}</td>
            </tr>
            `;
        }).join('');
    } catch (err) {
        console.error('Error cargando mermas', err);
    }
}

function openWasteModal() {
    document.getElementById('wasteForm').reset();
    updateWasteCostPreview();
    document.getElementById('wasteModal').style.display = 'flex';
}

function closeWasteModal() {
    document.getElementById('wasteModal').style.display = 'none';
}

async function handleSaveWaste(e) {
    e.preventDefault();
    const payload = {
        product_id: parseInt(document.getElementById('wasteProductId').value),
        quantity: parseFloat(document.getElementById('wasteQuantity').value),
        reason: document.getElementById('wasteReason').value,
        notes: document.getElementById('wasteNotes').value.trim()
    };

    try {
        const res = await fetch('/api/admin/mermas', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        if (!res.ok) {
            showToast(data.message || 'Error al registrar merma', 'error');
            return;
        }

        showToast(data.message);
        closeWasteModal();
        loadMermas();
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
