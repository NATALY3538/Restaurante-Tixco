@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>🧾 Checkout</h1>
        <p>Completa tu información para confirmar tu pedido</p>
    </div>

    <div class="checkout-layout">
        <!-- ═══ LEFT: CHECKOUT STEPS ═══ -->
        <div class="checkout-steps">

            <!-- Step 1: Order Type -->
            <div class="checkout-step">
                <h2><span class="step-num">1</span> Tipo de Pedido</h2>
                <div class="order-type-selector">
                    <div class="order-type-option" data-type="delivery" onclick="selectOrderType('delivery')">
                        <div class="ot-icon">🛵</div>
                        <h4>A Domicilio</h4>
                        <p>Te lo llevamos a tu puerta</p>
                    </div>
                    <div class="order-type-option" data-type="pickup" onclick="selectOrderType('pickup')">
                        <div class="ot-icon">🥡</div>
                        <h4>Para Recoger</h4>
                        <p>Recógelo en el restaurante</p>
                    </div>
                    <div class="order-type-option" data-type="dine_in" onclick="selectOrderType('dine_in')">
                        <div class="ot-icon">🍽️</div>
                        <h4>Comer Aquí</h4>
                        <p>Disfrútalo en nuestro local</p>
                    </div>
                </div>
            </div>

            <!-- Step 2: Customer Data -->
            <div class="checkout-step">
                <h2><span class="step-num">2</span> Datos del Cliente</h2>
                <div class="form-group">
                    <label class="form-label" for="customerName">Nombre completo</label>
                    <input type="text" id="customerName" placeholder="Tu nombre completo" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="customerPhone">Teléfono</label>
                        <input type="tel" id="customerPhone" placeholder="(555) 123-4567" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="customerEmail">Correo electrónico</label>
                        <input type="email" id="customerEmail" placeholder="correo@ejemplo.com">
                    </div>
                </div>
            </div>

            <!-- Step 3: Delivery Address (shown only for delivery) -->
            <div class="checkout-step" id="stepDelivery" style="display:none;">
                <h2><span class="step-num">3</span> Dirección de Entrega</h2>
                <div class="form-group">
                    <label class="form-label" for="addressLine1">Dirección</label>
                    <input type="text" id="addressLine1" placeholder="Calle y número">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="neighborhood">Colonia</label>
                        <input type="text" id="neighborhood" placeholder="Colonia">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="city">Ciudad</label>
                        <input type="text" id="city" placeholder="Ciudad">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="state">Estado</label>
                        <input type="text" id="state" placeholder="Estado">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="postalCode">Código postal</label>
                        <input type="text" id="postalCode" placeholder="12345">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="deliveryNotes">Notas de entrega</label>
                    <textarea id="deliveryNotes" rows="2" placeholder="Ej: Portón rojo, tocar timbre..."></textarea>
                </div>
            </div>

            <!-- Step 3 alt: Table Selection (shown only for dine_in) -->
            <div class="checkout-step" id="stepTable" style="display:none;">
                <h2><span class="step-num">3</span> Selecciona tu Mesa</h2>
                <div class="form-group">
                    <label class="form-label" for="tableSelect">Mesa disponible</label>
                    <select id="tableSelect">
                        <option value="">Cargando mesas...</option>
                    </select>
                </div>
            </div>

            <!-- Step 4: Payment Method -->
            <div class="checkout-step">
                <h2><span class="step-num">4</span> Método de Pago</h2>
                <div id="paymentMethods">
                    <div class="skeleton" style="height:40px;margin-bottom:0.5rem;"></div>
                    <div class="skeleton" style="height:40px;margin-bottom:0.5rem;"></div>
                </div>
            </div>

            <!-- Step 5: Special Notes -->
            <div class="checkout-step">
                <h2><span class="step-num">5</span> Notas Especiales</h2>
                <div class="form-group">
                    <textarea id="specialNotes" rows="3" placeholder="¿Alguna instrucción especial para tu pedido?"></textarea>
                </div>
            </div>

            <!-- Submit -->
            <button class="btn btn-primary btn-block btn-lg" id="btnSubmitOrder" onclick="submitOrder()">
                ✅ Confirmar Pedido
            </button>
        </div>

        <!-- ═══ RIGHT: ORDER SUMMARY ═══ -->
        <div>
            <div class="cart-summary" id="checkoutSummary">
                <h3>📋 Tu Pedido</h3>
                <div id="summaryItems"></div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span id="checkoutTotal">$0.00</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let selectedOrderType = '';
let paymentMethodsData = [];

document.addEventListener('DOMContentLoaded', () => {
    // Prefill from localStorage
    try {
        const u = JSON.parse(localStorage.getItem('tixco_user'));
        if (u) {
            document.getElementById('customerName').value = u.nombre || '';
            document.getElementById('customerPhone').value = u.telefono || '';
            document.getElementById('customerEmail').value = u.correo || '';
        }
    } catch {}

    renderCheckoutSummary();
    loadPaymentMethods();
    window.addEventListener('cart-updated', renderCheckoutSummary);
});

function selectOrderType(type) {
    selectedOrderType = type;

    document.querySelectorAll('.order-type-option').forEach(opt => {
        opt.classList.toggle('selected', opt.dataset.type === type);
    });

    const stepDelivery = document.getElementById('stepDelivery');
    const stepTable = document.getElementById('stepTable');

    stepDelivery.style.display = type === 'delivery' ? '' : 'none';
    stepTable.style.display = type === 'dine_in' ? '' : 'none';

    if (type === 'dine_in') {
        loadTables();
    }
}

async function loadTables() {
    try {
        const tables = await apiFetch('/tables');
        const select = document.getElementById('tableSelect');
        select.innerHTML = '<option value="">— Selecciona una mesa —</option>';
        tables.forEach(t => {
            select.innerHTML += `<option value="${t.id}">${t.name || 'Mesa ' + t.table_code} (${t.capacity} personas)</option>`;
        });
    } catch (err) {
        Toast.error('No se pudieron cargar las mesas');
    }
}

async function loadPaymentMethods() {
    try {
        paymentMethodsData = await apiFetch('/payment-methods');
        const container = document.getElementById('paymentMethods');
        if (paymentMethodsData.length === 0) {
            container.innerHTML = '<p style="color:var(--clr-text-muted);">No hay métodos de pago disponibles</p>';
            return;
        }
        container.innerHTML = paymentMethodsData.map((pm, i) => `
            <label class="modifier-option">
                <label>
                    <input type="radio" name="paymentMethod" value="${pm.id}" ${i === 0 ? 'checked' : ''}>
                    <span>${pm.name}</span>
                </label>
            </label>
        `).join('');
    } catch (err) {
        document.getElementById('paymentMethods').innerHTML =
            '<p style="color:var(--clr-danger);">Error al cargar métodos de pago</p>';
    }
}

function renderCheckoutSummary() {
    const items = Cart.getItems();
    const summaryItemsEl = document.getElementById('summaryItems');
    let total = 0;

    if (items.length === 0) {
        summaryItemsEl.innerHTML = '<p style="color:var(--clr-text-muted);text-align:center;padding:var(--sp-lg) 0;">Tu carrito está vacío</p>';
        document.getElementById('checkoutTotal').textContent = formatMoney(0);
        return;
    }

    let html = '';
    items.forEach(item => {
        const lineTotal = (item.unitPrice + (item.modifiersTotal || 0)) * item.quantity;
        total += lineTotal;
        html += `
        <div class="summary-row">
            <span>${item.quantity}× ${item.productName}</span>
            <span>${formatMoney(lineTotal)}</span>
        </div>`;
    });

    summaryItemsEl.innerHTML = html;
    document.getElementById('checkoutTotal').textContent = formatMoney(total);
}

async function submitOrder() {
    const items = Cart.getItems();
    if (items.length === 0) {
        Toast.error('Tu carrito está vacío');
        return;
    }
    if (!selectedOrderType) {
        Toast.error('Selecciona un tipo de pedido');
        return;
    }

    const customerName = document.getElementById('customerName').value.trim();
    const customerPhone = document.getElementById('customerPhone').value.trim();
    if (!customerName || !customerPhone) {
        Toast.error('Nombre y teléfono son obligatorios');
        return;
    }

    const selectedPayment = document.querySelector('input[name="paymentMethod"]:checked');
    if (!selectedPayment) {
        Toast.error('Selecciona un método de pago');
        return;
    }

    // Save profile to localStorage for future use
    localStorage.setItem('tixco_user', JSON.stringify({
        nombre: customerName,
        telefono: customerPhone,
        correo: document.getElementById('customerEmail').value.trim()
    }));

    const order = {
        customerName: customerName,
        customerPhone: customerPhone,
        customerEmail: document.getElementById('customerEmail').value.trim(),
        orderType: selectedOrderType,
        restaurantTableId: selectedOrderType === 'dine_in' ? (parseInt(document.getElementById('tableSelect').value) || null) : null,
        addressLine1: selectedOrderType === 'delivery' ? document.getElementById('addressLine1').value.trim() : null,
        neighborhood: selectedOrderType === 'delivery' ? document.getElementById('neighborhood').value.trim() : null,
        city: selectedOrderType === 'delivery' ? document.getElementById('city').value.trim() : null,
        state: selectedOrderType === 'delivery' ? document.getElementById('state').value.trim() : null,
        postalCode: selectedOrderType === 'delivery' ? document.getElementById('postalCode').value.trim() : null,
        deliveryNotes: selectedOrderType === 'delivery' ? document.getElementById('deliveryNotes').value.trim() : null,
        paymentMethodId: parseInt(selectedPayment.value),
        specialNotes: document.getElementById('specialNotes').value.trim()
    };

    const mappedItems = items.map(item => ({
        productId: item.productId,
        productName: item.productName,
        quantity: item.quantity,
        unitPrice: item.unitPrice,
        specialNote: item.specialNote || '',
        modifiers: (item.modifiers || []).map(m => ({
            modifierId: m.modifierId,
            modifierName: m.name,
            priceDelta: m.priceDelta,
            quantity: 1
        }))
    }));

    const body = { order, items: mappedItems };

    const btn = document.getElementById('btnSubmitOrder');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner" style="width:20px;height:20px;border-width:2px;"></span> Procesando...';

    try {
        const result = await apiFetch('/pedidos', {
            method: 'POST',
            body: JSON.stringify(body)
        });
        Cart.clear();
        Toast.success('¡Pedido realizado con éxito!');
        window.location.href = `/pedido-confirmado?id=${result.id}`;
    } catch (err) {
        Toast.error(err.message || 'Error al procesar el pedido');
        btn.disabled = false;
        btn.innerHTML = '✅ Confirmar Pedido';
    }
}
</script>
@endsection
