@extends('layouts.app')

@section('title', 'Pedido en Mesa')

@section('content')
<div class="container">
    <!-- ═══ LOADING STATE ═══ -->
    <div id="mesaLoading" style="text-align: center; padding: var(--sp-3xl);">
        <div class="spinner" style="margin: 0 auto var(--sp-lg);"></div>
        <p style="color: var(--clr-text-secondary); font-size: 1.1rem;">Identificando mesa...</p>
    </div>

    <!-- ═══ ERROR STATE ═══ -->
    <div id="mesaError" style="display: none;">
        <div class="cart-empty" style="padding: var(--sp-3xl);">
            <div class="empty-icon">❌</div>
            <h2 style="font-size: 1.3rem; font-weight: 600; margin-bottom: var(--sp-sm);">Mesa no encontrada</h2>
            <p style="color: var(--clr-text-secondary); margin-bottom: var(--sp-xl);" id="errorMessage">
                El código QR no es válido o la mesa no está disponible.
            </p>
            <a href="/" class="btn btn-primary">🏠 Ir al Inicio</a>
        </div>
    </div>

    <!-- ═══ TABLE CONTENT ═══ -->
    <div id="mesaContent" style="display: none;">
        <!-- Table Banner -->
        <div class="card-glass fade-in" style="padding: var(--sp-xl); margin-bottom: var(--sp-2xl); text-align: center;">
            <div style="font-size: 2.5rem; margin-bottom: var(--sp-sm);">🍽️</div>
            <h1 style="font-family: var(--font-display); font-size: 1.8rem; font-weight: 700; margin-bottom: var(--sp-xs);">
                Estás ordenando desde
            </h1>
            <p style="font-size: 1.3rem; font-weight: 600; color: var(--clr-primary);" id="tableNameDisplay">Mesa</p>
            <p style="color: var(--clr-text-secondary); font-size: 0.9rem; margin-top: var(--sp-xs);" id="tableAreaDisplay"></p>
        </div>

        <!-- Search & Filter -->
        <div class="menu-toolbar slide-up">
            <div class="search-box">
                <span class="search-icon">🔍</span>
                <input type="text" id="searchInput" placeholder="Buscar platillos..." />
            </div>
        </div>

        <!-- Category Tabs -->
        <div class="category-tabs slide-up" id="categoryTabs"></div>

        <!-- Products Grid -->
        <div class="products-grid" id="productsGrid"></div>

        <!-- Empty search result -->
        <div id="noResults" style="display: none;">
            <div class="cart-empty">
                <div class="empty-icon">🔍</div>
                <p>No se encontraron platillos con ese filtro.</p>
            </div>
        </div>
    </div>
</div>

<!-- ═══ FLOATING CART BUTTON ═══ -->
<div id="floatingCart" style="position: fixed; bottom: calc(var(--nav-bottom-height) + var(--sp-lg)); right: var(--sp-lg); z-index: 900; display: none;">
    <a href="/carrito" class="btn btn-primary btn-lg" style="border-radius: var(--radius-full); box-shadow: var(--shadow-lg), var(--shadow-glow); padding: 1rem 1.5rem;">
        🛒 Ver Carrito — <span id="floatingCartTotal">$0.00</span>
    </a>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
    const token = '{{ $token }}';
    const loadingEl = document.getElementById('mesaLoading');
    const errorEl = document.getElementById('mesaError');
    const contentEl = document.getElementById('mesaContent');
    const errorMsg = document.getElementById('errorMessage');

    let allProducts = [];
    let categories = [];
    let activeCategory = 'all';

    if (!token) {
        showError('No se proporcionó un código de mesa.');
        return;
    }

    // ── Step 1: Fetch table info ──
    try {
        const table = await apiFetch(`/tables/qr/${encodeURIComponent(token)}`);

        if (!table || !table.id) {
            showError('La mesa no fue encontrada o no está activa.');
            return;
        }

        // Store table info for checkout
        sessionStorage.setItem('tixco_table_id', table.id);
        sessionStorage.setItem('tixco_table_name', table.name || `Mesa ${table.table_code || ''}`);
        sessionStorage.setItem('tixco_order_type', 'dine_in');

        // Display table info
        const tableName = table.name || `Mesa ${table.table_code || ''}`;
        document.getElementById('tableNameDisplay').textContent = tableName;
        if (table.service_area && table.service_area.name) {
            document.getElementById('tableAreaDisplay').textContent = `📍 Área: ${table.service_area.name}`;
        }

        // Step 2: Load the menu
        await loadMenu();

        loadingEl.style.display = 'none';
        contentEl.style.display = 'block';

    } catch (err) {
        showError(err.message || 'Error al identificar la mesa.');
    }

    function showError(msg) {
        loadingEl.style.display = 'none';
        errorMsg.textContent = msg;
        errorEl.style.display = 'block';
    }

    // ── Load menu ──
    async function loadMenu() {
        try {
            const data = await apiFetch('/products');
            allProducts = (data || []).filter(p => p.is_active !== false);

            // Extract categories
            const catSet = new Map();
            allProducts.forEach(p => {
                if (p.category) {
                    catSet.set(p.category.id || p.category_id, p.category);
                }
            });
            categories = Array.from(catSet.values());

            renderCategoryTabs();
            renderProducts();
        } catch (err) {
            Toast.error('Error al cargar el menú');
        }
    }

    // ── Category tabs ──
    function renderCategoryTabs() {
        const tabs = document.getElementById('categoryTabs');
        let html = `<button class="category-tab active" data-cat="all">🍽️ Todos</button>`;
        categories.forEach(cat => {
            html += `<button class="category-tab" data-cat="${cat.id}">${cat.name}</button>`;
        });
        tabs.innerHTML = html;

        tabs.querySelectorAll('.category-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                activeCategory = tab.dataset.cat;
                renderProducts();
            });
        });
    }

    // ── Render products grid ──
    function renderProducts() {
        const grid = document.getElementById('productsGrid');
        const noResults = document.getElementById('noResults');
        const search = (document.getElementById('searchInput').value || '').toLowerCase();

        let filtered = allProducts;

        if (activeCategory !== 'all') {
            filtered = filtered.filter(p => (p.category?.id || p.category_id) == activeCategory);
        }

        if (search) {
            filtered = filtered.filter(p =>
                (p.name || '').toLowerCase().includes(search) ||
                (p.description || '').toLowerCase().includes(search)
            );
        }

        if (filtered.length === 0) {
            grid.innerHTML = '';
            noResults.style.display = 'block';
            return;
        }

        noResults.style.display = 'none';
        grid.innerHTML = filtered.map(product => {
            const imgUrl = product.image_url || '';
            const imgHtml = imgUrl
                ? `<div class="card-img-wrap" onclick="location.href='/menu/detalle/${product.slug}'" style="cursor:pointer;"><img class="card-img" src="${imgUrl}" alt="${product.name}" loading="lazy" /></div>`
                : `<div class="card-img-wrap" onclick="location.href='/menu/detalle/${product.slug}'" style="cursor:pointer; height:200px; background: var(--clr-surface-2); display:flex; align-items:center; justify-content:center; font-size:3rem;">🍽️</div>`;

            const tags = [];
            if (product.is_vegetarian) tags.push('<span class="tag tag-vegetarian">Vegetariano</span>');
            if (product.is_spicy) tags.push('<span class="tag tag-spicy">Picante</span>');

            return `
                <div class="product-card fade-in">
                    ${imgHtml}
                    <div class="card-body">
                        <h3 class="card-title" onclick="location.href='/menu/detalle/${product.slug}'" style="cursor:pointer;">${product.name}</h3>
                        <p class="card-desc">${product.description || ''}</p>
                        ${tags.length ? `<div style="display:flex;gap:4px;margin-top:var(--sp-sm);">${tags.join('')}</div>` : ''}
                        <div class="card-meta">
                            <span class="card-price">${formatMoney(product.price)}</span>
                            <button class="btn btn-sm btn-primary" onclick='addToCartMesa(${JSON.stringify({ id: product.id, categoryId: product.category_id, name: product.name, slug: product.slug, price: product.price, imageUrl: imgUrl })})'>
                                🛒 Agregar
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    // ── Search input ──
    document.getElementById('searchInput').addEventListener('input', () => {
        renderProducts();
    });

    // ── Add to cart for dine-in ──
    window.addToCartMesa = function(product) {
        Cart.add(product, 1, [], '');
        updateFloatingCart();
    };

    // ── Floating cart button ──
    function updateFloatingCart() {
        const fab = document.getElementById('floatingCart');
        const total = document.getElementById('floatingCartTotal');
        const items = Cart.getItems();

        if (items.length > 0) {
            fab.style.display = 'block';
            let subtotal = 0;
            let mods = 0;
            items.forEach(i => {
                subtotal += i.unitPrice * i.quantity;
                mods += (i.modifiersTotal || 0) * i.quantity;
            });
            total.textContent = formatMoney(subtotal + mods);
        } else {
            fab.style.display = 'none';
        }
    }

    window.addEventListener('cart-updated', updateFloatingCart);
    updateFloatingCart();
});
</script>
@endsection
