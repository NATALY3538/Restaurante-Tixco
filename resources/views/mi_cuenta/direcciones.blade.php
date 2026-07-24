@extends('layouts.app')

@section('title', 'Mis Direcciones')

@section('content')
<div class="container">
    <!-- ═══ PAGE HEADER ═══ -->
    <div class="page-header">
        <h1>📍 Mis Direcciones</h1>
        <p>Gestiona tus direcciones de entrega para pedidos a domicilio</p>
    </div>

    <!-- ═══ ACCOUNT LAYOUT ═══ -->
    <div class="account-layout">
        <!-- Sidebar -->
        <aside class="account-sidebar">
            <nav class="sidebar-nav">
                <a href="/mi-cuenta">👤 Mi Perfil</a>
                <a href="/mi-cuenta/pedidos">📦 Mis Pedidos</a>
                <a href="/mi-cuenta/reservaciones">📅 Mis Reservaciones</a>
                <a href="/mi-cuenta/direcciones" class="active">📍 Mis Direcciones</a>
            </nav>
        </aside>

        <!-- Content -->
        <section class="account-content" style="display:flex; flex-direction:column; gap:var(--sp-xl);">
            <!-- Phone prompt card (shown when no phone saved) -->
            <div id="phonePrompt" class="card" style="padding: var(--sp-xl); display: none;">
                <h2 style="font-size: 1.2rem; font-weight: 600; margin-bottom: var(--sp-md);">🔍 Buscar mis direcciones</h2>
                <p style="color: var(--clr-text-secondary); margin-bottom: var(--sp-lg);">Ingresa tu número de teléfono para consultar tus direcciones.</p>
                <form id="phoneForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="phoneInput">Teléfono</label>
                            <input type="tel" id="phoneInput" placeholder="(555) 123-4567" required />
                        </div>
                        <div class="form-group" style="display: flex; align-items: flex-end;">
                            <button type="submit" class="btn btn-primary">🔍 Buscar</button>
                        </div>
                    </div>
                </form>
            </div>

            <div id="mainDashboard" style="display:none; display:flex; flex-direction:column; gap:var(--sp-xl);">
                <!-- Add New Address Card -->
                <div class="card" style="padding: var(--sp-xl);">
                    <h2 style="font-size: 1.2rem; font-weight: 700; margin-bottom: var(--sp-lg);">➕ Agregar Nueva Dirección</h2>
                    <form id="addressForm" onsubmit="saveAddress(event)">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="addrLabel">Alias o etiqueta (ej. Casa, Oficina)</label>
                                <input type="text" id="addrLabel" placeholder="Ej: Mi Casa" required />
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="addrRecipient">Nombre del destinatario</label>
                                <input type="text" id="addrRecipient" placeholder="Quién recibe" required />
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="addrPhone">Teléfono de contacto</label>
                                <input type="tel" id="addrPhone" placeholder="(555) 123-4567" required />
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="addrLine1">Calle y número</label>
                                <input type="text" id="addrLine1" placeholder="Ej: Av. Reforma 123 Int. 4" required />
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="addrNeighborhood">Colonia</label>
                                <input type="text" id="addrNeighborhood" placeholder="Colonia" required />
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="addrCity">Ciudad</label>
                                <input type="text" id="addrCity" placeholder="Ciudad" required />
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="addrState">Estado</label>
                                <input type="text" id="addrState" placeholder="Estado" required />
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="addrPostalCode">Código Postal</label>
                                <input type="text" id="addrPostalCode" placeholder="12345" required />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="addrNotes">Notas de entrega (ej. timbre dañado, portón negro)</label>
                            <textarea id="addrNotes" rows="2" placeholder="Notas para el repartidor..."></textarea>
                        </div>

                        <div style="display:flex; justify-content:flex-end; margin-top:var(--sp-md);">
                            <button type="submit" class="btn btn-primary" id="btnSaveAddress">💾 Guardar Dirección</button>
                        </div>
                    </form>
                </div>

                <!-- Addresses List Card -->
                <div class="card" style="padding: var(--sp-xl);">
                    <h2 style="font-size: 1.2rem; font-weight: 700; margin-bottom: var(--sp-lg);">📍 Mis Direcciones Guardadas</h2>
                    <div id="addressesList">
                        <div class="spinner" style="margin:2rem auto; display:none;" id="addressesSpinner"></div>
                        <div id="addressesGrid"></div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const phonePrompt = document.getElementById('phonePrompt');
    const mainDashboard = document.getElementById('mainDashboard');
    const phoneForm = document.getElementById('phoneForm');
    const phoneInput = document.getElementById('phoneInput');

    function getSavedPhone() {
        try {
            const user = JSON.parse(localStorage.getItem('tixco_user')) || {};
            return user.telefono || '';
        } catch { return ''; }
    }

    const savedPhone = getSavedPhone();
    if (savedPhone) {
        showDashboard(savedPhone);
    } else {
        phonePrompt.style.display = 'block';
        mainDashboard.style.display = 'none';
    }

    phoneForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const phone = phoneInput.value.trim();
        if (!phone) return;

        const user = JSON.parse(localStorage.getItem('tixco_user') || '{}');
        user.telefono = phone;
        localStorage.setItem('tixco_user', JSON.stringify(user));

        phonePrompt.style.display = 'none';
        showDashboard(phone);
    });
});

let currentPhone = '';

function showDashboard(phone) {
    currentPhone = phone;
    document.getElementById('mainDashboard').style.display = 'block';
    document.getElementById('phonePrompt').style.display = 'none';

    // Autofill name and phone in add form
    try {
        const user = JSON.parse(localStorage.getItem('tixco_user')) || {};
        document.getElementById('addrRecipient').value = user.nombre || '';
        document.getElementById('addrPhone').value = phone;
    } catch {}

    loadAddresses();
}

async function loadAddresses() {
    const grid = document.getElementById('addressesGrid');
    const spinner = document.getElementById('addressesSpinner');

    grid.innerHTML = '';
    spinner.style.display = 'block';

    try {
        const encoded = encodeURIComponent(currentPhone);
        const addresses = await apiFetch(`/customers/${encoded}/addresses`);
        spinner.style.display = 'none';

        if (!addresses || addresses.length === 0) {
            grid.innerHTML = `
                <div class="cart-empty" style="padding:var(--sp-xl);">
                    <div class="empty-icon">📍</div>
                    <p style="font-weight:500;">No tienes direcciones registradas.</p>
                    <p class="form-hint">Agrega una dirección arriba para que aparezca aquí.</p>
                </div>`;
            return;
        }

        grid.innerHTML = addresses.map(addr => `
            <div class="order-card fade-in" style="margin-bottom:var(--sp-md); justify-content:space-between; align-items:center;">
                <div style="flex:1;">
                    <div style="font-weight:700; font-size:1.1rem; display:flex; align-items:center; gap:var(--sp-xs);">
                        🏡 ${addr.label}
                        ${addr.is_default ? '<span class="tag tag-featured" style="font-size:0.7rem; padding:2px 6px;">Principal</span>' : ''}
                    </div>
                    <div style="color:var(--clr-text-secondary); margin-top:var(--sp-xs); font-size:0.9rem;">
                        <strong>Recibe:</strong> ${addr.recipient_name} | <strong>Tel:</strong> ${addr.phone}<br>
                        ${addr.address_line_1}, Col. ${addr.neighborhood}<br>
                        ${addr.city}, ${addr.state}, CP ${addr.postal_code}
                        ${addr.delivery_notes ? `<br><span style="font-style:italic; color:var(--clr-text-muted);">Nota: ${addr.delivery_notes}</span>` : ''}
                    </div>
                </div>
                <div>
                    <button class="btn btn-sm btn-danger" onclick="deleteAddress(${addr.id})">🗑️ Eliminar</button>
                </div>
            </div>
        `).join('');

    } catch (err) {
        spinner.style.display = 'none';
        grid.innerHTML = `<p style="color:var(--clr-danger); text-align:center;">Error al cargar direcciones.</p>`;
    }
}

async function saveAddress(e) {
    e.preventDefault();

    const body = {
        label: document.getElementById('addrLabel').value.trim(),
        recipientName: document.getElementById('addrRecipient').value.trim(),
        phone: document.getElementById('addrPhone').value.trim(),
        addressLine1: document.getElementById('addrLine1').value.trim(),
        neighborhood: document.getElementById('addrNeighborhood').value.trim(),
        city: document.getElementById('addrCity').value.trim(),
        state: document.getElementById('addrState').value.trim(),
        postalCode: document.getElementById('addrPostalCode').value.trim(),
        deliveryNotes: document.getElementById('addrNotes').value.trim()
    };

    const btn = document.getElementById('btnSaveAddress');
    btn.disabled = true;
    btn.innerHTML = 'Guardando...';

    try {
        const encoded = encodeURIComponent(currentPhone);
        await apiFetch(`/customers/${encoded}/addresses`, {
            method: 'POST',
            body: JSON.stringify(body)
        });

        Toast.success('Dirección guardada correctamente');
        document.getElementById('addressForm').reset();
        
        // Refill from localStorage/profile
        try {
            const user = JSON.parse(localStorage.getItem('tixco_user')) || {};
            document.getElementById('addrRecipient').value = user.nombre || '';
            document.getElementById('addrPhone').value = currentPhone;
        } catch {}

        loadAddresses();
    } catch (err) {
        Toast.error(err.message || 'Error al guardar dirección');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '💾 Guardar Dirección';
    }
}

async function deleteAddress(id) {
    if (!confirm('¿Estás seguro de que deseas eliminar esta dirección?')) return;

    try {
        await apiFetch(`/customers/addresses/${id}`, {
            method: 'DELETE'
        });
        Toast.info('Dirección eliminada');
        loadAddresses();
    } catch (err) {
        Toast.error('Error al eliminar dirección');
    }
}
</script>
@endsection
