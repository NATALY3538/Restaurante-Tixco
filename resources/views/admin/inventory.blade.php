@extends('layouts.app')

@section('title', 'Admin - Auditoría de Inventario')

@section('content')
<div class="container fade-in" style="padding-bottom:var(--sp-3xl);">
    <!-- Breadcrumb -->
    <div style="padding:var(--sp-lg) 0;">
        <a href="/admin" style="color:var(--clr-text-secondary); font-size:0.9rem;">← Volver al Panel</a>
    </div>

    <div class="page-header">
        <h1>📋 Historial de Movimientos de Inventario</h1>
        <p>Bitácora de auditoría detallada de compras, ventas y ajustes manuales</p>
    </div>

    <!-- ═══ HISTORIAL TABLE CARD ═══ -->
    <div class="card" style="padding:var(--sp-xl); overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; text-align:left; color:var(--clr-text);">
            <thead>
                <tr style="border-bottom:2px solid var(--clr-border); font-family:var(--font-display); font-size:0.95rem;">
                    <th style="padding:var(--sp-md) var(--sp-sm);">Fecha / Hora</th>
                    <th style="padding:var(--sp-md) var(--sp-sm);">Platillo</th>
                    <th style="padding:var(--sp-md) var(--sp-sm);">Cambio (Delta)</th>
                    <th style="padding:var(--sp-md) var(--sp-sm);">Tipo de Movimiento</th>
                    <th style="padding:var(--sp-md) var(--sp-sm);">Detalle / Notas</th>
                </tr>
            </thead>
            <tbody id="inventoryLogsTable">
                <tr>
                    <td colspan="5" style="text-align:center; padding:var(--sp-2xl);">
                        <div class="spinner" style="margin:0 auto;"></div>
                        <p style="margin-top:var(--sp-md); color:var(--clr-text-secondary);">Cargando historial...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    loadInventoryLogs();
});

const MOVEMENT_TYPES = {
    'purchase': '📥 Carga Inicial / Compra',
    'sale': '📤 Venta de Pedido',
    'adjustment': '🔧 Ajuste Manual',
    'damage': '🗑️ Pérdida / Merma'
};

async function loadInventoryLogs() {
    try {
        const logs = await apiFetch('/admin/inventario/movimientos');
        const tbody = document.getElementById('inventoryLogsTable');

        if (logs.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" style="text-align:center; padding:var(--sp-xl); color:var(--clr-text-muted);">
                        No hay movimientos registrados en la bitácora.
                    </td>
                </tr>`;
            return;
        }

        tbody.innerHTML = logs.map(log => {
            const date = new Date(log.created_at).toLocaleDateString('es-MX', {
                year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
            });

            const delta = log.quantity_delta;
            const deltaHtml = delta > 0 
                ? `<span style="color:var(--clr-success); font-weight:700;">+${delta}</span>` 
                : `<span style="color:var(--clr-danger); font-weight:700;">${delta}</span>`;

            const typeLabel = MOVEMENT_TYPES[log.type] || log.type;

            return `
                <tr style="border-bottom:1px solid var(--clr-border); vertical-align:middle;">
                    <td style="padding:var(--sp-md) var(--sp-sm); color:var(--clr-text-secondary); font-size:0.9rem;">${date}</td>
                    <td style="padding:var(--sp-md) var(--sp-sm); font-weight:600;">${log.product ? log.product.name : 'Platillo Eliminado'}</td>
                    <td style="padding:var(--sp-md) var(--sp-sm);">${deltaHtml}</td>
                    <td style="padding:var(--sp-md) var(--sp-sm);"><span class="badge" style="background:var(--clr-surface-2); font-size:0.8rem;">${typeLabel}</span></td>
                    <td style="padding:var(--sp-md) var(--sp-sm); color:var(--clr-text-secondary); font-size:0.9rem;">${log.notes || '—'}</td>
                </tr>
            `;
        }).join('');

    } catch (err) {
        document.getElementById('inventoryLogsTable').innerHTML = `
            <tr>
                <td colspan="5" style="text-align:center; padding:var(--sp-2xl); color:var(--clr-danger);">
                    ⚠️ Error al cargar bitácora de inventarios.
                </td>
            </tr>`;
    }
}
</script>
@endsection
