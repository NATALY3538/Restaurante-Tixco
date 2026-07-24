@extends('layouts.app')

@section('title', 'Menú')

@section('content')
<div class="container">
    <!-- ═══ PAGE HEADER ═══ -->
    <div class="page-header slide-up">
        <h1>🍽️ Nuestro Menú</h1>
        <p>Explora nuestra carta con platillos auténticos de la cocina mexicana, preparados con ingredientes frescos del día.</p>
    </div>

    <!-- ═══ TOOLBAR ═══ -->
    <div class="menu-toolbar fade-in">
        <div class="search-box">
            <span class="search-icon">🔍</span>
            <input type="text" id="searchInput" placeholder="Buscar platillo..." autocomplete="off">
        </div>
        <div class="filter-chips">
            <button class="chip" data-filter="vegetarian">🥬 Vegetariano</button>
            <button class="chip" data-filter="spicy">🌶️ Picante</button>
            <button class="chip" data-filter="glutenFree">🌾 Sin Gluten</button>
            <button class="chip" data-filter="featured">⭐ Destacados</button>
        </div>
    </div>

    <!-- ═══ CATEGORY TABS ═══ -->
    <div class="category-tabs" id="categoryTabs">
        <div class="category-tab skeleton" style="width:80px;height:38px;"></div>
        <div class="category-tab skeleton" style="width:100px;height:38px;"></div>
        <div class="category-tab skeleton" style="width:90px;height:38px;"></div>
    </div>

    <!-- ═══ PRODUCTS GRID ═══ -->
    <div class="products-grid" id="productsGrid">
        <div class="product-card"><div class="skeleton" style="height:200px;"></div><div class="card-body"><div class="skeleton" style="height:20px;width:60%;margin-bottom:8px;"></div><div class="skeleton" style="height:14px;width:90%;"></div></div></div>
        <div class="product-card"><div class="skeleton" style="height:200px;"></div><div class="card-body"><div class="skeleton" style="height:20px;width:60%;margin-bottom:8px;"></div><div class="skeleton" style="height:14px;width:90%;"></div></div></div>
        <div class="product-card"><div class="skeleton" style="height:200px;"></div><div class="card-body"><div class="skeleton" style="height:20px;width:60%;margin-bottom:8px;"></div><div class="skeleton" style="height:14px;width:90%;"></div></div></div>
    </div>

    <!-- ═══ EMPTY STATE ═══ -->
    <div id="emptyState" style="display:none;text-align:center;padding:var(--sp-3xl);">
        <div style="font-size:4rem;margin-bottom:var(--sp-md);opacity:0.3;">🔍</div>
        <p style="color:var(--clr-text-secondary);font-size:1.1rem;">No se encontraron platillos con los filtros seleccionados.</p>
        <button class="btn btn-secondary" style="margin-top:var(--sp-lg);" onclick="clearAllFilters()">Limpiar filtros</button>
    </div>
</div>
@endsection

@section('scripts')
<script>
let allCategories = [];
let allProducts = [];
let activeCategory = 'all';
let activeFilters = new Set();
let searchTerm = '';

(async () => {
    try {
        const data = await apiFetch('/categories-products');
        allCategories = data || [];

        // Flatten products with their category info
        allProducts = [];
        allCategories.forEach(cat => {
            (cat.products || []).forEach(p => {
                if (p.isActive) {
                    p._categoryId = cat.categoryId;
                    p._categoryName = cat.name;
                    allProducts.push(p);
                }
            });
        });

        renderCategoryTabs();
        renderProducts();
    } catch (err) {
        document.getElementById('productsGrid').innerHTML = `
            <div style="grid-column:1/-1;text-align:center;padding:var(--sp-3xl);color:var(--clr-text-secondary);">
                <div style="font-size:3rem;margin-bottom:var(--sp-md);">⚠️</div>
                <p>Error al cargar el menú. Verifica tu conexión e intenta de nuevo.</p>
            </div>`;
        console.error('Error loading menu:', err);
    }
})();

function renderCategoryTabs() {
    const tabs = document.getElementById('categoryTabs');
    let html = `<button class="category-tab active" onclick="filterByCategory(this, 'all')">🍴 Todos</button>`;
    allCategories.forEach(cat => {
        html += `<button class="category-tab" onclick="filterByCategory(this, '${cat.categoryId}')">${cat.name}</button>`;
    });
    tabs.innerHTML = html;
}

function filterByCategory(element, catId) {
    activeCategory = catId;
    document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
    if (element) {
        element.classList.add('active');
    } else {
        const target = Array.from(document.querySelectorAll('.category-tab'))
            .find(t => t.getAttribute('onclick')?.includes(`'${catId}'`) || (catId === 'all' && t.getAttribute('onclick')?.includes('\'all\'')));
        if (target) target.classList.add('active');
    }
    renderProducts();
}

function renderProducts() {
    let filtered = [...allProducts];

    // Filter by category
    if (activeCategory !== 'all') {
        filtered = filtered.filter(p => String(p._categoryId) === String(activeCategory));
    }

    // Filter by search
    if (searchTerm) {
        const q = searchTerm.toLowerCase();
        filtered = filtered.filter(p =>
            (p.name || '').toLowerCase().includes(q) ||
            (p.description || '').toLowerCase().includes(q)
        );
    }

    // Filter by chips
    if (activeFilters.has('vegetarian')) filtered = filtered.filter(p => p.isVegetarian);
    if (activeFilters.has('spicy')) filtered = filtered.filter(p => p.isSpicy);
    if (activeFilters.has('glutenFree')) filtered = filtered.filter(p => p.isGlutenFree);
    if (activeFilters.has('featured')) filtered = filtered.filter(p => p.isFeatured);

    const grid = document.getElementById('productsGrid');
    const empty = document.getElementById('emptyState');

    if (filtered.length === 0) {
        grid.style.display = 'none';
        empty.style.display = 'block';
        return;
    }

    grid.style.display = '';
    empty.style.display = 'none';

    grid.innerHTML = filtered.map(p => {
        let tags = '';
        if (p.isVegetarian) tags += '<span class="tag tag-vegetarian">🥬 Vegetariano</span>';
        if (p.isSpicy) tags += '<span class="tag tag-spicy">🌶️ Picante</span>';
        if (p.isGlutenFree) tags += '<span class="tag tag-gluten-free">🌾 Sin Gluten</span>';
        if (p.isFeatured) tags += '<span class="tag tag-featured">⭐ Destacado</span>';

        return `
        <div class="product-card" onclick="location.href='/menu/detalle/${p.slug}'" style="cursor:pointer;">
            <div class="card-img-wrap">
                <img class="card-img" src="${p.imageUrl || '/img/placeholder.jpg'}" alt="${p.name}" onerror="this.src='/img/placeholder.jpg'">
                <div class="card-tags">${tags}</div>
            </div>
            <div class="card-body">
                <div class="card-title">${p.name}</div>
                <div class="card-desc">${p.description || ''}</div>
                <div class="card-meta">
                    <span class="card-price">${formatMoney(p.price)}</span>
                    <span class="card-time">⏱️ ${p.estimatedPreparationMinutes || '?'} min</span>
                </div>
            </div>
        </div>`;
    }).join('');
}

let searchTimeout;
document.getElementById('searchInput').addEventListener('input', e => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        searchTerm = e.target.value.trim();
        renderProducts();
    }, 250);
});

document.querySelectorAll('.chip[data-filter]').forEach(chip => {
    chip.addEventListener('click', () => {
        const f = chip.dataset.filter;
        if (activeFilters.has(f)) {
            activeFilters.delete(f);
            chip.classList.remove('active');
        } else {
            activeFilters.add(f);
            chip.classList.add('active');
        }
        renderProducts();
    });
});

function clearAllFilters() {
    activeCategory = 'all';
    activeFilters.clear();
    searchTerm = '';
    document.getElementById('searchInput').value = '';
    document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
    document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
    document.querySelector('.category-tab')?.classList.add('active');
    renderProducts();
}
</script>
@endsection
