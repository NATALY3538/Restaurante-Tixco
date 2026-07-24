@extends('layouts.app')

@section('title', 'Plano 2D e Infraestructura de Mesas')

@section('styles')
<style>
/* ═══ FLOOR PLAN & 2D TABLE STYLES ═══ */
.floor-plan-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: var(--sp-md);
    margin-bottom: var(--sp-xl);
}

.status-legend {
    display: flex;
    gap: var(--sp-md);
    align-items: center;
    background: var(--clr-surface-1);
    border: 1px solid var(--clr-border);
    padding: 8px 16px;
    border-radius: var(--radius-md);
    font-size: 0.85rem;
    flex-wrap: wrap;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 6px;
}

.legend-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    box-shadow: 0 0 8px currentColor;
}

/* Canvas Area */
.floor-plan-canvas {
    background: #0f172a;
    background-image: 
        radial-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
        radial-gradient(rgba(255, 255, 255, 0.05) 1px, #0f172a 1px);
    background-size: 20px 20px;
    background-position: 0 0, 10px 10px;
    border: 1px solid var(--clr-border);
    border-radius: var(--radius-lg);
    padding: var(--sp-xl);
    min-height: 320px;
    display: flex;
    flex-wrap: wrap;
    gap: 32px;
    align-items: center;
    justify-content: flex-start;
    position: relative;
    overflow: hidden;
}

/* Individual Table Container Node */
.mesa-visual-node {
    position: relative;
    width: 120px;
    height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    user-select: none;
    transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.mesa-visual-node:hover {
    transform: scale(1.12);
    z-index: 10;
}

/* Central Table Shape */
.mesa-shape {
    position: absolute;
    width: 68px;
    height: 68px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 700;
    font-family: var(--font-display);
    text-shadow: 0 2px 4px rgba(0,0,0,0.8);
    transition: all 0.3s ease;
    z-index: 2;
}

/* Table Shapes (Round vs Rectangular) */
.mesa-shape.shape-round {
    border-radius: 50%;
}
.mesa-shape.shape-rect {
    width: 80px;
    height: 58px;
    border-radius: 10px;
}

/* Table States (Color Coding & Glow) */
.mesa-node[data-state="available"] .mesa-shape {
    background: radial-gradient(circle at 30% 30%, #10b981, #047857);
    border: 2px solid #34d399;
    box-shadow: 0 0 16px rgba(16, 185, 129, 0.4), inset 0 2px 4px rgba(255,255,255,0.3);
}

.mesa-node[data-state="occupied"] .mesa-shape {
    background: radial-gradient(circle at 30% 30%, #ef4444, #b91c1c);
    border: 2px solid #f87171;
    box-shadow: 0 0 16px rgba(239, 68, 68, 0.5), inset 0 2px 4px rgba(255,255,255,0.3);
}

.mesa-node[data-state="reserved"] .mesa-shape {
    background: radial-gradient(circle at 30% 30%, #f59e0b, #b45309);
    border: 2px solid #fbbf24;
    box-shadow: 0 0 16px rgba(245, 158, 11, 0.4), inset 0 2px 4px rgba(255,255,255,0.3);
}

.mesa-node[data-state="disabled"] .mesa-shape {
    background: radial-gradient(circle at 30% 30%, #475569, #1e293b);
    border: 2px solid #64748b;
    opacity: 0.6;
    box-shadow: none;
}

/* Seat / Chair Dots */
.mesa-seat {
    position: absolute;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    z-index: 1;
    transition: all 0.3s ease;
}

.mesa-node[data-state="available"] .mesa-seat {
    background: #34d399;
    box-shadow: 0 0 8px rgba(52, 211, 153, 0.6);
    border: 1px solid #10b981;
}

.mesa-node[data-state="occupied"] .mesa-seat {
    background: #f87171;
    box-shadow: 0 0 8px rgba(248, 113, 113, 0.6);
    border: 1px solid #ef4444;
}

.mesa-node[data-state="reserved"] .mesa-seat {
    background: #fbbf24;
    box-shadow: 0 0 8px rgba(251, 191, 36, 0.6);
    border: 1px solid #f59e0b;
}

.mesa-node[data-state="disabled"] .mesa-seat {
    background: #64748b;
    border: 1px solid #475569;
    opacity: 0.5;
}

/* Tooltip Popup */
.mesa-tooltip {
    position: absolute;
    bottom: calc(100% + 10px);
    left: 50%;
    transform: translateX(-50%) translateY(6px);
    background: rgba(15, 23, 42, 0.95);
    backdrop-filter: blur(12px);
    border: 1px solid var(--clr-border);
    padding: 8px 12px;
    border-radius: var(--radius-md);
    white-space: nowrap;
    font-size: 0.75rem;
    color: #fff;
    pointer-events: none;
    opacity: 0;
    transition: all 0.2s ease;
    box-shadow: 0 10px 25px rgba(0,0,0,0.5);
    z-index: 20;
}

.mesa-visual-node:hover .mesa-tooltip {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
}

/* Print Stylesheet for Table QR Stand Card */
@media print {
    body * {
        visibility: hidden !important;
    }
    #qrPrintArea, #qrPrintArea * {
        visibility: visible !important;
    }
    #qrPrintArea {
        position: fixed !important;
        inset: 0 !important;
        background: #ffffff !important;
        color: #000000 !important;
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        z-index: 999999 !important;
        padding: 40px !important;
    }
    .print-card-box {
        border: 4px double #000 !important;
        border-radius: 20px !important;
        padding: 30px !important;
        text-align: center !important;
        width: 320px !important;
        box-shadow: none !important;
        background: #fff !important;
    }
}
</style>
@endsection

@section('content')
<div class="container" style="padding-top: var(--sp-xl); padding-bottom: var(--sp-xxl);">
    <!-- Header -->
    <div class="floor-plan-header">
        <div>
            <h1 style="font-family:var(--font-display); font-size:2rem; margin-bottom:0.25rem; color:var(--clr-primary);">
                🗺️ Plano Visual 2D y Distribución de Mesas
            </h1>
            <p style="color:var(--clr-text-muted); font-size:0.95rem;">
                Visualiza el mapa interactivo en tiempo real de salones, capacidad de sillas y estados de mesas.
            </p>
        </div>
        <div style="display:flex; gap:var(--sp-sm); flex-wrap:wrap;">
            <button onclick="openAreaModal()" class="btn btn-primary" style="display:flex; align-items:center; gap:6px;">
                ➕ Nueva Área / Salón
            </button>
            <a href="/admin" class="btn btn-secondary">
                ⬅️ Catálogo Admin
            </a>
        </div>
    </div>

    <!-- Status Legend & Auto Refresh Notice -->
    <div style="margin-bottom:var(--sp-lg); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:var(--sp-md);">
        <div class="status-legend">
            <span style="font-weight:600; margin-right:6px; color:var(--clr-text-muted);">Estados:</span>
            <div class="legend-item"><span class="legend-dot" style="background:#10b981; color:#10b981;"></span> 🟢 Disponible</div>
            <div class="legend-item"><span class="legend-dot" style="background:#ef4444; color:#ef4444;"></span> 🔴 Ocupada</div>
            <div class="legend-item"><span class="legend-dot" style="background:#f59e0b; color:#f59e0b;"></span> 🟡 Reservada</div>
            <div class="legend-item"><span class="legend-dot" style="background:#64748b; color:#64748b;"></span> ⚪ Inactiva</div>
        </div>
        <div style="font-size:0.85rem; color:var(--clr-text-muted); display:flex; align-items:center; gap:8px;">
            <span>🔄 Sincronización en vivo (6s)</span>
            💡 <em>Pasa el cursor sobre una mesa o haz clic para interactuar.</em>
        </div>
    </div>

    <!-- Container of Areas & Floor Plans -->
    <div id="areasContainer" style="display:flex; flex-direction:column; gap:var(--sp-xl);">
        <div style="text-align:center; padding:var(--sp-xl); color:var(--clr-text-muted); background:var(--clr-surface-1); border-radius:var(--radius-lg);">
            Cargando lienzo 2D del restaurante...
        </div>
    </div>
</div>

<!-- ═══ MODAL INTERACTIVO DETALLE DE MESA ═══ -->
<div id="tableDetailModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); backdrop-filter:blur(6px); z-index:9999; justify-content:center; align-items:center; padding:var(--sp-md);">
    <div style="background:var(--clr-surface-1); border:1px solid var(--clr-border); border-radius:var(--radius-lg); width:100%; max-width:460px; padding:var(--sp-lg); box-shadow:0 20px 40px rgba(0,0,0,0.6);">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:var(--sp-md);">
            <div>
                <h3 id="modalTableTitle" style="font-family:var(--font-display); font-size:1.3rem; color:var(--clr-text); margin-bottom:2px;">Mesa T1</h3>
                <p id="modalTableSubtitle" style="font-size:0.85rem; color:var(--clr-text-muted);">Área: Terraza</p>
            </div>
            <span id="modalTableBadge" class="badge badge-success">🟢 Disponible</span>
        </div>

        <div style="background:var(--clr-surface-2); border:1px solid var(--clr-border); border-radius:var(--radius-md); padding:var(--sp-md); margin-bottom:var(--sp-md); font-size:0.85rem;">
            <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                <span style="color:var(--clr-text-muted);">Capacidad Sillas:</span>
                <strong id="modalTableCapacity" style="color:var(--clr-text);">4 Personas</strong>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                <span style="color:var(--clr-text-muted);">Forma Física:</span>
                <strong id="modalTableShape" style="color:var(--clr-text);">Circular (Circular)</strong>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                <span style="color:var(--clr-text-muted);">Código QR Token:</span>
                <code id="modalTableToken" style="color:var(--clr-primary);">mesa4</code>
            </div>
            <div style="display:flex; justify-content:space-between;">
                <span style="color:var(--clr-text-muted);">Estado Actual:</span>
                <strong id="modalTableStatusText">Lista para ordenar</strong>
            </div>
        </div>

        <div style="display:flex; flex-direction:column; gap:var(--sp-xs); margin-bottom:var(--sp-md);">
            <div id="modalLiberarContainer" style="display:none;">
                <button onclick="handleLiberarMesaFromModal()" class="btn btn-success" style="width:100%; display:flex; justify-content:center; align-items:center; gap:6px; font-weight:700;">
                    🔓 Liberar / Desocupar Mesa (Marcar Disponible 🟢)
                </button>
            </div>

            <!-- RENAMED BUTTON: Ver y Gestionar Código QR -->
            <button onclick="openQrManagementModal()" class="btn btn-primary" style="display:flex; justify-content:center; align-items:center; gap:6px; background:var(--clr-primary); color:#fff; font-weight:600;">
                🔍 Ver y Gestionar Código QR
            </button>

            <button id="modalEditMesaBtn" onclick="openAddEditSingleMesaModal()" class="btn btn-secondary" style="width:100%;">
                ✏️ Editar Mesa (Capacidad, Forma, Nombre)
            </button>

            <button id="modalToggleStatusBtn" onclick="handleToggleTableStatusFromModal()" class="btn btn-secondary" style="width:100%;">
                🔴 Cambiar Estado / Desactivar Mesa
            </button>
        </div>

        <div style="display:flex; justify-content:flex-end;">
            <button onclick="closeTableDetailModal()" class="btn btn-secondary" style="font-size:0.85rem;">Cerrar</button>
        </div>
    </div>
</div>

<!-- ═══ NUEVO SUB-MODAL: GESTIÓN Y VISTA PREVIA DE CÓDIGO QR ═══ -->
<div id="qrManagementModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); backdrop-filter:blur(8px); z-index:99999; justify-content:center; align-items:center; padding:var(--sp-md);">
    <div style="background:var(--clr-surface-1); border:1px solid var(--clr-border); border-radius:var(--radius-lg); width:100%; max-width:480px; padding:var(--sp-lg); box-shadow:0 20px 50px rgba(0,0,0,0.8);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--sp-md);">
            <h3 id="qrModalHeading" style="font-family:var(--font-display); font-size:1.2rem; color:var(--clr-text);">Gestión de Código QR</h3>
            <button onclick="closeQrManagementModal()" style="background:none; border:none; color:var(--clr-text-muted); font-size:1.2rem; cursor:pointer;">✕</button>
        </div>

        <!-- Printable QR Stand Card Container -->
        <div id="qrPrintArea">
            <div class="print-card-box" style="background:var(--clr-surface-2); border:2px dashed var(--clr-primary); border-radius:var(--radius-md); padding:var(--sp-md); text-align:center; margin-bottom:var(--sp-md);">
                <div style="display:flex; align-items:center; justify-content:center; gap:8px; margin-bottom:8px;">
                    <img src="/img/logo-tixco.png" alt="Tixco" style="width:28px; height:28px; border-radius:6px; object-fit:cover;">
                    <span style="font-family:var(--font-display); font-size:1.1rem; font-weight:700; color:var(--clr-text);">Restaurante Tixco</span>
                </div>
                <p style="font-size:0.75rem; color:var(--clr-text-muted); margin-bottom:var(--sp-sm);">Escanea para ordenar y pedir en mesa</p>
                
                <!-- Dynamically Rendered QR Code Image -->
                <div style="background:#fff; padding:12px; border-radius:12px; display:inline-block; margin-bottom:var(--sp-sm); box-shadow:0 4px 12px rgba(0,0,0,0.3);">
                    <img id="qrCodeImg" src="" alt="Código QR" style="width:180px; height:180px; display:block;">
                </div>

                <h4 id="qrCardTableName" style="font-family:var(--font-display); font-size:1.3rem; color:var(--clr-primary); margin-bottom:2px;">Mesa T1</h4>
                <p id="qrCardAreaName" style="font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Área: Terraza</p>
                <div id="qrCardCapacityBadge" style="font-size:0.75rem; font-weight:600; color:var(--clr-text); opacity:0.8;">Aforo: 4 Personas</div>
            </div>
        </div>

        <!-- Direct URL display -->
        <div style="background:var(--clr-surface-2); border:1px solid var(--clr-border); border-radius:var(--radius-sm); padding:8px 12px; margin-bottom:var(--sp-md); display:flex; justify-content:space-between; align-items:center; gap:8px;">
            <span id="qrDirectUrlText" style="font-size:0.75rem; color:var(--clr-text-muted); word-break:break-all; font-family:monospace;">http://127.0.0.1:8000/mesa/...</span>
            <button onclick="copyQrDirectUrl()" class="btn btn-sm btn-secondary" style="font-size:0.75rem; flex-shrink:0;">🔗 Copiar</button>
        </div>

        <!-- Action Toolbar -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--sp-sm); margin-bottom:var(--sp-md);">
            <button onclick="printQrStandCard()" class="btn btn-primary" style="display:flex; justify-content:center; align-items:center; gap:6px; font-size:0.85rem;">
                🖨️ Imprimir Etiqueta
            </button>
            <button onclick="downloadQrCodeImage()" class="btn btn-secondary" style="display:flex; justify-content:center; align-items:center; gap:6px; font-size:0.85rem;">
                📥 Descargar PNG
            </button>
        </div>

        <div style="margin-bottom:var(--sp-md);">
            <!-- Opens Customer Interface in a NEW TAB (target="_blank") so Admin NEVER loses session tab -->
            <a id="simulateCustomerViewBtn" href="#" target="_blank" class="btn btn-secondary" style="width:100%; display:flex; justify-content:center; align-items:center; gap:6px; background:var(--clr-surface-2); border:1px solid var(--clr-primary); color:var(--clr-primary); font-size:0.85rem; font-weight:600;">
                📱 Probar Vista Cliente (Simulación en Nueva Pestaña)
            </a>
        </div>

        <div style="display:flex; justify-content:flex-end;">
            <button onclick="closeQrManagementModal()" class="btn btn-secondary" style="font-size:0.85rem;">Regresar</button>
        </div>
    </div>
</div>

<!-- ═══ MODAL CREAR / EDITAR ÁREA ═══ -->
<div id="areaModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); backdrop-filter:blur(4px); z-index:9999; justify-content:center; align-items:center; padding:var(--sp-md);">
    <div style="background:var(--clr-surface-1); border:1px solid var(--clr-border); border-radius:var(--radius-lg); width:100%; max-width:500px; padding:var(--sp-lg); box-shadow:0 20px 40px rgba(0,0,0,0.5);">
        <h3 id="areaModalTitle" style="font-family:var(--font-display); margin-bottom:var(--sp-md); color:var(--clr-text);">Nueva Área de Servicio</h3>
        <form id="areaForm" onsubmit="handleSaveArea(event)">
            <input type="hidden" id="areaId" value="">
            <div style="margin-bottom:var(--sp-sm);">
                <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Nombre del Área *</label>
                <input type="text" id="areaName" required class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);" placeholder="Ej. Terraza Norte, Salón VIP">
            </div>
            <div style="margin-bottom:var(--sp-sm);">
                <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Descripción</label>
                <textarea id="areaDescription" class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm); min-height:60px;" placeholder="Descripción de ubicación o características..."></textarea>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--sp-sm); margin-bottom:var(--sp-sm);">
                <div>
                    <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Máx. Mesas (0 = Ilimitado)</label>
                    <input type="number" id="areaMaxTables" min="0" value="0" class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);">
                </div>
                <div>
                    <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Máx. Capacidad Personas</label>
                    <input type="number" id="areaMaxCapacity" min="0" value="0" class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);">
                </div>
            </div>

            <!-- Checkbox Layout FIX: Horizontal flex row -->
            <div style="display:flex; flex-direction:row; align-items:center; gap:var(--sp-lg); margin-bottom:var(--sp-lg); flex-wrap:wrap;">
                <label style="display:inline-flex; align-items:center; gap:8px; font-size:0.9rem; color:var(--clr-text); cursor:pointer; user-select:none;">
                    <input type="checkbox" id="areaAllowsSmoking" style="width:18px; height:18px; accent-color:var(--clr-primary); cursor:pointer;">
                    <span>🚬 Área de Fumar</span>
                </label>
                <label style="display:inline-flex; align-items:center; gap:8px; font-size:0.9rem; color:var(--clr-text); cursor:pointer; user-select:none;">
                    <input type="checkbox" id="areaIsVip" style="width:18px; height:18px; accent-color:var(--clr-primary); cursor:pointer;">
                    <span>👑 Zona VIP</span>
                </label>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:var(--sp-sm);">
                <button type="button" onclick="closeAreaModal()" class="btn btn-secondary">Cancelar</button>
                <button type="submit" id="btnSaveArea" onclick="handleSaveArea(event)" class="btn btn-primary" style="background:var(--clr-primary); color:#fff; font-weight:600;">Guardar Área</button>
            </div>
        </form>
    </div>
</div>

<!-- ═══ MODAL CREAR / EDITAR MESA INDIVIDUAL (ModalAddEditMesa) ═══ -->
<div id="addEditMesaModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); backdrop-filter:blur(4px); z-index:9999; justify-content:center; align-items:center; padding:var(--sp-md);">
    <div style="background:var(--clr-surface-1); border:1px solid var(--clr-border); border-radius:var(--radius-lg); width:100%; max-width:480px; padding:var(--sp-lg); box-shadow:0 20px 40px rgba(0,0,0,0.5);">
        <h3 id="singleMesaModalTitle" style="font-family:var(--font-display); margin-bottom:var(--sp-md); color:var(--clr-text);">Configurar Mesa</h3>
        <form id="singleMesaForm" onsubmit="handleSaveSingleMesa(event)">
            <input type="hidden" id="singleMesaId" value="">
            <div style="margin-bottom:var(--sp-sm);">
                <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Área / Salón Destino *</label>
                <select id="singleMesaAreaId" required class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);"></select>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--sp-sm); margin-bottom:var(--sp-sm);">
                <div>
                    <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Código de Mesa *</label>
                    <input type="text" id="singleMesaCode" required class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);" placeholder="Ej. M-05, T2">
                </div>
                <div>
                    <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Nombre Visible</label>
                    <input type="text" id="singleMesaName" class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);" placeholder="Ej. Mesa 5">
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--sp-sm); margin-bottom:var(--sp-md);">
                <div>
                    <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Capacidad (Sillas) *</label>
                    <input type="number" id="singleMesaCapacity" min="1" value="4" required class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);">
                </div>
                <div>
                    <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Forma Geométrica</label>
                    <select id="singleMesaShape" class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);">
                        <option value="round">🟢 Circular</option>
                        <option value="rect">🟦 Rectangular / Lineal</option>
                    </select>
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:var(--sp-sm);">
                <button type="button" onclick="closeAddEditSingleMesaModal()" class="btn btn-secondary">Cancelar</button>
                <button type="submit" id="btnSaveSingleMesa" onclick="handleSaveSingleMesa(event)" class="btn btn-primary" style="background:var(--clr-primary); color:#fff; font-weight:600;">Guardar Mesa</button>
            </div>
        </form>
    </div>
</div>

<!-- ═══ MODAL GENERACIÓN MASIVA DE MESAS ═══ -->
<div id="tableModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); backdrop-filter:blur(4px); z-index:9999; justify-content:center; align-items:center; padding:var(--sp-md);">
    <div style="background:var(--clr-surface-1); border:1px solid var(--clr-border); border-radius:var(--radius-lg); width:100%; max-width:500px; padding:var(--sp-lg); box-shadow:0 20px 40px rgba(0,0,0,0.5);">
        <h3 style="font-family:var(--font-display); margin-bottom:var(--sp-md); color:var(--clr-text);">Agregar Mesas al Área</h3>
        <form id="tableForm" onsubmit="handleSaveTables(event)">
            <input type="hidden" id="targetAreaId" value="">
            <div style="margin-bottom:var(--sp-sm);">
                <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Modo de Creación</label>
                <select id="tableCreationMode" onchange="toggleCreationModeFields()" class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);">
                    <option value="masivo">⚡ Generación Masiva (Recomendado)</option>
                    <option value="individual">📌 Crear Mesa Única</option>
                </select>
            </div>

            <div id="bulkFields">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--sp-sm); margin-bottom:var(--sp-sm);">
                    <div>
                        <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Prefijo Código</label>
                        <input type="text" id="tablePrefix" value="M" class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);" placeholder="Ej. T1, M, BAL">
                    </div>
                    <div>
                        <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Cantidad de Mesas</label>
                        <input type="number" id="tableQuantity" min="1" max="50" value="4" class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);">
                    </div>
                </div>
                <div style="margin-bottom:var(--sp-md);">
                    <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Capacidad por Mesa (Personas)</label>
                    <input type="number" id="bulkCapacity" min="1" value="4" class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);">
                </div>
            </div>

            <div id="singleFields" style="display:none;">
                <div style="margin-bottom:var(--sp-sm);">
                    <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Código de Mesa</label>
                    <input type="text" id="singleTableCode" class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);" placeholder="Ej. M-101">
                </div>
                <div style="margin-bottom:var(--sp-md);">
                    <label style="display:block; font-size:0.85rem; color:var(--clr-text-muted); margin-bottom:4px;">Capacidad (Personas)</label>
                    <input type="number" id="singleCapacity" min="1" value="4" class="form-control" style="width:100%; background:var(--clr-surface-2); border:1px solid var(--clr-border); color:var(--clr-text); padding:8px 12px; border-radius:var(--radius-sm);">
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:var(--sp-sm);">
                <button type="button" onclick="closeTableModal()" class="btn btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-primary">Generar Mesas</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
let areasData = [];
let activeSelectedTable = null;
let currentAreaName = '';

document.addEventListener('DOMContentLoaded', () => {
    loadAreas();
    // Auto Refresh every 6 seconds for live status sync
    setInterval(loadAreas, 6000);
});

async function loadAreas() {
    try {
        const res = await fetch('/api/admin/areas');
        areasData = await res.json();
        renderAreasFloorPlan(areasData);
    } catch (err) {
        console.error('Error al cargar mapa', err);
    }
}

/* ═════════════════════════════════════════════════════════════════════════════
   2D FLOOR PLAN RENDERER
   ═════════════════════════════════════════════════════════════════════════════ */
function renderAreasFloorPlan(areas) {
    const container = document.getElementById('areasContainer');
    if (!areas || areas.length === 0) {
        container.innerHTML = `
            <div style="text-align:center; padding:var(--sp-xl); background:var(--clr-surface-1); border-radius:var(--radius-lg);">
                <p style="color:var(--clr-text-muted);">No hay áreas de servicio configuradas.</p>
                <button onclick="openAreaModal()" class="btn btn-primary" style="margin-top:var(--sp-sm);">Crear Primera Área</button>
            </div>`;
        return;
    }

    container.innerHTML = areas.map(area => {
        const tables = area.tables || [];
        const isLimitReached = area.max_tables > 0 && tables.length >= area.max_tables;
        
        return `
        <div style="background:var(--clr-surface-1); border:1px solid ${area.is_active ? 'var(--clr-border)' : '#f43f5e'}; border-radius:var(--radius-lg); padding:var(--sp-lg); opacity: ${area.is_active ? '1' : '0.7'}; box-shadow:0 10px 30px rgba(0,0,0,0.3);">
            <!-- Area Header -->
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:var(--sp-sm); margin-bottom:var(--sp-md);">
                <div>
                    <h2 style="font-size:1.3rem; color:var(--clr-text); font-family:var(--font-display); display:flex; align-items:center; gap:8px;">
                        🏛️ ${escapeHtml(area.name)}
                        ${area.allows_smoking ? '<span title="Área de Fumar">🚬</span>' : ''}
                        ${area.is_vip ? '<span title="Zona VIP">👑</span>' : ''}
                    </h2>
                    <p style="font-size:0.85rem; color:var(--clr-text-muted); margin-top:2px;">
                        ${escapeHtml(area.description || 'Sin descripción')}
                    </p>
                </div>

                <!-- Stats & Actions Toolbar -->
                <div style="display:flex; align-items:center; gap:var(--sp-sm); flex-wrap:wrap;">
                    <div style="background:var(--clr-surface-2); border:1px solid var(--clr-border); padding:6px 12px; border-radius:var(--radius-sm); font-size:0.8rem; display:flex; gap:12px;">
                        <span><strong>Mesas:</strong> ${tables.length} / ${area.max_tables > 0 ? area.max_tables : '∞'}</span>
                        <span><strong>Máx Aforo:</strong> ${area.max_capacity > 0 ? area.max_capacity + ' pers.' : '∞'}</span>
                    </div>

                    <button onclick="openAddEditSingleMesaModal(null, ${area.id})" ${isLimitReached || !area.is_active ? 'disabled' : ''} class="btn btn-sm btn-primary">
                        ➕ Mesa Única
                    </button>
                    <button onclick="openTableModal(${area.id})" ${isLimitReached || !area.is_active ? 'disabled' : ''} class="btn btn-sm btn-secondary">
                        ⚡ Gen. Masiva
                    </button>
                    <button onclick="openAreaModal(${area.id})" class="btn btn-sm btn-secondary">
                        ✏️ Editar
                    </button>
                    <button onclick="toggleAreaStatus(${area.id}, ${area.is_active})" class="btn btn-sm ${area.is_active ? 'btn-danger' : 'btn-success'}">
                        ${area.is_active ? '🚫 Desactivar' : '✅ Activar'}
                    </button>
                </div>
            </div>

            <!-- 2D Canvas Floor Plan -->
            <div class="floor-plan-canvas">
                ${tables.length === 0 ? `
                    <div style="width:100%; text-align:center; padding:var(--sp-xl); color:var(--clr-text-muted);">
                        Lienzo vacío. Haz clic en <strong>➕ Mesa Única</strong> o <strong>⚡ Gen. Masiva</strong> para añadir mesas.
                    </div>
                ` : tables.map(table => renderMesaVisualNode(table, area.name)).join('')}
            </div>
        </div>
        `;
    }).join('');
}

/* ═════════════════════════════════════════════════════════════════════════════
   POSICIONAMIENTO TRIGONOMÉTRICO DE SILLAS
   ═════════════════════════════════════════════════════════════════════════════ */
function renderMesaVisualNode(table, areaName) {
    const capacity = parseInt(table.capacity) || 4;
    const isRound = table.shape === 'rect' ? false : (capacity <= 5 || capacity % 2 !== 0);
    const shapeClass = isRound ? 'shape-round' : 'shape-rect';

    let state = 'available';
    let stateText = '🟢 Disponible';

    if (!table.is_active) {
        state = 'disabled';
        stateText = '⚪ Inactiva';
    } else if (table.status === 'occupied') {
        state = 'occupied';
        stateText = '🔴 Ocupada';
    } else if (table.status === 'reserved') {
        state = 'reserved';
        stateText = '🟡 Reservada';
    }

    let seatsHTML = '';
    const containerRadius = 48;

    for (let i = 0; i < capacity; i++) {
        const angle = (2 * Math.PI * i / capacity) - (Math.PI / 2);
        const x = 60 + containerRadius * Math.cos(angle) - 7;
        const y = 60 + containerRadius * Math.sin(angle) - 7;

        seatsHTML += `<div class="mesa-seat" style="left:${x.toFixed(1)}px; top:${y.toFixed(1)}px;"></div>`;
    }

    const tableJsonStr = escapeHtml(JSON.stringify(table));

    return `
    <div class="mesa-visual-node mesa-node" data-state="${state}" onclick="handleTableClick('${tableJsonStr}', '${escapeHtml(areaName)}')">
        ${seatsHTML}

        <div class="mesa-shape ${shapeClass}">
            <span style="font-size:0.95rem;">${escapeHtml(table.table_code)}</span>
            <span style="font-size:0.65rem; font-weight:400; opacity:0.9;">👤 ${capacity}</span>
        </div>

        <div class="mesa-tooltip">
            <strong>${escapeHtml(table.name || table.table_code)}</strong> (${areaName})<br>
            <span>Estado: ${stateText}</span><br>
            <span>Capacidad: ${capacity} Sillas</span>
        </div>
    </div>
    `;
}

/* ═════════════════════════════════════════════════════════════════════════════
   INTERACTIVE TABLE CLICK & DETAIL MODAL
   ═════════════════════════════════════════════════════════════════════════════ */
function handleTableClick(tableJson, areaName) {
    const table = JSON.parse(tableJson);
    activeSelectedTable = table;
    currentAreaName = areaName;

    document.getElementById('modalTableTitle').innerText = table.name || table.table_code;
    document.getElementById('modalTableSubtitle').innerText = `Área: ${areaName}`;
    document.getElementById('modalTableCapacity').innerText = `${table.capacity} Personas (Sillas)`;
    document.getElementById('modalTableShape').innerText = table.shape === 'rect' ? '🟦 Rectangular / Lineal' : '🟢 Circular';
    document.getElementById('modalTableToken').innerText = table.qr_token;

    const badge = document.getElementById('modalTableBadge');
    const statusText = document.getElementById('modalTableStatusText');
    const toggleBtn = document.getElementById('modalToggleStatusBtn');
    const liberarContainer = document.getElementById('modalLiberarContainer');

    if (!table.is_active) {
        badge.className = 'badge badge-danger';
        badge.innerText = '⚪ Inactiva';
        statusText.innerText = 'Mesa fuera de servicio';
        toggleBtn.innerText = '✅ Activar Mesa';
        toggleBtn.className = 'btn btn-success';
        liberarContainer.style.display = 'none';
    } else if (table.status === 'occupied') {
        badge.className = 'badge badge-danger';
        badge.innerText = '🔴 Ocupada';
        statusText.innerText = 'Comensales activos en mesa con pedido';
        toggleBtn.innerText = '🔴 Cambiar Estado / Desactivar Mesa';
        toggleBtn.className = 'btn btn-secondary';
        liberarContainer.style.display = 'block';
    } else {
        badge.className = 'badge badge-success';
        badge.innerText = '🟢 Disponible';
        statusText.innerText = 'Lista para recibir clientes o reservación';
        toggleBtn.innerText = '🔴 Desactivar Mesa';
        toggleBtn.className = 'btn btn-danger';
        liberarContainer.style.display = 'none';
    }

    document.getElementById('tableDetailModal').style.display = 'flex';
}

function closeTableDetailModal() {
    document.getElementById('tableDetailModal').style.display = 'none';
}

async function handleLiberarMesaFromModal() {
    if (!activeSelectedTable) return;
    try {
        const res = await fetch(`/api/admin/mesas/${activeSelectedTable.id}/liberar`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        if (!res.ok) {
            showToast(data.message || 'Error al liberar mesa', 'error');
            return;
        }
        showToast(data.message);
        closeTableDetailModal();
        await loadAreas();
    } catch (err) {
        showToast('Error al conectar con el servidor', 'error');
    }
}

async function handleToggleTableStatusFromModal() {
    if (!activeSelectedTable) return;
    closeTableDetailModal();
    await toggleTableStatus(activeSelectedTable.id, activeSelectedTable.is_active);
}

/* ═════════════════════════════════════════════════════════════════════════════
   NUEVO SUB-MODAL: GESTIÓN DE CÓDIGO QR (NUNCA EXPULSA AL ADMIN DE SU PESTAÑA)
   ═════════════════════════════════════════════════════════════════════════════ */
function openQrManagementModal() {
    if (!activeSelectedTable) return;
    closeTableDetailModal();

    const table = activeSelectedTable;
    const fullUrl = `${window.location.origin}/mesa/${table.qr_token}`;

    document.getElementById('qrModalHeading').innerText = `Gestión de Código QR — ${table.name || table.table_code}`;
    document.getElementById('qrCardTableName').innerText = table.name || table.table_code;
    document.getElementById('qrCardAreaName').innerText = `Área: ${currentAreaName || 'Restaurante'}`;
    document.getElementById('qrCardCapacityBadge').innerText = `Aforo: ${table.capacity} Personas`;
    document.getElementById('qrDirectUrlText').innerText = fullUrl;

    // Render Dynamic QR Image using Google Chart QR API
    const qrApiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(fullUrl)}`;
    document.getElementById('qrCodeImg').src = qrApiUrl;

    // Set Customer Simulation Button to OPEN IN NEW TAB (target="_blank")
    const simBtn = document.getElementById('simulateCustomerViewBtn');
    simBtn.href = fullUrl;

    document.getElementById('qrManagementModal').style.display = 'flex';
}

function closeQrManagementModal() {
    document.getElementById('qrManagementModal').style.display = 'none';
}

function copyQrDirectUrl() {
    const urlText = document.getElementById('qrDirectUrlText').innerText;
    navigator.clipboard.writeText(urlText).then(() => {
        showToast('¡Enlace directo copiado al portapapeles!');
    }).catch(err => {
        showToast('Error al copiar enlace', 'error');
    });
}

function printQrStandCard() {
    window.print();
}

function downloadQrCodeImage() {
    const img = document.getElementById('qrCodeImg');
    const link = document.createElement('a');
    link.href = img.src;
    link.download = `QR-${activeSelectedTable ? activeSelectedTable.table_code : 'Mesa'}.png`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/* ═════════════════════════════════════════════════════════════════════════════
   MODAL ADD / EDIT SINGLE MESA HANDLERS
   ═════════════════════════════════════════════════════════════════════════════ */
function openAddEditSingleMesaModal(table = null, defaultAreaId = null) {
    if (activeSelectedTable && !table) {
        table = activeSelectedTable;
    }
    if (activeSelectedTable) {
        closeTableDetailModal();
    }

    const select = document.getElementById('singleMesaAreaId');
    select.innerHTML = areasData.map(a => `<option value="${a.id}">${escapeHtml(a.name)}</option>`).join('');

    if (table) {
        document.getElementById('singleMesaModalTitle').innerText = 'Editar Mesa';
        document.getElementById('singleMesaId').value = table.id;
        document.getElementById('singleMesaAreaId').value = table.service_area_id;
        document.getElementById('singleMesaCode').value = table.table_code;
        document.getElementById('singleMesaName').value = table.name || '';
        document.getElementById('singleMesaCapacity').value = table.capacity;
        document.getElementById('singleMesaShape').value = table.shape || 'round';
    } else {
        document.getElementById('singleMesaModalTitle').innerText = 'Agregar Mesa Única';
        document.getElementById('singleMesaId').value = '';
        if (defaultAreaId) document.getElementById('singleMesaAreaId').value = defaultAreaId;
        document.getElementById('singleMesaCode').value = '';
        document.getElementById('singleMesaName').value = '';
        document.getElementById('singleMesaCapacity').value = 4;
        document.getElementById('singleMesaShape').value = 'round';
    }

    document.getElementById('addEditMesaModal').style.display = 'flex';
}

function closeAddEditSingleMesaModal() {
    document.getElementById('addEditMesaModal').style.display = 'none';
}

async function handleSaveSingleMesa(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }

    const id = document.getElementById('singleMesaId').value;
    const areaId = document.getElementById('singleMesaAreaId').value;
    const code = document.getElementById('singleMesaCode').value.trim();
    const capacity = parseInt(document.getElementById('singleMesaCapacity').value) || 0;

    if (!areaId || !code || capacity <= 0) {
        showToast('Por favor completa los campos obligatorios del formulario', 'error');
        return false;
    }

    const payload = {
        service_area_id: parseInt(areaId),
        table_code: code,
        name: document.getElementById('singleMesaName').value.trim(),
        capacity: capacity,
        shape: document.getElementById('singleMesaShape').value
    };

    try {
        const url = id ? `/api/admin/mesas/${id}` : '/api/admin/mesas';
        const method = id ? 'PUT' : 'POST';

        const res = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        if (!res.ok) {
            showToast(data.message || 'Error al guardar mesa', 'error');
            return false;
        }

        showToast(id ? 'Mesa actualizada correctamente' : 'Mesa creada y agregada al lienzo');
        closeAddEditSingleMesaModal();
        await loadAreas();
    } catch (err) {
        showToast('Error de conexión con el servidor', 'error');
    }
    return false;
}

/* ═════════════════════════════════════════════════════════════════════════════
   AREA MODAL HANDLERS
   ═════════════════════════════════════════════════════════════════════════════ */
function openAreaModal(id = null) {
    document.getElementById('areaForm').reset();
    document.getElementById('areaId').value = id || '';
    document.getElementById('areaModalTitle').innerText = id ? 'Editar Área de Servicio' : 'Nueva Área de Servicio';

    if (id) {
        const area = areasData.find(a => a.id === id);
        if (area) {
            document.getElementById('areaName').value = area.name;
            document.getElementById('areaDescription').value = area.description || '';
            document.getElementById('areaMaxTables').value = area.max_tables || 0;
            document.getElementById('areaMaxCapacity').value = area.max_capacity || 0;
            document.getElementById('areaAllowsSmoking').checked = area.allows_smoking;
            document.getElementById('areaIsVip').checked = area.is_vip;
        }
    }

    document.getElementById('areaModal').style.display = 'flex';
}

function closeAreaModal() {
    document.getElementById('areaModal').style.display = 'none';
}

async function handleSaveArea(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }

    const id = document.getElementById('areaId').value;
    const name = document.getElementById('areaName').value.trim();

    if (!name) {
        showToast('El nombre del área es obligatorio', 'error');
        return false;
    }

    const payload = {
        name: name,
        description: document.getElementById('areaDescription').value.trim(),
        max_tables: parseInt(document.getElementById('areaMaxTables').value) || 0,
        max_capacity: parseInt(document.getElementById('areaMaxCapacity').value) || 0,
        allows_smoking: document.getElementById('areaAllowsSmoking').checked,
        is_vip: document.getElementById('areaIsVip').checked
    };

    try {
        const url = id ? `/api/admin/areas/${id}` : '/api/admin/areas';
        const method = id ? 'PUT' : 'POST';

        const res = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        if (!res.ok) {
            showToast(data.message || 'Error al guardar área', 'error');
            return false;
        }

        showToast(id ? 'Área de servicio actualizada' : 'Área de servicio creada');
        closeAreaModal();
        await loadAreas();
    } catch (err) {
        showToast('Error de servidor al guardar área', 'error');
    }
    return false;
}

async function toggleAreaStatus(id, currentStatus) {
    try {
        const res = await fetch(`/api/admin/areas/${id}/status`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ is_active: !currentStatus })
        });
        const data = await res.json();
        if (!res.ok) {
            showToast(data.message || 'No se puede modificar el estado del área', 'error');
            return;
        }
        showToast(data.message);
        await loadAreas();
    } catch (err) {
        showToast('Error de conexión', 'error');
    }
}

function openTableModal(areaId) {
    document.getElementById('tableForm').reset();
    document.getElementById('targetAreaId').value = areaId;
    toggleCreationModeFields();
    document.getElementById('tableModal').style.display = 'flex';
}

function closeTableModal() {
    document.getElementById('tableModal').style.display = 'none';
}

function toggleCreationModeFields() {
    const mode = document.getElementById('tableCreationMode').value;
    document.getElementById('bulkFields').style.display = mode === 'masivo' ? 'block' : 'none';
    document.getElementById('singleFields').style.display = mode === 'individual' ? 'block' : 'none';
}

async function handleSaveTables(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }

    const areaId = document.getElementById('targetAreaId').value;
    const mode = document.getElementById('tableCreationMode').value;

    if (mode === 'individual') {
        closeTableModal();
        openAddEditSingleMesaModal(null, areaId);
        return false;
    }

    const payload = {
        modo: 'masivo',
        prefijo: document.getElementById('tablePrefix').value,
        cantidad: parseInt(document.getElementById('tableQuantity').value) || 1,
        capacidad_por_mesa: parseInt(document.getElementById('bulkCapacity').value) || 4
    };

    try {
        const res = await fetch(`/api/admin/areas/${areaId}/mesas`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        if (!res.ok) {
            showToast(data.message || 'Error al generar mesas', 'error');
            return false;
        }

        showToast(data.message || 'Mesa(s) generada(s) exitosamente');
        closeTableModal();
        await loadAreas();
    } catch (err) {
        showToast('Error al conectar con el servidor', 'error');
    }
    return false;
}

async function toggleTableStatus(id, currentStatus) {
    try {
        const res = await fetch(`/api/admin/mesas/${id}/status`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ is_active: !currentStatus })
        });
        const data = await res.json();
        if (!res.ok) {
            showToast(data.message || 'No se puede modificar el estado de la mesa', 'error');
            return;
        }
        showToast(data.message);
        await loadAreas();
    } catch (err) {
        showToast('Error de conexión', 'error');
    }
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>
@endsection
