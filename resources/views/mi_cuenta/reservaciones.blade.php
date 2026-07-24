@extends('layouts.app')

@section('title', 'Mis Reservaciones')

@section('content')
<div class="container">
    <!-- ═══ PAGE HEADER ═══ -->
    <div class="page-header">
        <h1>📅 Mis Reservaciones</h1>
        <p>Consulta el estado de tus reservaciones</p>
    </div>

    <!-- ═══ ACCOUNT LAYOUT ═══ -->
    <div class="account-layout">
        <!-- Sidebar -->
        <aside class="account-sidebar">
            <nav class="sidebar-nav">
                <a href="/mi-cuenta">👤 Mi Perfil</a>
                <a href="/mi-cuenta/pedidos">📦 Mis Pedidos</a>
                <a href="/mi-cuenta/reservaciones" class="active">📅 Mis Reservaciones</a>
                <a href="/mi-cuenta/direcciones">📍 Mis Direcciones</a>
            </nav>
        </aside>

        <!-- Content -->
        <section class="account-content">
            <!-- Phone prompt card -->
            <div id="phonePrompt" class="card" style="padding: var(--sp-xl); display: none;">
                <h2 style="font-size: 1.2rem; font-weight: 600; margin-bottom: var(--sp-md);">🔍 Buscar mis reservaciones</h2>
                <p style="color: var(--clr-text-secondary); margin-bottom: var(--sp-lg);">Ingresa tu número de teléfono para consultar tus reservaciones.</p>
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

            <!-- Loading -->
            <div id="reservationsLoading" style="text-align: center; padding: var(--sp-2xl); display: none;">
                <div class="spinner" style="margin: 0 auto var(--sp-md);"></div>
                <p style="color: var(--clr-text-secondary);">Cargando reservaciones...</p>
            </div>

            <!-- Reservations List -->
            <div class="orders-list" id="reservationsList"></div>
        </section>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const phonePrompt = document.getElementById('phonePrompt');
    const phoneForm = document.getElementById('phoneForm');
    const phoneInput = document.getElementById('phoneInput');
    const reservationsList = document.getElementById('reservationsList');
    const reservationsLoading = document.getElementById('reservationsLoading');

    function getSavedPhone() {
        try {
            const user = JSON.parse(localStorage.getItem('tixco_user')) || {};
            return user.telefono || '';
        } catch { return ''; }
    }

    const savedPhone = getSavedPhone();
    if (savedPhone) {
        loadReservations(savedPhone);
    } else {
        phonePrompt.style.display = 'block';
    }

    phoneForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const phone = phoneInput.value.trim();
        if (!phone) return;

        const user = JSON.parse(localStorage.getItem('tixco_user') || '{}');
        user.telefono = phone;
        localStorage.setItem('tixco_user', JSON.stringify(user));

        phonePrompt.style.display = 'none';
        loadReservations(phone);
    });

    async function loadReservations(phone) {
        reservationsLoading.style.display = 'block';
        reservationsList.innerHTML = '';

        try {
            const encoded = encodeURIComponent(phone);
            const reservations = await apiFetch(`/reservations/customer/${encoded}`);
            reservationsLoading.style.display = 'none';

            if (!reservations || reservations.length === 0) {
                renderEmpty();
                return;
            }

            renderReservations(reservations);
        } catch (err) {
            reservationsLoading.style.display = 'none';
            renderEmpty();
        }
    }

    function renderReservations(reservations) {
        reservationsList.innerHTML = reservations.map(res => {
            const date = new Date(res.reservation_date).toLocaleDateString('es-MX', {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
            });
            const time = res.reservation_time || '';
            const statusBadge = getStatusBadge(res.status, 'reservation');
            const code = res.reservation_code || res.id || '—';
            const partySize = res.party_size || 0;
            const notes = res.notes || '';

            return `
                <div class="order-card fade-in" style="flex-direction: column; align-items: stretch; gap: var(--sp-md);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: var(--sp-sm);">
                        <div>
                            <div style="display: flex; align-items: center; gap: var(--sp-sm); margin-bottom: var(--sp-sm);">
                                <span style="font-weight: 700; font-size: 1.05rem;">🎫 ${code}</span>
                                ${statusBadge}
                            </div>
                            <div style="color: var(--clr-text-secondary); font-size: 0.9rem; display: flex; flex-direction: column; gap: var(--sp-xs);">
                                <span>📅 ${date}</span>
                                <span>🕐 ${time}</span>
                                <span>👥 ${partySize} persona${partySize !== 1 ? 's' : ''}</span>
                            </div>
                        </div>
                    </div>
                    ${notes ? `
                        <div style="padding-top: var(--sp-sm); border-top: 1px solid var(--clr-border); color: var(--clr-text-muted); font-size: 0.85rem; font-style: italic;">
                            📝 ${notes}
                        </div>
                    ` : ''}
                </div>
            `;
        }).join('');
    }

    function renderEmpty() {
        reservationsList.innerHTML = `
            <div class="cart-empty">
                <div class="empty-icon">📅</div>
                <p style="font-size: 1.1rem; font-weight: 500;">No tienes reservaciones.</p>
                <p style="color: var(--clr-text-muted); margin-bottom: var(--sp-xl);">Cuando realices una reservación, aparecerá aquí.</p>
                <a href="/reservaciones" class="btn btn-primary">📅 Reservar Mesa</a>
            </div>
        `;
    }
});
</script>
@endsection
