@extends('layouts.app')

@section('title', 'Pedido Confirmado')

@section('content')
<div class="container">
    <div class="page-header" style="padding:var(--sp-3xl) 0;">
        <div class="cart-empty fade-in">
            <div class="empty-icon" style="opacity:1;">✅</div>
            <h1 style="font-family:var(--font-display);font-size:2.5rem;margin-bottom:var(--sp-md);">¡Pedido Recibido!</h1>
            <p style="font-size:1.1rem;max-width:500px;margin:0 auto var(--sp-2xl);">
                Recibimos tu pedido. Te avisaremos cuando cambie de estado.
                ¡Gracias por elegir Restaurante Tixco!
            </p>
            <div style="display:flex;gap:var(--sp-md);justify-content:center;flex-wrap:wrap;">
                <a href="/mi-cuenta/pedidos" class="btn btn-primary btn-lg">📦 Ver Mis Pedidos</a>
                <a href="/menu" class="btn btn-secondary btn-lg">🍽️ Volver al Menú</a>
            </div>
        </div>
    </div>
</div>
@endsection
