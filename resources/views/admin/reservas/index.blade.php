@extends('layouts.app')

@section('title', 'Gestión de Reservas - Tixco Admin')

@section('styles')
<style>
.reservas-header {
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

.reservas-box {
    background: var(--clr-surface-1);
    border: 1px solid var(--clr-border);
    border-radius: var(--radius-lg);
    padding: var(--sp-lg);
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    overflow-x: auto;
}

.table-filters {
    display: flex;
    gap: 8px;
    margin-bottom: var(--sp-md);
    flex-wrap: wrap;
}

.filter-btn {
    background: var(--clr-surface-2);
    border: 1px solid var(--clr-border);
    color: var(--clr-text-muted);
    padding: 6px 14px;
    border-radius: var(--radius-full);
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.2s;
}

.filter-btn.active {
    background: var(--clr-primary);
    color: #fff;
    border-color: var(--clr-primary);
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

.status-pendiente { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid #f59e0b; }
.status-aceptada { background: rgba(34, 197, 94, 0.2); color: #4ade80; border: 1px solid #22c55e; }
.status-rechazada { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid #ef4444; }

.btn-action-accept {
    background: #16a34a;
    color: #fff;
    border: none;
    padding: 6px 12px;
    border-radius: var(--radius-sm);
    font-weight: 600;
    cursor: pointer;
    font-size: 0.82rem;
    transition: transform 0.15s;
}

.btn-action-accept:hover {
    background: #15803d;
    transform: scale(1.03);
}

.btn-action-reject {
    background: #dc2626;
    color: #fff;
    border: none;
    padding: 6px 12px;
    border-radius: var(--radius-sm);
    font-weight: 600;
    cursor: pointer;
    font-size: 0.82rem;
    transition: transform 0.15s;
}

.btn-action-reject:hover {
    background: #b91c1c;
    transform: scale(1.03);
}
</style>
@endsection

@section('content')
<div class="container" style="padding-top: var(--sp-xl); padding-bottom: var(--sp-xxl);">
    <!-- Header -->
    <div class="reservas-header">
        <div>
            <h1 style="font-family:var(--font-display); font-size:2rem; margin-bottom:0.25rem; color:var(--clr-primary);">
                📅 Gestión de Reservaciones
            </h1>
            <p style="color:var(--clr-text-muted); font-size:0.95rem;">
                Administración de solicitudes de comensales, control de sobrecupo y notificación en tiempo real.
            </p>
        </div>
        <div>
            <a href="/admin/areas-mesas" class="btn btn-secondary" style="font-weight:600;">
                🏛️ Ver Mapa de Áreas y Mesas
            </a>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <span style="font-size:0.85rem; color:var(--clr-text-muted);">Total de Solicitudes</span>
            <div class="stat-val" id="statTotal">0</div>
        </div>
        <div class="stat-card">
            <span style="font-size:0.85rem; color:var(--clr-text-muted);">⏳ Pendientes</span>
            <div class="stat-val" id="statPending" style="color:#fbbf24;">0</div>
        </div>
        <div class="stat-card">
            <span style="font-size:0.85rem; color:var(--clr-text-muted);">✅ Aceptadas</span>
            <div class="stat-val" id="statAccepted" style="color:#4ade80;">0</div>
        </div>
        <div class="stat-card">
            <span style="font-size:0.85rem; color:var(--clr-text-muted);">❌ Rechazadas</span>
            <div class="stat-val" id="statRejected" style="color:#f87171;">0</div>
        </div>
    </div>

    <!-- Reservas Table Box -->
    <div class="reservas-box">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; margin-bottom:var(--sp-sm);">
            <h3 style="font-family:var(--font-display); font-size:1.2rem; color:var(--clr-text);">
                📋 Solicitudes de Reservación
            </h3>
            <!-- Filters -->
            <div class="table-filters">
                <button class="filter-btn active" onclick="setFilter('todos', this)">Todos</button>
                <button class="filter-btn" onclick="setFilter('pendiente', this)">⏳ Pendientes</button>
                <button class="filter-btn" onclick="setFilter('aceptada', this)">✅ Aceptadas</button>
                <button class="filter-btn" onclick="setFilter('rechazada', this)">❌ Rechazadas</button>
            </div>
        </div>

        <table class="custom-table">
            <thead>
                <tr>
                    <th>Código / Cliente</th>
                    <th>Contacto</th>
                    <th>Fecha y Hora</th>
                    <th>Personas</th>
                    <th>Mesa / Área</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="reservasTableBody">
                <tr>
                    <td colspan="7" style="text-align:center; padding:2rem; color:var(--clr-text-muted);">
                        Cargando reservaciones...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ═══ MODAL ACEPTAR RESERVACIÓN ═══ -->
<div id="modalAccept" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); backdrop-filter:blur(6px); z-index:9999; justify-content:center; align-items:center; padding:var(--sp-md);">
    <div style="background:var(--clr-surface-1); border:1px solid var(--clr-border); border-radius:var(--radius-lg); width:100%; max-width:520px; padding:var(--sp-lg); box-shadow:0 20px 40px rgba(0,0,0,0.7);">
        <h3 style="font-family:var(--font-display); font-size:1.3rem; color:#4ade80; margin-bottom:var(--sp-md); display:flex; align-items:center; gap:8px;">
            🟢 Aceptar y Confirmar Reservación
        </h3>
        
        <div id="acceptDetailsBox" style="background:var(--clr-surface-2); padding:12px; border-radius:8px; margin-bottom:var(--sp-md); font-size:0.9rem;">
            <!-- Dynamic Reservation Info -->
        </div>

        <!-- Conflict Alert Banner (hidden by default) -->
        <div id="conflictAlert" style="display:none; background:rgba(239, 68, 68, 0.15); border:1px solid #ef4444; color:#f87171; border-radius:8px; padding:12px; margin-bottom:var(--sp-md); font-size:0.85rem;">
            <strong display="block" style="font-size:0.9rem; margin-bottom:4px;">⚠️ Conflicto de Disponibilidad Detectado</strong>
            <span id="conflictText"></span>
        </div>

        <form id="formAccept" onsubmit="handleConfirmAccept(event)">
            <input type="hidden" id="acceptReservationId">
            <input type="hidden" id="acceptForce" value="0">

            <div style="margin-bottom:var(--sp-sm);">
                <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Asignar Mesa *</label>
                <select id="acceptMesaId" required class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);"></select>
            </div>

            <div style="margin-bottom:var(--sp-lg);">
                <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Notas de Confirmación (Opcional)</label>
                <textarea id="acceptAdminNotes" class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm); min-height:60px;" placeholder="Ej. Mesa reservada en terraza con vista..."></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:var(--sp-sm);">
                <button type="button" onclick="closeAcceptModal()" class="btn btn-secondary">Cancelar</button>
                <button type="submit" id="btnConfirmAccept" class="btn btn-primary" style="background:#16a34a; border-color:#16a34a; font-weight:600;">
                    ✅ Aceptar y Notificar al Cliente
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ═══ MODAL RECHAZAR RESERVACIÓN ═══ -->
<div id="modalReject" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); backdrop-filter:blur(6px); z-index:9999; justify-content:center; align-items:center; padding:var(--sp-md);">
    <div style="background:var(--clr-surface-1); border:1px solid var(--clr-border); border-radius:var(--radius-lg); width:100%; max-width:500px; padding:var(--sp-lg); box-shadow:0 20px 40px rgba(0,0,0,0.7);">
        <h3 style="font-family:var(--font-display); font-size:1.3rem; color:#f87171; margin-bottom:var(--sp-md); display:flex; align-items:center; gap:8px;">
            🔴 Rechazar Reservación
        </h3>
        
        <p style="font-size:0.9rem; color:var(--clr-text-muted); margin-bottom:var(--sp-md);">
            Al rechazar la solicitud, el cliente recibirá inmediatamente una notificación con la explicación indicada.
        </p>

        <form id="formReject" onsubmit="handleConfirmReject(event)">
            <input type="hidden" id="rejectReservationId">

            <div style="margin-bottom:var(--sp-lg);">
                <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Motivo de Rechazo *</label>
                <textarea id="rejectReason" required class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:10px 12px; border-radius:var(--radius-sm); min-height:80px;" placeholder="Ej. Sobrecupo de mesas en el horario seleccionado de 14:00 a 16:00 hrs."></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:var(--sp-sm);">
                <button type="button" onclick="closeRejectModal()" class="btn btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-primary" style="background:#dc2626; border-color:#dc2626; font-weight:600;">
                    🔴 Confirmar Rechazo
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
let allReservations = [];
let tablesList = [];
let currentFilter = 'todos';

document.addEventListener('DOMContentLoaded', () => {
    loadTables();
    loadReservations();
});

async function loadTables() {
    try {
        const res = await fetch('/api/tables');
        tablesList = await res.json();
    } catch (err) {
        console.error('Error cargando mesas', err);
    }
}

async function loadReservations() {
    try {
        const res = await fetch('/api/admin/reservas');
        const data = await res.json();

        allReservations = data.reservations || [];
        
        // Stats
        document.getElementById('statTotal').innerText = data.stats.total || 0;
        document.getElementById('statPending').innerText = data.stats.pending || 0;
        document.getElementById('statAccepted').innerText = data.stats.accepted || 0;
        document.getElementById('statRejected').innerText = data.stats.rejected || 0;

        renderReservations();
    } catch (err) {
        console.error('Error cargando reservaciones', err);
    }
}

function setFilter(filter, btn) {
    currentFilter = filter;
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    renderReservations();
}

function renderReservations() {
    const tbody = document.getElementById('reservasTableBody');
    let filtered = allReservations;

    if (currentFilter === 'pendiente') {
        filtered = allReservations.filter(r => ['pendiente', 'pending'].includes(r.status));
    } else if (currentFilter === 'aceptada') {
        filtered = allReservations.filter(r => ['aceptada', 'confirmed', 'accepted'].includes(r.status));
    } else if (currentFilter === 'rechazada') {
        filtered = allReservations.filter(r => ['rechazada', 'rejected'].includes(r.status));
    }

    if (filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:2rem; color:var(--clr-text-muted);">No hay reservaciones registradas en esta categoría.</td></tr>`;
        return;
    }

    tbody.innerHTML = filtered.map(r => {
        const isPending = ['pendiente', 'pending'].includes(r.status);
        const isAccepted = ['aceptada', 'confirmed', 'accepted'].includes(r.status);
        const isRejected = ['rechazada', 'rejected'].includes(r.status);

        let statusBadge = `<span class="badge-status status-pendiente">⏳ Pendiente</span>`;
        if (isAccepted) statusBadge = `<span class="badge-status status-aceptada">✅ Aceptada</span>`;
        if (isRejected) statusBadge = `<span class="badge-status status-rechazada">❌ Rechazada</span>`;

        const mesaName = r.mesa ? `${r.mesa.name} (${r.mesa.service_area ? r.mesa.service_area.name : 'Zona'})` : 'Sin asignar';

        return `
        <tr>
            <td>
                <strong>#${escapeHtml(r.reservation_code)}</strong><br>
                <span style="font-weight:600;">${escapeHtml(r.customer_name)}</span>
            </td>
            <td style="font-size:0.85rem;">
                📧 ${escapeHtml(r.customer_email || 'N/A')}<br>
                📞 ${escapeHtml(r.customer_phone || 'N/A')}
            </td>
            <td>
                📅 <strong>${r.reservation_date}</strong><br>
                ⏰ ${r.reservation_time} hrs
            </td>
            <td>👥 <strong>${r.party_size} pers.</strong></td>
            <td>
                🪑 ${escapeHtml(mesaName)}
            </td>
            <td>${statusBadge}</td>
            <td>
                <div style="display:flex; gap:6px;">
                    ${isPending || isRejected ? `
                        <button onclick="openAcceptModal(${r.id})" class="btn-action-accept" title="Aceptar Reservación">
                            🟢 Aceptar
                        </button>
                    ` : ''}
                    ${isPending || isAccepted ? `
                        <button onclick="openRejectModal(${r.id})" class="btn-action-reject" title="Rechazar Reservación">
                            🔴 Rechazar
                        </button>
                    ` : ''}
                </div>
            </td>
        </tr>
        `;
    }).join('');
}

function openAcceptModal(id) {
    const r = allReservations.find(item => item.id === id);
    if (!r) return;

    document.getElementById('acceptReservationId').value = r.id;
    document.getElementById('acceptForce').value = '0';
    document.getElementById('conflictAlert').style.display = 'none';

    document.getElementById('acceptDetailsBox').innerHTML = `
        <strong>Cliente:</strong> ${escapeHtml(r.customer_name)} (${r.party_size} personas)<br>
        <strong>Fecha y Hora:</strong> 📅 ${r.reservation_date} a las ⏰ ${r.reservation_time} hrs<br>
        <strong>Notas Cliente:</strong> ${escapeHtml(r.notes || 'Ninguna')}
    `;

    // Populate tables select dropdown
    const select = document.getElementById('acceptMesaId');
    select.innerHTML = tablesList.map(t => {
        const isSelected = r.mesa_id === t.id ? 'selected' : '';
        const areaName = t.service_area ? t.service_area.name : 'Zona';
        return `<option value="${t.id}" ${isSelected}>
            ${escapeHtml(t.name)} (${areaName}) — Capacidad: ${t.capacity} pers.
        </option>`;
    }).join('');

    document.getElementById('modalAccept').style.display = 'flex';
}

function closeAcceptModal() {
    document.getElementById('modalAccept').style.display = 'none';
}

async function handleConfirmAccept(e) {
    e.preventDefault();
    const id = document.getElementById('acceptReservationId').value;
    const mesa_id = document.getElementById('acceptMesaId').value;
    const admin_notes = document.getElementById('acceptAdminNotes').value.trim();
    const force = document.getElementById('acceptForce').value === '1';

    try {
        const res = await fetch(`/api/admin/reservas/${id}/accept`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ mesa_id: parseInt(mesa_id), admin_notes, force })
        });

        const data = await res.json();

        if (!res.ok) {
            if (data.conflict) {
                // Show conflict banner with force button option
                document.getElementById('conflictText').innerText = data.message;
                document.getElementById('conflictAlert').style.display = 'block';
                document.getElementById('acceptForce').value = '1';
                document.getElementById('btnConfirmAccept').innerText = '⚠️ Forzar Aprobación y Ocupación';
                return;
            }
            showToast(data.message || 'Error al aceptar la reservación', 'error');
            return;
        }

        showToast(data.message);
        closeAcceptModal();
        loadReservations();
    } catch (err) {
        showToast('Error al conectar con el servidor', 'error');
    }
}

function openRejectModal(id) {
    const r = allReservations.find(item => item.id === id);
    if (!r) return;

    document.getElementById('rejectReservationId').value = r.id;
    document.getElementById('rejectReason').value = '';
    document.getElementById('modalReject').style.display = 'flex';
}

function closeRejectModal() {
    document.getElementById('modalReject').style.display = 'none';
}

async function handleConfirmReject(e) {
    e.preventDefault();
    const id = document.getElementById('rejectReservationId').value;
    const reason = document.getElementById('rejectReason').value.trim();

    try {
        const res = await fetch(`/api/admin/reservas/${id}/reject`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ reason })
        });

        const data = await res.json();
        if (!res.ok) {
            showToast(data.message || 'Error al rechazar reservación', 'error');
            return;
        }

        showToast(data.message);
        closeRejectModal();
        loadReservations();
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
