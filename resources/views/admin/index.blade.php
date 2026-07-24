@extends('layouts.app')

@section('title', 'Admin - Panel de Control')

@section('content')
<div class="container fade-in">
    <!-- ═══ PAGE HEADER ═══ -->
    <div class="page-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:var(--sp-md);">
        <div>
            <h1>⚙️ Panel del Administrador</h1>
            <p>Gestiona el catálogo de productos, precios, imágenes e inventario</p>
        </div>
        <div style="display:flex; gap:var(--sp-sm);">
            <a href="/admin/productos/crear" class="btn btn-primary">➕ Nuevo Platillo</a>
            <a href="/admin/inventario" class="btn btn-outline">📋 Historial Inventario</a>
        </div>
    </div>

    <!-- ═══ ADMIN TOOLBAR ═══ -->
    <div class="menu-toolbar" style="margin-bottom:var(--sp-xl);">
        <div class="search-box">
            <span class="search-icon">🔍</span>
            <input type="text" id="adminSearchInput" placeholder="Buscar platillo por nombre..." autocomplete="off">
        </div>
        <div class="filter-chips">
            <button class="chip" id="btnFilterAll" onclick="filterAdminProducts('all')" class="active">Todos</button>
            <button class="chip" id="btnFilterLowStock" onclick="filterAdminProducts('low_stock')">⚠️ Stock Bajo</button>
            <button class="chip" id="btnFilterInactive" onclick="filterAdminProducts('inactive')">🚫 Inactivos</button>
        </div>
    </div>

    <!-- ═══ PRODUCTS LIST CARD ═══ -->
    <div class="card" style="padding:var(--sp-xl); overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; text-align:left; color:var(--clr-text);">
            <thead>
                <tr style="border-bottom:2px solid var(--clr-border); font-family:var(--font-display); font-size:0.95rem;">
                    <th style="padding:var(--sp-md) var(--sp-sm);">Platillo</th>
                    <th style="padding:var(--sp-md) var(--sp-sm);">Categoría</th>
                    <th style="padding:var(--sp-md) var(--sp-sm);">Precio</th>
                    <th style="padding:var(--sp-md) var(--sp-sm);">Stock Actual</th>
                    <th style="padding:var(--sp-md) var(--sp-sm);">Mínimo</th>
                    <th style="padding:var(--sp-md) var(--sp-sm);">Estado</th>
                    <th style="padding:var(--sp-md) var(--sp-sm); text-align:right;">Acciones</th>
                </tr>
            </thead>
            <tbody id="adminProductsTable">
                <tr>
                    <td colspan="7" style="text-align:center; padding:var(--sp-2xl);">
                        <div class="spinner" style="margin:0 auto;"></div>
                        <p style="margin-top:var(--sp-md); color:var(--clr-text-secondary);">Cargando catálogo...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
let adminProducts = [];
let currentFilter = 'all';
let searchText = '';

document.addEventListener('DOMContentLoaded', () => {
    loadAdminCatalog();

    let searchTimeout;
    document.getElementById('adminSearchInput').addEventListener('input', e => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchText = e.target.value.trim().toLowerCase();
            renderTable();
        }, 200);
    });
});

async function loadAdminCatalog() {
    try {
        adminProducts = await apiFetch('/admin/productos');
        renderTable();
    } catch (err) {
        document.getElementById('adminProductsTable').innerHTML = `
            <tr>
                <td colspan="7" style="text-align:center; padding:var(--sp-2xl); color:var(--clr-danger);">
                    ⚠️ Error al cargar catálogo de administrador.
                </td>
            </tr>`;
    }
}

function filterAdminProducts(filterType) {
    currentFilter = filterType;
    document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
    
    if (filterType === 'all') document.getElementById('btnFilterAll').classList.add('active');
    if (filterType === 'low_stock') document.getElementById('btnFilterLowStock').classList.add('active');
    if (filterType === 'inactive') document.getElementById('btnFilterInactive').classList.add('active');
    
    renderTable();
}

function renderTable() {
    let filtered = [...adminProducts];

    // Filter by type
    if (currentFilter === 'inactive') {
        filtered = filtered.filter(p => !p.is_active);
    } else {
        // Default only active, unless looking for inactive
        filtered = filtered.filter(p => p.is_active);
    }

    if (currentFilter === 'low_stock') {
        filtered = filtered.filter(p => {
            const stock = p.inventory ? p.inventory.stock : 0;
            const min = p.inventory ? p.inventory.min_stock : 5;
            return stock <= min;
        });
    }

    // Filter by text search
    if (searchText) {
        filtered = filtered.filter(p => 
            p.name.toLowerCase().includes(searchText) ||
            (p.category && p.category.name.toLowerCase().includes(searchText))
        );
    }

    const tbody = document.getElementById('adminProductsTable');
    if (filtered.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align:center; padding:var(--sp-xl); color:var(--clr-text-muted);">
                    No hay productos que coincidan con el filtro.
                </td>
            </tr>`;
        return;
    }

    tbody.innerHTML = filtered.map(p => {
        const stock = p.inventory ? p.inventory.stock : 0;
        const min = p.inventory ? p.inventory.min_stock : 5;
        const isLow = stock <= min;

        const stockHtml = isLow 
            ? `<span style="color:var(--clr-danger); font-weight:700;">⚠️ ${stock}</span>` 
            : `<span style="color:var(--clr-success); font-weight:600;">${stock}</span>`;

        const statusHtml = p.is_active 
            ? '<span class="tag tag-vegetarian" style="font-size:0.75rem; padding:2px 8px;">Activo</span>' 
            : '<span class="tag tag-spicy" style="font-size:0.75rem; padding:2px 8px;">Inactivo</span>';

        return `
            <tr style="border-bottom:1px solid var(--clr-border); vertical-align:middle; transition:background 0.2s;">
                <td style="padding:var(--sp-md) var(--sp-sm); display:flex; align-items:center; gap:var(--sp-md);">
                    <img src="${p.image_url || '/img/placeholder.jpg'}" alt="${p.name}" style="width:40px; height:40px; border-radius:var(--radius-sm); object-fit:cover;" onerror="this.src='/img/placeholder.jpg'">
                    <div>
                        <div style="font-weight:600; color:var(--clr-text);">${p.name}</div>
                        <div style="font-size:0.75rem; color:var(--clr-text-muted);">${p.slug}</div>
                    </div>
                </td>
                <td style="padding:var(--sp-md) var(--sp-sm); color:var(--clr-text-secondary);">${p.category ? p.category.name : '—'}</td>
                <td style="padding:var(--sp-md) var(--sp-sm); font-weight:600; color:var(--clr-text);">${formatMoney(p.price)}</td>
                <td style="padding:var(--sp-md) var(--sp-sm);">${stockHtml}</td>
                <td style="padding:var(--sp-md) var(--sp-sm); color:var(--clr-text-muted);">${min}</td>
                <td style="padding:var(--sp-md) var(--sp-sm);">${statusHtml}</td>
                <td style="padding:var(--sp-md) var(--sp-sm); text-align:right;">
                    <a href="/admin/productos/editar/${p.id}" class="btn btn-sm btn-outline" style="margin-right:var(--sp-xs);">✏️ Editar</a>
                    ${p.is_active ? `<button class="btn btn-sm btn-danger" onclick="deleteProduct(${p.id})">🗑️ Baja</button>` : ''}
                </td>
            </tr>
        `;
    }).join('');
}

async function deleteProduct(id) {
    if (!confirm('¿Estás seguro de que deseas dar de baja este platillo? Se marcará como inactivo.')) return;

    try {
        await apiFetch(`/admin/productos/${id}`, {
            method: 'DELETE'
        });
        Toast.info('Platillo dado de baja correctamente.');
        loadAdminCatalog();
    } catch (err) {
        Toast.error('Error al dar de baja el platillo.');
    }
}
</script>
@endsection
