@extends('layouts.app')

@section('title', 'Mis Pedidos')

@section('content')
<div class="container">
    <!-- ═══ PAGE HEADER ═══ -->
    <div class="page-header">
        <h1>📦 Mis Pedidos</h1>
        <p>Consulta el historial y estado de tus pedidos</p>
    </div>

    <!-- ═══ ACCOUNT LAYOUT ═══ -->
    <div class="account-layout">
        <!-- Sidebar -->
        <aside class="account-sidebar">
            <nav class="sidebar-nav">
                <a href="/mi-cuenta">👤 Mi Perfil</a>
                <a href="/mi-cuenta/pedidos" class="active">📦 Mis Pedidos</a>
                <a href="/mi-cuenta/reservaciones">📅 Mis Reservaciones</a>
                <a href="/mi-cuenta/direcciones">📍 Mis Direcciones</a>
            </nav>
        </aside>

        <!-- Content -->
        <section class="account-content">
            <!-- Phone prompt card (shown when no phone saved) -->
            <div id="phonePrompt" class="card" style="padding: var(--sp-xl); display: none;">
                <h2 style="font-size: 1.2rem; font-weight: 600; margin-bottom: var(--sp-md);">🔍 Buscar mis pedidos</h2>
                <p style="color: var(--clr-text-secondary); margin-bottom: var(--sp-lg);">Ingresa tu número de teléfono para consultar tus pedidos.</p>
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
            <div id="ordersLoading" style="text-align: center; padding: var(--sp-2xl); display: none;">
                <div class="spinner" style="margin: 0 auto var(--sp-md);"></div>
                <p style="color: var(--clr-text-secondary);">Cargando pedidos...</p>
            </div>

            <!-- Orders List -->
            <div class="orders-list" id="ordersList"></div>
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
    const ordersList = document.getElementById('ordersList');
    const ordersLoading = document.getElementById('ordersLoading');

    const ORDER_TYPE_LABELS = {
        'dine_in': '🍽️ En mesa',
        'pickup': '🥡 Para llevar',
        'delivery': '🛵 A domicilio'
    };

    function getSavedPhone() {
        try {
            const user = JSON.parse(localStorage.getItem('tixco_user')) || {};
            return user.telefono || '';
        } catch { return ''; }
    }

    const savedPhone = getSavedPhone();
    if (savedPhone) {
        loadOrders(savedPhone);
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
        loadOrders(phone);
    });

    async function loadOrders(phone) {
        ordersLoading.style.display = 'block';
        ordersList.innerHTML = '';

        try {
            const encoded = encodeURIComponent(phone);
            const orders = await apiFetch(`/orders/customer/${encoded}`);
            ordersLoading.style.display = 'none';

            if (!orders || orders.length === 0) {
                renderEmpty();
                return;
            }

            renderOrders(orders);
        } catch (err) {
            ordersLoading.style.display = 'none';
            renderEmpty();
        }
    }

    function renderOrders(orders) {
        ordersList.innerHTML = orders.map(order => {
            const date = new Date(order.created_at).toLocaleDateString('es-MX', {
                year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
            });
            const orderType = ORDER_TYPE_LABELS[order.order_type] || order.order_type;
            const statusBadge = getStatusBadge(order.status, 'order');
            const total = formatMoney(order.total || 0);
            const orderNum = order.order_number || order.id || '—';

            // Items breakdown
            const itemsHtml = (order.items || []).map(item => {
                const itemMods = (item.modifiers || []).map(m => `+ ${m.modifier_name}`).join(', ');
                const modsStr = itemMods ? `<div style="font-size:0.75rem;color:var(--clr-text-muted);">${itemMods}</div>` : '';
                return `
                    <div style="padding:4px 0;border-bottom:1px solid var(--clr-border);display:flex;justify-content:space-between;font-size:0.85rem;">
                        <span>${item.quantity}x ${item.product_name}</span>
                        <span>${formatMoney(item.total)}</span>
                    </div>
                    ${modsStr}
                `;
            }).join('');

            return `
                <div class="order-card fade-in" style="flex-direction:column;align-items:stretch;gap:var(--sp-md);">
                    <div style="display:flex;justify-content:between;align-items:center;flex-wrap:wrap;gap:var(--sp-sm);">
                        <div style="flex:1;">
                            <div style="display: flex; align-items: center; gap: var(--sp-sm); flex-wrap: wrap;">
                                <span style="font-weight: 700; font-size: 1.05rem;">#${orderNum}</span>
                                <span class="badge" style="background: var(--clr-surface-2); font-size: 0.75rem;">${orderType}</span>
                                ${statusBadge}
                            </div>
                            <div style="color: var(--clr-text-secondary); font-size: 0.85rem; margin-top:2px;">
                                📅 ${date}
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <span style="font-weight: 700; font-size: 1.15rem; color: var(--clr-primary);">${total}</span>
                        </div>
                    </div>
                    <div style="background:var(--clr-surface-2);padding:var(--sp-md);border-radius:var(--radius-sm);">
                        <h4 style="font-size:0.85rem;margin-bottom:var(--sp-xs);border-bottom:1px solid var(--clr-border);padding-bottom:4px;">Artículos</h4>
                        ${itemsHtml}
                    </div>
                </div>
            `;
        }).join('');
    }

    function renderEmpty() {
        ordersList.innerHTML = `
            <div class="cart-empty">
                <div class="empty-icon">📦</div>
                <p style="font-size: 1.1rem; font-weight: 500;">No tienes pedidos aún.</p>
                <p style="color: var(--clr-text-muted); margin-bottom: var(--sp-xl);">Cuando realices tu primer pedido, aparecerá aquí.</p>
                <a href="/menu" class="btn btn-primary">🍽️ Ver Menú</a>
            </div>
        `;
    }
});
</script>
@endsection
