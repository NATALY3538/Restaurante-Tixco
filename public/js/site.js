/* ══════════════════════════════════════════════════════════════════════════════
   RESTAURANTE TIXCO — GLOBAL JS
   Carrito (localStorage), Toasts, Navegación activa, Utilidades
   ══════════════════════════════════════════════════════════════════════════════ */

const API_BASE = '/api';

// ── Cart Manager ──────────────────────────────────────────────────────────────
const Cart = {
    KEY: 'tixco_cart',

    getItems() {
        try { return JSON.parse(localStorage.getItem(this.KEY)) || []; }
        catch { return []; }
    },

    save(items) {
        localStorage.setItem(this.KEY, JSON.stringify(items));
        this.updateBadge();
        window.dispatchEvent(new Event('cart-updated'));
    },

    add(product, quantity, modifiers, specialNote) {
        const items = this.getItems();
        const modKey = (modifiers || []).map(m => m.modifierId).sort().join(',');
        const existing = items.find(i => i.productId === product.id && i.modKey === modKey && i.specialNote === (specialNote || ''));

        if (existing) {
            existing.quantity += quantity;
        } else {
            items.push({
                cartItemId: Date.now() + Math.random(),
                productId: product.id,
                productName: product.name,
                productSlug: product.slug,
                imageUrl: product.imageUrl || product.images?.[0]?.imageUrl || '',
                unitPrice: product.price,
                quantity: quantity,
                modifiers: modifiers || [],
                modifiersTotal: (modifiers || []).reduce((s, m) => s + m.priceDelta * (m.quantity || 1), 0),
                specialNote: specialNote || '',
                modKey: modKey
            });
        }
        this.save(items);
        Toast.success(`${product.name} agregado al carrito`);
    },

    updateQuantity(cartItemId, newQty) {
        const items = this.getItems();
        const item = items.find(i => i.cartItemId === cartItemId);
        if (item) {
            if (newQty <= 0) {
                this.remove(cartItemId);
                return;
            }
            item.quantity = newQty;
            this.save(items);
        }
    },

    remove(cartItemId) {
        let items = this.getItems();
        items = items.filter(i => i.cartItemId !== cartItemId);
        this.save(items);
        Toast.info('Producto eliminado del carrito');
    },

    clear() {
        localStorage.removeItem(this.KEY);
        this.updateBadge();
        window.dispatchEvent(new Event('cart-updated'));
    },

    getCount() {
        return this.getItems().reduce((s, i) => s + i.quantity, 0);
    },

    getSubtotal() {
        return this.getItems().reduce((s, i) => s + (i.unitPrice + i.modifiersTotal) * i.quantity, 0);
    },

    updateBadge() {
        const badge = document.getElementById('cartBadge');
        if (!badge) return;
        const count = this.getCount();
        badge.textContent = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
    }
};

// ── Toast Notifications ───────────────────────────────────────────────────────
const Toast = {
    container: null,

    init() {
        this.container = document.getElementById('toastContainer');
    },

    show(message, type = 'info', duration = 3000) {
        if (!this.container) this.init();
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        const icons = { success: '✅', error: '❌', info: 'ℹ️', warning: '⚠️' };
        toast.innerHTML = `<span>${icons[type] || ''}</span><span>${message}</span>`;
        this.container.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100px)';
            toast.style.transition = 'all 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },

    success(msg) { this.show(msg, 'success'); },
    error(msg) { this.show(msg, 'error'); },
    info(msg) { this.show(msg, 'info'); },
    warning(msg) { this.show(msg, 'warning'); }
};

// ── Active Navigation ─────────────────────────────────────────────────────────
function setActiveNav() {
    const path = window.location.pathname.toLowerCase();
    document.querySelectorAll('.nav-desktop a, .nav-mobile-bottom .nav-item').forEach(link => {
        const href = link.getAttribute('href')?.toLowerCase();
        if (!href) return;
        if (href === '/' && path === '/') {
            link.classList.add('active');
        } else if (href !== '/' && path.startsWith(href)) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
}

// ── Format Currency ───────────────────────────────────────────────────────────
function formatMoney(amount) {
    return '$' + Number(amount).toFixed(2);
}

// ── API Fetch Helper ──────────────────────────────────────────────────────────
async function apiFetch(endpoint, options = {}) {
    const url = `${API_BASE}${endpoint}`;
    const res = await fetch(url, {
        headers: { 'Content-Type': 'application/json', ...options.headers },
        ...options
    });
    if (!res.ok) {
        const err = await res.json().catch(() => ({ mensaje: 'Error de conexión' }));
        throw new Error(err.mensaje || `Error ${res.status}`);
    }
    return res.json();
}

// ── Status Badges ─────────────────────────────────────────────────────────────
const ORDER_STATUS_TEXT = {
    'draft': 'Borrador',
    'pending_confirmation': 'Pendiente de confirmación',
    'pending_authorization': 'Pendiente de autorización',
    'confirmed': 'Confirmado',
    'preparing': 'En preparación',
    'ready': 'Listo',
    'on_the_way': 'En camino',
    'delivered': 'Entregado',
    'cancelled': 'Cancelado',
    'rejected': 'Rechazado'
};

const RESERVATION_STATUS_TEXT = {
    'pending': 'Pendiente',
    'confirmed': 'Confirmada',
    'rejected': 'Rechazada',
    'cancelled': 'Cancelada',
    'completed': 'Completada',
    'no_show': 'No asistió'
};

function getStatusBadge(status, type = 'order') {
    const texts = type === 'order' ? ORDER_STATUS_TEXT : RESERVATION_STATUS_TEXT;
    const text = texts[status] || status;
    return `<span class="badge badge-${status.replace(/_/g, '-')}">${text}</span>`;
}

// ── Init ──────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    Cart.updateBadge();
    setActiveNav();
    Toast.init();
});
