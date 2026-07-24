@extends('layouts.app')

@section('title', 'Gestión de Sucursales y Filiales Multi-Branch')

@section('styles')
<style>
.sucursales-header {
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
    font-size: 1.6rem;
    font-weight: 700;
    font-family: var(--font-display);
    color: var(--clr-primary);
    margin-top: 4px;
}

.sucursales-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
    gap: var(--sp-lg);
    margin-bottom: var(--sp-xl);
}

.sucursal-card {
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

.sucursal-card:hover {
    transform: translateY(-3px);
    border-color: var(--clr-primary);
}

.matriz-badge {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #ffffff;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    box-shadow: 0 2px 8px rgba(245,158,11,0.3);
}

.day-tag {
    background: var(--clr-surface-2);
    border: 1px solid var(--clr-border);
    color: var(--clr-text-muted);
    font-size: 0.7rem;
    padding: 2px 6px;
    border-radius: 6px;
}
</style>
@endsection

@section('content')
<div class="container" style="padding-top: var(--sp-xl); padding-bottom: var(--sp-xxl);">
    <!-- Header -->
    <div class="sucursales-header">
        <div>
            <h1 style="font-family:var(--font-display); font-size:2rem; margin-bottom:0.25rem; color:var(--clr-primary);">
                🏢 Gestión de Sucursales Multi-Branch
            </h1>
            <p style="color:var(--clr-text-muted); font-size:0.95rem;">
                Administra los puntos de venta, horarios, datos fiscales y sucursal matriz de Tixco.
            </p>
        </div>
        <div style="display:flex; gap:var(--sp-sm); flex-wrap:wrap;">
            <button onclick="openSucursalModal()" class="btn btn-primary" style="display:flex; align-items:center; gap:6px; background:var(--clr-primary); font-weight:700;">
                ➕ Registrar Nueva Sucursal
            </button>
            <a href="/admin" class="btn btn-secondary">
                ⬅️ Panel General
            </a>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <span style="font-size:0.85rem; color:var(--clr-text-muted);">Total de Sucursales</span>
            <div class="stat-val" id="statTotalSucursales">0</div>
        </div>
        <div class="stat-card">
            <span style="font-size:0.85rem; color:var(--clr-text-muted);">Sucursales Activas</span>
            <div class="stat-val" id="statActiveSucursales" style="color:#34d399;">0</div>
        </div>
        <div class="stat-card">
            <span style="font-size:0.85rem; color:var(--clr-text-muted);">Matriz Principal</span>
            <div class="stat-val" id="statMatrizNombre" style="font-size:1.1rem; color:#f59e0b; font-weight:600; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;">Cargando...</div>
        </div>
    </div>

    <!-- Sucursales Grid Cards -->
    <div id="sucursalesGridContainer" class="sucursales-grid">
        <div style="grid-column:1/-1; text-align:center; padding:3rem; background:var(--clr-surface-1); border-radius:var(--radius-lg); color:var(--clr-text-muted);">
            Cargando información de sucursales...
        </div>
    </div>
</div>

<!-- ═══ MODAL CREAR / EDITAR SUCURSAL (ModalSucursalSave) ═══ -->
<div id="sucursalModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); backdrop-filter:blur(8px); z-index:9999; justify-content:center; align-items:center; padding:var(--sp-md); overflow-y:auto;">
    <div style="background:var(--clr-surface-1); border:1px solid var(--clr-border); border-radius:var(--radius-lg); width:100%; max-width:620px; padding:var(--sp-lg); box-shadow:0 20px 40px rgba(0,0,0,0.8); max-height:92vh; overflow-y:auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--sp-md);">
            <h3 id="sucursalModalTitle" style="font-family:var(--font-display); font-size:1.3rem; color:var(--clr-text); margin:0;">
                🏢 Registrar Nueva Sucursal
            </h3>
            <button onclick="closeSucursalModal()" style="background:none; border:none; color:var(--clr-text-muted); font-size:1.2rem; cursor:pointer;">✕</button>
        </div>

        <form id="sucursalForm" onsubmit="handleSaveSucursal(event)">
            <input type="hidden" id="sucursalId" value="">

            <div style="margin-bottom:var(--sp-sm);">
                <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Nombre de la Sucursal *</label>
                <input type="text" id="sucNombre" required class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:10px 12px; border-radius:var(--radius-sm);" placeholder="Ej. Tixco - Sucursal Plaza Norte">
            </div>

            <div style="margin-bottom:var(--sp-sm);">
                <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Dirección (Calle y Número) *</label>
                <input type="text" id="sucCalle" required class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);" placeholder="Ej. Av. Insurgentes Norte #1250, Local 4">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--sp-sm); margin-bottom:var(--sp-sm);">
                <div>
                    <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Colonia / Ciudad *</label>
                    <input type="text" id="sucColonia" required class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);" placeholder="Ej. Gustavo A. Madero, CDMX">
                </div>
                <div>
                    <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Código Postal *</label>
                    <input type="text" id="sucCp" required class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);" placeholder="Ej. 07300">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--sp-sm); margin-bottom:var(--sp-sm);">
                <div>
                    <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Teléfono de Contacto *</label>
                    <input type="text" id="sucTelefono" required class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);" placeholder="(55) 5789-0123">
                </div>
                <div>
                    <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Correo Electrónico *</label>
                    <input type="email" id="sucEmail" required class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);" placeholder="plazanorte@tixco.com">
                </div>
            </div>

            <div style="margin-bottom:var(--sp-sm);">
                <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">RFC / Identificación Fiscal (Opcional)</label>
                <input type="text" id="sucRfc" class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);" placeholder="Ej. TIX260701ABC">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--sp-sm); margin-bottom:var(--sp-md);">
                <div>
                    <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Horario Apertura *</label>
                    <input type="time" id="sucApertura" required value="08:00" class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);">
                </div>
                <div>
                    <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Horario Cierre *</label>
                    <input type="time" id="sucCierre" required value="23:00" class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);">
                </div>
            </div>

            <!-- Días de Operación Checkboxes -->
            <div style="margin-bottom:var(--sp-md);">
                <label style="display:block; font-size:0.85rem; font-weight:600; color:var(--clr-text); margin-bottom:6px;">📅 Días de Operación Semanal</label>
                <div style="display:flex; flex-wrap:wrap; gap:8px; background:var(--clr-surface-2); padding:10px; border-radius:8px; border:1px solid var(--clr-border);">
                    <label style="display:flex; align-items:center; gap:4px; font-size:0.8rem; color:var(--clr-text); cursor:pointer;">
                        <input type="checkbox" name="dias" value="Lunes" checked style="accent-color:var(--clr-primary);"> Lun
                    </label>
                    <label style="display:flex; align-items:center; gap:4px; font-size:0.8rem; color:var(--clr-text); cursor:pointer;">
                        <input type="checkbox" name="dias" value="Martes" checked style="accent-color:var(--clr-primary);"> Mar
                    </label>
                    <label style="display:flex; align-items:center; gap:4px; font-size:0.8rem; color:var(--clr-text); cursor:pointer;">
                        <input type="checkbox" name="dias" value="Miércoles" checked style="accent-color:var(--clr-primary);"> Mié
                    </label>
                    <label style="display:flex; align-items:center; gap:4px; font-size:0.8rem; color:var(--clr-text); cursor:pointer;">
                        <input type="checkbox" name="dias" value="Jueves" checked style="accent-color:var(--clr-primary);"> Jue
                    </label>
                    <label style="display:flex; align-items:center; gap:4px; font-size:0.8rem; color:var(--clr-text); cursor:pointer;">
                        <input type="checkbox" name="dias" value="Viernes" checked style="accent-color:var(--clr-primary);"> Vie
                    </label>
                    <label style="display:flex; align-items:center; gap:4px; font-size:0.8rem; color:var(--clr-text); cursor:pointer;">
                        <input type="checkbox" name="dias" value="Sábado" checked style="accent-color:var(--clr-primary);"> Sáb
                    </label>
                    <label style="display:flex; align-items:center; gap:4px; font-size:0.8rem; color:var(--clr-text); cursor:pointer;">
                        <input type="checkbox" name="dias" value="Domingo" checked style="accent-color:var(--clr-primary);"> Dom
                    </label>
                </div>
            </div>

            <!-- Configuration Checkboxes -->
            <div style="display:flex; gap:var(--sp-md); margin-bottom:var(--sp-lg); background:var(--clr-surface-2); padding:10px; border-radius:8px;">
                <label style="display:flex; align-items:center; gap:8px; font-size:0.85rem; color:var(--clr-text); cursor:pointer;">
                    <input type="checkbox" id="sucIsMatriz" style="accent-color:#f59e0b; width:16px; height:16px;">
                    <span>⭐ Asignar como Sucursal Matriz Principal</span>
                </label>
                <label style="display:flex; align-items:center; gap:8px; font-size:0.85rem; color:var(--clr-text); cursor:pointer;">
                    <input type="checkbox" id="sucIsActive" checked style="accent-color:var(--clr-primary); width:16px; height:16px;">
                    <span>🟢 Sucursal Activa</span>
                </label>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:var(--sp-sm);">
                <button type="button" onclick="closeSucursalModal()" class="btn btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-primary" style="background:var(--clr-primary); font-weight:700;">
                    💾 Guardar Sucursal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
let sucursalesData = [];

document.addEventListener('DOMContentLoaded', () => {
    loadSucursales();
});

async function loadSucursales() {
    try {
        const res = await fetch('/api/admin/sucursales');
        const data = await res.json();

        document.getElementById('statTotalSucursales').innerText = data.stats.total;
        document.getElementById('statActiveSucursales').innerText = data.stats.activas;
        document.getElementById('statMatrizNombre').innerText = data.stats.matriz_nombre;

        sucursalesData = data.sucursales || [];
        renderSucursalesGrid(sucursalesData);
    } catch (err) {
        console.error('Error cargando sucursales', err);
    }
}

function renderSucursalesGrid(sucursales) {
    const container = document.getElementById('sucursalesGridContainer');
    if (!sucursales || sucursales.length === 0) {
        container.innerHTML = `<div style="grid-column:1/-1; text-align:center; padding:3rem; background:var(--clr-surface-1); border-radius:var(--radius-lg); color:var(--clr-text-muted);">No hay sucursales registradas.</div>`;
        return;
    }

    container.innerHTML = sucursales.map(suc => {
        const dias = suc.dias_operacion || [];
        const diasHTML = dias.map(d => `<span class="day-tag">${d.slice(0,3)}</span>`).join(' ');

        return `
        <div class="sucursal-card" style="border-left: 4px solid ${suc.is_matriz ? '#f59e0b' : (suc.is_active ? 'var(--clr-primary)' : '#64748b')};">
            <div>
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:var(--sp-sm);">
                    <div>
                        <h3 style="font-family:var(--font-display); font-size:1.2rem; color:var(--clr-text); margin:0; display:flex; align-items:center; gap:8px;">
                            🏢 ${escapeHtml(suc.nombre)}
                        </h3>
                        <div style="margin-top:4px;">
                            ${suc.is_matriz ? '<span class="matriz-badge">⭐ Matriz Principal</span>' : '<span style="font-size:0.75rem; color:var(--clr-text-muted); background:var(--clr-surface-2); padding:2px 8px; border-radius:10px;">🏢 Filial</span>'}
                        </div>
                    </div>
                    <span class="badge ${suc.is_active ? 'badge-success' : 'badge-danger'}" style="font-size:0.75rem;">
                        ${suc.is_active ? '🟢 Activa' : '🔴 Inactiva'}
                    </span>
                </div>

                <div style="font-size:0.85rem; color:var(--clr-text-muted); line-height:1.5; margin-bottom:var(--sp-md);">
                    <div style="display:flex; align-items:flex-start; gap:6px; margin-bottom:4px;">
                        <span>📍</span>
                        <span>${escapeHtml(suc.direccion_calle)}, ${escapeHtml(suc.colonia_ciudad)} (CP ${escapeHtml(suc.codigo_postal)})</span>
                    </div>
                    <div style="display:flex; gap:12px; margin-bottom:4px;">
                        <span>📞 ${escapeHtml(suc.telefono_contacto)}</span>
                        <span>✉️ ${escapeHtml(suc.email_contacto)}</span>
                    </div>
                    ${suc.rfc_identificacion_fiscal ? `<div>📑 RFC: <code>${escapeHtml(suc.rfc_identificacion_fiscal)}</code></div>` : ''}
                </div>

                <div style="background:var(--clr-surface-2); border:1px solid var(--clr-border); padding:8px 12px; border-radius:8px; margin-bottom:var(--sp-md);">
                    <div style="font-size:0.8rem; color:var(--clr-text); font-weight:600; margin-bottom:4px; display:flex; justify-content:space-between;">
                        <span>⏰ Horario Operativo:</span>
                        <span style="color:var(--clr-primary);">${escapeHtml(suc.horario_apertura)} - ${escapeHtml(suc.horario_cierre)} hrs</span>
                    </div>
                    <div style="display:flex; flex-wrap:wrap; gap:4px; margin-top:4px;">
                        ${diasHTML || '<span style="font-size:0.72rem; color:var(--clr-text-muted);">Todos los días</span>'}
                    </div>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--sp-xs);">
                <button onclick="openSucursalModal(${suc.id})" class="btn btn-sm btn-secondary" style="font-size:0.8rem;">
                    ✏️ Editar Sucursal
                </button>
                <button onclick="toggleSucursalStatus(${suc.id}, ${suc.is_active})" class="btn btn-sm ${suc.is_active ? 'btn-danger' : 'btn-success'}" style="font-size:0.8rem;">
                    ${suc.is_active ? '🚫 Desactivar' : '✅ Activar'}
                </button>
            </div>
        </div>
        `;
    }).join('');
}

function openSucursalModal(id = null) {
    document.getElementById('sucursalForm').reset();
    document.getElementById('sucursalId').value = id || '';
    document.getElementById('sucursalModalTitle').innerText = id ? '✏️ Editar Sucursal' : '🏢 Registrar Nueva Sucursal';

    if (id) {
        const suc = sucursalesData.find(s => s.id === id);
        if (suc) {
            document.getElementById('sucNombre').value = suc.nombre || '';
            document.getElementById('sucCalle').value = suc.direccion_calle || '';
            document.getElementById('sucColonia').value = suc.colonia_ciudad || '';
            document.getElementById('sucCp').value = suc.codigo_postal || '';
            document.getElementById('sucTelefono').value = suc.telefono_contacto || '';
            document.getElementById('sucEmail').value = suc.email_contacto || '';
            document.getElementById('sucRfc').value = suc.rfc_identificacion_fiscal || '';
            document.getElementById('sucApertura').value = suc.horario_apertura || '08:00';
            document.getElementById('sucCierre').value = suc.horario_cierre || '23:00';
            document.getElementById('sucIsMatriz').checked = !!suc.is_matriz;
            document.getElementById('sucIsActive').checked = !!suc.is_active;

            const dias = suc.dias_operacion || [];
            document.querySelectorAll('input[name="dias"]').forEach(cb => {
                cb.checked = dias.includes(cb.value);
            });
        }
    }

    document.getElementById('sucursalModal').style.display = 'flex';
}

function closeSucursalModal() {
    document.getElementById('sucursalModal').style.display = 'none';
}

async function handleSaveSucursal(e) {
    e.preventDefault();
    const id = document.getElementById('sucursalId').value;

    const selectedDias = Array.from(document.querySelectorAll('input[name="dias"]:checked')).map(cb => cb.value);

    const payload = {
        nombre: document.getElementById('sucNombre').value.trim(),
        direccion_calle: document.getElementById('sucCalle').value.trim(),
        colonia_ciudad: document.getElementById('sucColonia').value.trim(),
        codigo_postal: document.getElementById('sucCp').value.trim(),
        telefono_contacto: document.getElementById('sucTelefono').value.trim(),
        email_contacto: document.getElementById('sucEmail').value.trim(),
        rfc_identificacion_fiscal: document.getElementById('sucRfc').value.trim(),
        horario_apertura: document.getElementById('sucApertura').value,
        horario_cierre: document.getElementById('sucCierre').value,
        dias_operacion: selectedDias,
        is_matriz: document.getElementById('sucIsMatriz').checked,
        is_active: document.getElementById('sucIsActive').checked
    };

    try {
        const url = id ? `/api/admin/sucursales/${id}` : '/api/admin/sucursales';
        const method = id ? 'PUT' : 'POST';

        const res = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        if (!res.ok) {
            showToast(data.message || 'Error al guardar sucursal', 'error');
            return;
        }

        showToast(data.message);
        closeSucursalModal();
        loadSucursales();
    } catch (err) {
        showToast('Error de conexión con el servidor', 'error');
    }
}

async function toggleSucursalStatus(id, currentStatus) {
    try {
        const res = await fetch(`/api/admin/sucursales/${id}/status`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ is_active: !currentStatus })
        });
        const data = await res.json();
        if (!res.ok) {
            showToast(data.message || 'No se puede cambiar el estado de la sucursal', 'error');
            return;
        }
        showToast(data.message);
        loadSucursales();
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
