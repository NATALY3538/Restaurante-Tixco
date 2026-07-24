@extends('layouts.app')

@section('title', 'Reservar Mesa')

@section('content')
<div class="container">
    <!-- ═══ PAGE HEADER ═══ -->
    <div class="page-header">
        <h1>📅 Reserva tu Mesa</h1>
        <p>Asegura tu lugar y disfruta de una experiencia gastronómica inolvidable</p>
    </div>

    <!-- ═══ RESERVATION FORM ═══ -->
    <div class="reservation-form">
        <div class="card">
            <form id="reservationForm" onsubmit="submitReservation(event)">
                <div class="form-group">
                    <label class="form-label" for="resName">Nombre completo</label>
                    <input type="text" id="resName" placeholder="Tu nombre completo" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="resPhone">Teléfono</label>
                        <input type="tel" id="resPhone" placeholder="(555) 123-4567" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="resEmail">Correo electrónico</label>
                        <input type="email" id="resEmail" placeholder="correo@ejemplo.com">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="resDate">Fecha</label>
                        <input type="date" id="resDate" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="resTime">Hora</label>
                        <input type="time" id="resTime" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="resGuests">Número de personas</label>
                    <input type="number" id="resGuests" min="1" max="20" value="2" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="resNotes">Notas especiales</label>
                    <textarea id="resNotes" rows="3" placeholder="Mesa cerca de ventana, silla para bebé, celebración de cumpleaños..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg" id="btnReservation">
                    📅 Solicitar Reservación
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('resDate').setAttribute('min', today);

    // Autofill user info if exists
    try {
        const u = JSON.parse(localStorage.getItem('tixco_user'));
        if (u) {
            document.getElementById('resName').value = u.nombre || '';
            document.getElementById('resPhone').value = u.telefono || '';
            document.getElementById('resEmail').value = u.correo || '';
        }
    } catch {}
});

async function submitReservation(e) {
    e.preventDefault();

    const name = document.getElementById('resName').value.trim();
    const phone = document.getElementById('resPhone').value.trim();
    const email = document.getElementById('resEmail').value.trim();
    const date = document.getElementById('resDate').value;
    const time = document.getElementById('resTime').value;
    const guests = parseInt(document.getElementById('resGuests').value);
    const notes = document.getElementById('resNotes').value.trim();

    if (!name || !phone || !date || !time) {
        Toast.error('Por favor completa todos los campos obligatorios');
        return;
    }

    const body = {
        customerName: name,
        customerPhone: phone,
        customerEmail: email,
        reservationDate: date,
        reservationTime: time,
        partySize: guests,
        notes: notes
    };

    // Save profile to localStorage for future use
    localStorage.setItem('tixco_user', JSON.stringify({
        nombre: name,
        telefono: phone,
        correo: email
    }));

    const btn = document.getElementById('btnReservation');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner" style="width:20px;height:20px;border-width:2px;"></span> Enviando...';

    try {
        await apiFetch('/reservations', {
            method: 'POST',
            body: JSON.stringify(body)
        });
        Toast.success('🎉 Tu reservación fue recibida y está confirmada.');
        document.getElementById('reservationForm').reset();
    } catch (err) {
        Toast.error(err.message || 'Error al enviar la reservación');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '📅 Solicitar Reservación';
    }
}
</script>
@endsection
