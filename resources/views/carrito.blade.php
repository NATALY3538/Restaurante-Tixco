@extends('layouts.app')

@section('title', 'Carrito')

@section('content')
<div class="container fade-in">
    <div class="page-header">
        <h1>🛒 Tu Carrito</h1>
        <p>Revisa y personaliza tu pedido antes de proceder al checkout.</p>
    </div>

    <!-- ═══ LAYOUT CON ELEMENTOS ═══ -->
    <div class="cart-layout" id="cartLayout" style="display:none;">
        <!-- Left: Items list -->
        <div class="cart-items-list" id="cartItems"></div>

        <!-- Right: Summary & Checkout Button -->
        <div class="cart-summary">
            <h3>Resumen del Pedido</h3>
            <div class="summary-row">
                <span>Subtotal platillos</span>
                <span id="summarySubtotal">$0.00</span>
            </div>
            <div class="summary-row">
                <span>Complementos</span>
                <span id="summaryMods">$0.00</span>
            </div>
            <div class="summary-row total">
                <span>Total estimado</span>
                <span id="summaryTotal">$0.00</span>
            </div>
            <a href="/checkout" class="btn btn-primary btn-block btn-lg" style="margin-top:var(--sp-xl);">
                💳 Continuar al Pago
            </a>
            <a href="/menu" class="btn btn-secondary btn-block" style="margin-top:var(--sp-md);">
                🍽️ Seguir Comprando
            </a>
        </div>
    </div>

    <!-- ═══ EMPTY STATE ═══ -->
    <div class="cart-empty" id="cartEmpty" style="display:none;text-align:center;padding:var(--sp-3xl);">
        <div class="empty-icon">🛒</div>
        <h2>Tu carrito está vacío</h2>
        <p style="color:var(--clr-text-secondary);margin-bottom:var(--sp-xl);">
            Aún no has agregado ningún platillo a tu pedido. ¡Explora nuestro menú y encuentra tus favoritos!
        </p>
        <a href="/menu" class="btn btn-primary btn-lg">Ver Menú 🍽️</a>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    renderCart();
    window.addEventListener('cart-updated', renderCart);
});

function renderCart() {
    const items = Cart.getItems();
    const cartItemsEl = document.getElementById('cartItems');
    const cartLayout = document.getElementById('cartLayout');
    const cartEmpty = document.getElementById('cartEmpty');

    if (items.length === 0) {
        cartLayout.style.display = 'none';
        cartEmpty.style.display = 'block';
        return;
    }

    cartLayout.style.display = '';
    cartEmpty.style.display = 'none';

    let html = '';
    let subtotal = 0;
    let modsTotal = 0;

    items.forEach(item => {
        const itemSubtotal = item.unitPrice * item.quantity;
        const itemMods = (item.modifiersTotal || 0) * item.quantity;
        subtotal += itemSubtotal;
        modsTotal += itemMods;

        const modsHtml = item.modifiers && item.modifiers.length > 0
            ? `<div class="item-mods">` +
              item.modifiers.map(m => `<span>+ ${m.name} (${formatMoney(m.priceDelta)})</span>`).join('<br>') +
              `</div>`
            : '';

        const noteHtml = item.specialNote
            ? `<div class="item-note">📝 ${item.specialNote}</div>`
            : '';

        const imgSrc = item.imageUrl || '/img/placeholder.jpg';

        html += `
        <div class="cart-item fade-in">
            <img src="${imgSrc}" alt="${item.productName}" class="item-img" onerror="this.src='/img/placeholder.jpg'">
            <div class="item-info">
                <div class="item-name">${item.productName}</div>
                ${modsHtml}
                ${noteHtml}
                <div class="item-actions">
                    <div class="qty-selector">
                        <button onclick="Cart.updateQuantity(${item.cartItemId}, ${item.quantity - 1})">−</button>
                        <span class="qty-value">${item.quantity}</span>
                        <button onclick="Cart.updateQuantity(${item.cartItemId}, ${item.quantity + 1})">+</button>
                    </div>
                    <span class="item-price">${formatMoney(itemSubtotal + itemMods)}</span>
                    <button class="btn btn-sm btn-danger" onclick="Cart.remove(${item.cartItemId})">🗑️</button>
                </div>
            </div>
        </div>`;
    });

    cartItemsEl.innerHTML = html;

    const total = subtotal + modsTotal;
    document.getElementById('summarySubtotal').textContent = formatMoney(subtotal);
    document.getElementById('summaryMods').textContent = formatMoney(modsTotal);
    document.getElementById('summaryTotal').textContent = formatMoney(total);
}
</script>
@endsection
