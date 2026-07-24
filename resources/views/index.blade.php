@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
<!-- ═══ HERO ═══ -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="container">
        <div class="hero-content slide-up">
            <h1>Sabores auténticos de <span class="highlight">México</span></h1>
            <p>
                Descubre la esencia de la cocina tradicional mexicana con ingredientes frescos,
                recetas familiares y un ambiente que te hará sentir como en casa.
            </p>
            <div class="hero-actions" style="margin-top: 2.2rem;">
                <a href="/menu" 
                   id="btnWelcomeTixco" 
                   class="inline-flex items-center justify-center gap-4 px-14 py-6 text-2xl lg:text-3xl font-extrabold text-[#2B1B17] rounded-full bg-gradient-to-r from-[#FFA24C] via-[#FF8D4D] to-[#FF6B4A] shadow-[0_0_50px_rgba(255,120,40,0.7)] hover:shadow-[0_0_70px_rgba(255,120,40,0.9)] hover:scale-108 transition-all duration-300 cursor-pointer" 
                   style="background: linear-gradient(90deg, #FFA24C 0%, #FF8D4D 50%, #FF6B4A 100%); color: #1f120e; border: none; text-decoration: none; border-radius: 9999px !important; box-shadow: 0 0 50px rgba(255,120,40,0.7); font-size: 1.75rem; padding: 1.4rem 3.5rem;">
                    <span class="text-3xl lg:text-4xl">🔥</span>
                    <span style="font-weight: 800; letter-spacing: 0.5px;">Bienvenido a Tixco</span>
                </a>
            </div>
        </div>
        <div class="hero-visual">
            <div class="hero-image-wrap">
                <div style="width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;
                            background:linear-gradient(135deg, var(--clr-primary), var(--clr-accent));
                            color:#fff;text-align:center;padding:2rem;
                            font-family:var(--font-display);">
                    <img src="/img/logo-tixco.png" alt="Logo Tixco" style="width:100px;height:100px;border-radius:24px;margin-bottom:1.5rem;box-shadow:var(--shadow-lg);object-fit:cover;">
                    <div style="font-size:2.2rem;font-weight:700;line-height:1.2;">Restaurante<br>Tixco</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══ MODALIDADES ═══ -->
<section class="container fade-in">
    <div class="modalities">
        <div class="modality-card">
            <div class="modality-icon">🍽️</div>
            <h3>En Restaurante</h3>
            <p>Disfruta de una experiencia gastronómica completa en nuestro acogedor restaurante.</p>
        </div>
        <div class="modality-card">
            <div class="modality-icon">🛵</div>
            <h3>A Domicilio</h3>
            <p>Recibe tus platillos favoritos directamente en la puerta de tu casa.</p>
        </div>
        <div class="modality-card">
            <div class="modality-icon">🥡</div>
            <h3>Para Recoger</h3>
            <p>Ordena en línea y recoge tu pedido listo en el tiempo acordado.</p>
        </div>
    </div>
</section>

<!-- ═══ PLATILLOS DESTACADOS ═══ -->
<section id="featuredSection" class="container fade-in" style="padding-bottom:var(--sp-3xl); scroll-margin-top: 80px;">
    <h2 class="section-title" style="text-align:center;">✨ Platillos Destacados</h2>
    <p class="section-subtitle" style="text-align:center;">
        Los favoritos de nuestros clientes, preparados con los mejores ingredientes.
    </p>
    <div class="products-grid" id="featuredGrid">
        <!-- Skeleton loaders while fetching -->
        <div class="product-card"><div class="skeleton" style="height:200px;"></div><div class="card-body"><div class="skeleton" style="height:20px;width:60%;margin-bottom:8px;"></div><div class="skeleton" style="height:14px;width:90%;"></div></div></div>
        <div class="product-card"><div class="skeleton" style="height:200px;"></div><div class="card-body"><div class="skeleton" style="height:20px;width:60%;margin-bottom:8px;"></div><div class="skeleton" style="height:14px;width:90%;"></div></div></div>
        <div class="product-card"><div class="skeleton" style="height:200px;"></div><div class="card-body"><div class="skeleton" style="height:20px;width:60%;margin-bottom:8px;"></div><div class="skeleton" style="height:14px;width:90%;"></div></div></div>
    </div>
</section>
@endsection

@section('scripts')
<script>
(async () => {
    const grid = document.getElementById('featuredGrid');
    try {
        const data = await apiFetch('/categories-products');

        // Flatten all products from all categories and filter featured
        const allProducts = [];
        (data || []).forEach(cat => {
            (cat.products || []).forEach(p => {
                if (p.isFeatured && p.isActive) allProducts.push(p);
            });
        });

        if (allProducts.length === 0) {
            grid.innerHTML = `
                <div style="grid-column:1/-1;text-align:center;padding:var(--sp-3xl);color:var(--clr-text-secondary);">
                    <div style="font-size:3rem;margin-bottom:var(--sp-md);">🍽️</div>
                    <p>No hay platillos destacados por el momento.</p>
                </div>`;
            return;
        }

        grid.innerHTML = allProducts.map(p => {
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

    } catch (err) {
        grid.innerHTML = `
            <div style="grid-column:1/-1;text-align:center;padding:var(--sp-3xl);color:var(--clr-text-secondary);">
                <div style="font-size:3rem;margin-bottom:var(--sp-md);">⚠️</div>
                <p>No se pudieron cargar los platillos. Intenta más tarde.</p>
            </div>`;
        console.error('Error loading featured products:', err);
    }
})();
</script>
@endsection
