@extends('layouts.app')

@section('title', 'Admin - Nuevo Platillo')

@section('content')
<div class="container fade-in" style="max-width:800px; padding-bottom:var(--sp-3xl);">
    <!-- Breadcrumb -->
    <div style="padding:var(--sp-lg) 0;">
        <a href="/admin" style="color:var(--clr-text-secondary); font-size:0.9rem;">← Volver al Panel</a>
    </div>

    <div class="page-header">
        <h1>➕ Nuevo Platillo</h1>
        <p>Agrega un nuevo platillo o bebida al catálogo de Restaurante Tixco</p>
    </div>

    <div class="card" style="padding:var(--sp-xl);">
        <form id="createProductForm" onsubmit="saveNewProduct(event)">
            <div class="form-group">
                <label class="form-label" for="prodName">Nombre del platillo/bebida</label>
                <input type="text" id="prodName" placeholder="Ej: Enchiladas Verdes" required />
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="prodCategory">Categoría</label>
                    <select id="prodCategory" required>
                        <option value="">Cargando categorías...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="prodPrice">Precio (MXN)</label>
                    <input type="number" id="prodPrice" step="0.01" min="0" placeholder="Ej: 110.00" required />
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="prodDescription">Descripción</label>
                <textarea id="prodDescription" rows="3" placeholder="Ingredientes, preparación, detalles del platillo..."></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="prodImage">URL de la Imagen</label>
                    <input type="url" id="prodImage" placeholder="https://ejemplo.com/imagen.jpg" />
                </div>
                <div class="form-group">
                    <label class="form-label" for="prodPrepTime">Tiempo estimado preparación (minutos)</label>
                    <input type="number" id="prodPrepTime" min="1" value="10" />
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="prodStock">Inventario/Stock Inicial</label>
                    <input type="number" id="prodStock" min="0" value="50" required />
                    <span class="form-hint">Se creará un registro de inventario con este stock</span>
                </div>
            </div>

            <!-- Badges flags -->
            <div style="margin-top:var(--sp-lg); margin-bottom:var(--sp-xl);">
                <label class="form-label" style="margin-bottom:var(--sp-sm);">Etiquetas de Dieta / Sabor</label>
                <div style="display:flex; gap:var(--sp-md); flex-wrap:wrap;">
                    <label class="modifier-option" style="padding:var(--sp-sm) var(--sp-md); border-radius:var(--radius-md); border:1px solid var(--clr-border);">
                        <input type="checkbox" id="chkVegetarian">
                        <span style="margin-left:var(--sp-xs);">🥬 Vegetariano</span>
                    </label>
                    <label class="modifier-option" style="padding:var(--sp-sm) var(--sp-md); border-radius:var(--radius-md); border:1px solid var(--clr-border);">
                        <input type="checkbox" id="chkSpicy">
                        <span style="margin-left:var(--sp-xs);">🌶️ Picante</span>
                    </label>
                    <label class="modifier-option" style="padding:var(--sp-sm) var(--sp-md); border-radius:var(--radius-md); border:1px solid var(--clr-border);">
                        <input type="checkbox" id="chkGlutenFree">
                        <span style="margin-left:var(--sp-xs);">🌾 Sin Gluten</span>
                    </label>
                    <label class="modifier-option" style="padding:var(--sp-sm) var(--sp-md); border-radius:var(--radius-md); border:1px solid var(--clr-border);">
                        <input type="checkbox" id="chkFeatured">
                        <span style="margin-left:var(--sp-xs);">⭐ Destacado</span>
                    </label>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:var(--sp-md);">
                <a href="/admin" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary" id="btnSaveProduct">💾 Registrar Platillo</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    loadCategories();
});

async function loadCategories() {
    try {
        const cats = await apiFetch('/categories-products');
        const select = document.getElementById('prodCategory');
        select.innerHTML = '<option value="">— Selecciona categoría —</option>';
        cats.forEach(c => {
            select.innerHTML += `<option value="${c.categoryId}">${c.name}</option>`;
        });
    } catch (err) {
        Toast.error('Error al cargar categorías');
    }
}

async function saveNewProduct(e) {
    e.preventDefault();

    const body = {
        name: document.getElementById('prodName').value.trim(),
        category_id: parseInt(document.getElementById('prodCategory').value),
        price: parseFloat(document.getElementById('prodPrice').value),
        description: document.getElementById('prodDescription').value.trim(),
        image_url: document.getElementById('prodImage').value.trim() || null,
        estimated_preparation_minutes: parseInt(document.getElementById('prodPrepTime').value) || 10,
        is_vegetarian: document.getElementById('chkVegetarian').checked,
        is_spicy: document.getElementById('chkSpicy').checked,
        is_gluten_free: document.getElementById('chkGlutenFree').checked,
        is_featured: document.getElementById('chkFeatured').checked,
        stock: parseInt(document.getElementById('prodStock').value)
    };

    const btn = document.getElementById('btnSaveProduct');
    btn.disabled = true;
    btn.innerHTML = 'Guardando...';

    try {
        await apiFetch('/admin/productos', {
            method: 'POST',
            body: JSON.stringify(body)
        });
        Toast.success('¡Platillo agregado correctamente al catálogo!');
        setTimeout(() => { window.location.href = '/admin'; }, 1000);
    } catch (err) {
        Toast.error(err.message || 'Error al guardar el producto.');
        btn.disabled = false;
        btn.innerHTML = '💾 Registrar Platillo';
    }
}
</script>
@endsection
