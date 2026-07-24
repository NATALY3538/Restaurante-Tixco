@extends('layouts.app')

@section('title', 'Detalle del Platillo')

@section('content')
<div class="container">
    <!-- ═══ BREADCRUMB ═══ -->
    <div style="padding:var(--sp-lg) 0;">
        <a href="/menu" style="color:var(--clr-text-secondary);font-size:0.9rem;">← Volver al menú</a>
    </div>

    <!-- ═══ LOADING STATE ═══ -->
    <div id="loadingState" class="product-detail">
        <div class="product-gallery">
            <div class="skeleton" style="width:100%;aspect-ratio:4/3;border-radius:var(--radius-lg);"></div>
        </div>
        <div class="product-info">
            <div class="skeleton" style="height:36px;width:70%;margin-bottom:var(--sp-md);"></div>
            <div class="skeleton" style="height:28px;width:30%;margin-bottom:var(--sp-lg);"></div>
            <div class="skeleton" style="height:16px;width:100%;margin-bottom:8px;"></div>
            <div class="skeleton" style="height:16px;width:85%;margin-bottom:8px;"></div>
            <div class="skeleton" style="height:16px;width:60%;"></div>
        </div>
    </div>

    <!-- ═══ PRODUCT DETAIL ═══ -->
    <div class="product-detail fade-in" id="productDetail" style="display:none;">
        <!-- LEFT: Gallery -->
        <div class="product-gallery">
            <img class="main-image" id="mainImage" src="/img/placeholder.jpg" alt="Producto">
            <div class="thumbs" id="thumbsRow"></div>
        </div>

        <!-- RIGHT: Info -->
        <div class="product-info">
            <h1 id="productName"></h1>
            <div class="product-price" id="productPrice"></div>
            <p class="product-desc" id="productDesc"></p>

            <!-- Dietary badges -->
            <div class="product-badges" id="productBadges"></div>

            <!-- Modifier groups -->
            <div id="modifierGroups"></div>

            <!-- Special note -->
            <div class="form-group" style="margin-top:var(--sp-lg);">
                <label class="form-label">📝 Nota especial (opcional)</label>
                <textarea id="specialNote" rows="2" placeholder="Ej: Sin cebolla, poco picante..."></textarea>
            </div>

            <!-- Quantity selector -->
            <div style="margin-top:var(--sp-lg);">
                <label class="form-label">Cantidad</label>
                <div class="qty-selector">
                    <button type="button" onclick="changeQty(-1)">−</button>
                    <span class="qty-value" id="qtyValue">1</span>
                    <button type="button" onclick="changeQty(1)">+</button>
                </div>
            </div>

            <!-- Add to cart -->
            <div class="add-to-cart-section">
                <span class="total-preview" id="totalPreview">$0.00</span>
                <button class="btn btn-primary btn-lg btn-block" id="btnAddToCart" onclick="addToCart()">
                    🛒 Agregar al Carrito
                </button>
            </div>
        </div>
    </div>

    <!-- ═══ ERROR STATE ═══ -->
    <div id="errorState" style="display:none;text-align:center;padding:var(--sp-3xl);">
        <div style="font-size:4rem;margin-bottom:var(--sp-lg);opacity:0.3;">😕</div>
        <h2 style="margin-bottom:var(--sp-md);">Platillo no encontrado</h2>
        <p style="color:var(--clr-text-secondary);margin-bottom:var(--sp-xl);">
            No pudimos encontrar el platillo que buscas. Es posible que haya sido retirado del menú.
        </p>
        <a href="/menu" class="btn btn-primary">Ver Menú Completo</a>
    </div>
</div>
@endsection

@section('scripts')
<script>
let currentProduct = null;
let quantity = 1;
let selectedModifiers = {};
const slug = '{{ $slug }}';

(async () => {
    if (!slug) {
        showError();
        return;
    }

    try {
        const product = await apiFetch(`/products/${slug}`);
        currentProduct = product;
        renderProduct(product);
    } catch (err) {
        console.error('Error loading product:', err);
        showError();
    }
})();

function showError() {
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('errorState').style.display = 'block';
}

function renderProduct(p) {
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('productDetail').style.display = '';

    // Update page title
    document.title = `${p.name} — Restaurante Tixco`;

    // Name, price, description
    document.getElementById('productName').textContent = p.name;
    document.getElementById('productPrice').textContent = formatMoney(p.price);
    document.getElementById('productDesc').textContent = p.description || '';

    // Main image
    const mainImg = document.getElementById('mainImage');
    const images = p.images || [];
    if (images.length > 0) {
        mainImg.src = images[0].imageUrl || '/img/placeholder.jpg';
        mainImg.onerror = function() { this.src = '/img/placeholder.jpg'; };
    } else if (p.imageUrl) {
        mainImg.src = p.imageUrl;
        mainImg.onerror = function() { this.src = '/img/placeholder.jpg'; };
    }
    mainImg.alt = p.name;

    // Thumbnails
    const thumbsRow = document.getElementById('thumbsRow');
    if (images.length > 1) {
        thumbsRow.innerHTML = images.map((img, i) => `
            <img class="thumb ${i === 0 ? 'active' : ''}"
                 src="${img.imageUrl}"
                 alt="${p.name}"
                 onclick="switchImage(this, '${img.imageUrl}')"
                 onerror="this.style.display='none'">
        `).join('');
    }

    // Badges
    const badges = [];
    if (p.isVegetarian) badges.push('<span class="tag tag-vegetarian">🥬 Vegetariano</span>');
    if (p.isSpicy) badges.push('<span class="tag tag-spicy">🌶️ Picante</span>');
    if (p.isGlutenFree) badges.push('<span class="tag tag-gluten-free">🌾 Sin Gluten</span>');
    if (p.isFeatured) badges.push('<span class="tag tag-featured">⭐ Destacado</span>');
    if (p.estimatedPreparationMinutes) {
        badges.push(`<span class="badge" style="background:var(--clr-surface-2);">⏱️ ${p.estimatedPreparationMinutes} min</span>`);
    }
    document.getElementById('productBadges').innerHTML = badges.join('');

    // Modifier groups
    const modGroups = p.modifierGroups || [];
    const mgContainer = document.getElementById('modifierGroups');
    if (modGroups.length > 0) {
        mgContainer.innerHTML = modGroups.map(mg => {
            const isRadio = mg.maxSelection === 1;
            const inputType = isRadio ? 'radio' : 'checkbox';
            const required = mg.minSelection > 0 ? ' (Obligatorio)' : ' (Opcional)';
            const hint = `Selecciona ${mg.minSelection === mg.maxSelection ? mg.minSelection : `de ${mg.minSelection} a ${mg.maxSelection}`}`;

            return `
            <div class="modifier-group" data-group-id="${mg.id}" data-min="${mg.minSelection}" data-max="${mg.maxSelection}">
                <h3>${mg.name}${required}</h3>
                <div class="mg-hint">${hint}</div>
                ${(mg.modifiers || []).map(mod => `
                    <div class="modifier-option">
                        <label>
                            <input type="${inputType}"
                                   name="mg_${mg.id}"
                                   value="${mod.id}"
                                   data-price="${mod.priceDelta || 0}"
                                   data-name="${mod.name}"
                                   onchange="onModifierChange(this)">
                            <span>${mod.name}</span>
                        </label>
                        <span class="mod-price">${mod.priceDelta > 0 ? '+' + formatMoney(mod.priceDelta) : mod.priceDelta < 0 ? formatMoney(mod.priceDelta) : 'Incluido'}</span>
                    </div>
                `).join('')}
            </div>`;
        }).join('');
    }

    updateTotal();
}

function switchImage(thumbEl, url) {
    document.getElementById('mainImage').src = url;
    document.querySelectorAll('.thumb').forEach(t => t.classList.remove('active'));
    thumbEl.classList.add('active');
}

function changeQty(delta) {
    quantity = Math.max(1, quantity + delta);
    document.getElementById('qtyValue').textContent = quantity;
    updateTotal();
}

function onModifierChange(input) {
    const group = input.closest('.modifier-group');
    const groupId = group.dataset.groupId;
    const maxSel = parseInt(group.dataset.max);
    const isCheckbox = input.type === 'checkbox';

    if (isCheckbox && input.checked) {
        // Enforce max selection for checkboxes
        const checked = group.querySelectorAll('input[type="checkbox"]:checked');
        if (checked.length > maxSel) {
            input.checked = false;
            Toast.warning(`Máximo ${maxSel} selección(es) en este grupo.`);
            return;
        }
    }

    // Collect selected modifiers for this group
    const selected = [];
    group.querySelectorAll('input:checked').forEach(inp => {
        selected.push({
            modifierId: parseInt(inp.value),
            name: inp.dataset.name,
            priceDelta: parseFloat(inp.dataset.price),
            quantity: 1
        });
    });
    selectedModifiers[groupId] = selected;

    updateTotal();
}

function updateTotal() {
    if (!currentProduct) return;
    let modTotal = 0;
    Object.values(selectedModifiers).forEach(mods => {
        mods.forEach(m => { modTotal += m.priceDelta * (m.quantity || 1); });
    });
    const total = (currentProduct.price + modTotal) * quantity;
    document.getElementById('totalPreview').textContent = formatMoney(total);
}

function addToCart() {
    if (!currentProduct) return;

    // Validate required modifier groups
    const groups = document.querySelectorAll('.modifier-group');
    for (const group of groups) {
        const minSel = parseInt(group.dataset.min);
        const groupId = group.dataset.groupId;
        const selected = selectedModifiers[groupId] || [];
        if (selected.length < minSel) {
            const groupName = group.querySelector('h3').textContent;
            Toast.error(`Selecciona al menos ${minSel} opción(es) en "${groupName}".`);
            group.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
    }

    // Flatten modifiers
    const allMods = [];
    Object.values(selectedModifiers).forEach(mods => allMods.push(...mods));

    const note = document.getElementById('specialNote').value.trim();

    Cart.add(currentProduct, quantity, allMods, note);
}
</script>
@endsection
