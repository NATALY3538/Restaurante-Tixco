@extends('layouts.app')

@section('title', 'Admin - Editar Platillo')

@section('content')
<div class="container fade-in" style="max-width:800px; padding-bottom:var(--sp-3xl);">
    <!-- Breadcrumb -->
    <div style="padding:var(--sp-lg) 0;">
        <a href="/admin" style="color:var(--clr-text-secondary); font-size:0.9rem;">← Volver al Panel</a>
    </div>

    <div class="page-header">
        <h1>✏️ Editar Platillo</h1>
        <p>Modifica el precio, imagen o ajusta el stock actual del inventario</p>
    </div>

    <div class="card" style="padding:var(--sp-xl);">
        <form id="editProductForm" onsubmit="saveProductChanges(event)">
            <div class="form-group">
                <label class="form-label" for="prodName">Nombre del platillo/bebida</label>
                <input type="text" id="prodName" required />
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
                    <input type="number" id="prodPrice" step="0.01" min="0" required />
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="prodDescription">Descripción</label>
                <textarea id="prodDescription" rows="3"></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="prodImage">URL de la Imagen</label>
                    <input type="url" id="prodImage" />
                </div>
                <div class="form-group">
                    <label class="form-label" for="prodPrepTime">Tiempo estimado preparación (minutos)</label>
                    <input type="number" id="prodPrepTime" min="1" />
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="prodStock">Inventario/Stock Actual</label>
                    <input type="number" id="prodStock" min="0" required />
                    <span class="form-hint">Si modificas este valor, se registrará un ajuste manual de inventario</span>
                </div>
                <div class="form-group">
                    <label class="form-label" for="prodStatus">Estado de Catálogo</label>
                    <select id="prodStatus">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
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
                <button type="submit" class="btn btn-primary" id="btnSaveProduct">💾 Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
const productId = '{{ $id }}';
let categories = [];

document.addEventListener('DOMContentLoaded', async () => {
    await loadCategories();
    loadProductData();
});

async function loadCategories() {
    try {
        categories = await apiFetch('/categories-products');
        const select = document.getElementById('prodCategory');
        select.innerHTML = '<option value="">— Selecciona categoría —</option>';
        categories.forEach(c => {
            select.innerHTML += `<option value="${c.categoryId}">${c.name}</option>`;
        });
    } catch (err) {
        Toast.error('Error al cargar categorías');
    }
}

async function loadProductData() {
    try {
        const p = await apiFetch(`/products/${productId}`);
        
        document.getElementById('prodName').value = p.name;
        document.getElementById('prodCategory').value = p.category_id;
        document.getElementById('prodPrice').value = p.price;
        document.getElementById('prodDescription').value = p.description || '';
        document.getElementById('prodImage').value = p.image_url || '';
        document.getElementById('prodPrepTime').value = p.estimated_preparation_minutes || 10;
        document.getElementById('prodStock').value = p.inventory ? p.inventory.stock : 0;
        document.getElementById('prodStatus').value = p.is_active ? "1" : "0";

        document.getElementById('chkVegetarian').checked = !!p.is_vegetarian;
        document.getElementById('chkSpicy').checked = !!p.is_spicy;
        document.getElementById('chkGlutenFree').checked = !!p.is_gluten_free;
        document.getElementById('chkFeatured').checked = !!p.is_featured;
    } catch (err) {
        Toast.error('Error al cargar los datos del platillo');
    }
}

async function saveProductChanges(e) {
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
        is_active: document.getElementById('prodStatus').value === "1",
        stock: parseInt(document.getElementById('prodStock').value)
    };

    const btn = document.getElementById('btnSaveProduct');
    btn.disabled = true;
    btn.innerHTML = 'Guardando...';

    try {
        await apiFetch(`/admin/productos/${productId}`, {
            method: 'PUT',
            body: JSON.stringify(body)
        });
        Toast.success('¡Cambios guardados con éxito!');
        setTimeout(() => { window.location.href = '/admin'; }, 1000);
    } catch (err) {
        Toast.error(err.message || 'Error al actualizar el producto.');
        btn.disabled = false;
        btn.innerHTML = '💾 Guardar Cambios';
    }
}
</script>
@endsection
