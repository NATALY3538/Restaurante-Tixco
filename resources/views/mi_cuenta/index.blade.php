@extends('layouts.app')

@section('title', 'Mi Cuenta')

@section('content')
<div class="container">
    <!-- ═══ PAGE HEADER ═══ -->
    <div class="page-header">
        <h1>👤 Mi Cuenta</h1>
        <p>Administra tu perfil, direcciones y preferencias</p>
    </div>

    <!-- ═══ ACCOUNT LAYOUT ═══ -->
    <div class="account-layout">
        <!-- Sidebar -->
        <aside class="account-sidebar">
            <nav class="sidebar-nav">
                <a href="/mi-cuenta" class="active">👤 Mi Perfil</a>
                <a href="/mi-cuenta/pedidos">📦 Mis Pedidos</a>
                <a href="/mi-cuenta/reservaciones">📅 Mis Reservaciones</a>
                <a href="/mi-cuenta/direcciones">📍 Mis Direcciones</a>
            </nav>
        </aside>

        <!-- Content -->
        <section class="account-content">
            <div class="card" style="padding: var(--sp-xl);">
                <h2 style="font-size: 1.3rem; font-weight: 700; margin-bottom: var(--sp-xl);">Información Personal</h2>

                <form id="profileForm">
                    <!-- Nombre -->
                    <div class="form-group">
                        <label class="form-label" for="inputNombre">Nombre completo</label>
                        <input type="text" id="inputNombre" placeholder="Tu nombre completo" required />
                    </div>

                    <!-- Teléfono + Correo -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="inputTelefono">Teléfono</label>
                            <input type="tel" id="inputTelefono" placeholder="(555) 123-4567" required />
                            <span class="form-hint">Se usa para identificar tus pedidos y reservaciones</span>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="inputCorreo">Correo electrónico</label>
                            <input type="email" id="inputCorreo" placeholder="tu@correo.com" />
                        </div>
                    </div>

                    <!-- Guardar -->
                    <div style="margin-top: var(--sp-xl); display: flex; justify-content: flex-end;">
                        <button type="submit" class="btn btn-primary btn-lg">💾 Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('profileForm');
    const inputNombre = document.getElementById('inputNombre');
    const inputTelefono = document.getElementById('inputTelefono');
    const inputCorreo = document.getElementById('inputCorreo');

    // Load from localStorage
    try {
        const data = JSON.parse(localStorage.getItem('tixco_user')) || {};
        inputNombre.value = data.nombre || '';
        inputTelefono.value = data.telefono || '';
        inputCorreo.value = data.correo || '';
    } catch {}

    form.addEventListener('submit', (e) => {
        e.preventDefault();

        const userData = {
            nombre: inputNombre.value.trim(),
            telefono: inputTelefono.value.trim(),
            correo: inputCorreo.value.trim()
        };

        localStorage.setItem('tixco_user', JSON.stringify(userData));
        Toast.success('Perfil guardado exitosamente');
    });
});
</script>
@endsection
