<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\RestaurantTable;
use App\Models\ReservationNotification;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReservationController extends Controller
{
    /**
     * Render Admin Reservations Blade View
     */
    public function index()
    {
        return view('admin.reservas.index');
    }

    /**
     * GET /api/admin/reservas
     * Returns list of reservations & statistical summary
     */
    public function getReservations()
    {
        $reservations = Reservation::with(['user', 'mesa.serviceArea', 'sucursal'])
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total' => $reservations->count(),
            'pending' => $reservations->whereIn('status', ['pendiente', 'pending'])->count(),
            'accepted' => $reservations->whereIn('status', ['aceptada', 'confirmed', 'accepted'])->count(),
            'rejected' => $reservations->whereIn('status', ['rechazada', 'rejected'])->count(),
        ];

        return response()->json([
            'reservations' => $reservations,
            'stats' => $stats
        ]);
    }

    /**
     * POST /api/admin/reservas/{id}/accept
     * Verifies occupancy & approves client reservation
     */
    public function acceptReservation(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);

        $data = $request->validate([
            'mesa_id' => 'nullable|exists:restaurant_tables,id',
            'admin_notes' => 'nullable|string|max:500',
            'force' => 'nullable|boolean'
        ]);

        $mesaId = $data['mesa_id'] ?? $reservation->mesa_id;

        if (!$mesaId) {
            // Assign first available table if none selected
            $availableTable = RestaurantTable::where('is_active', true)->first();
            $mesaId = $availableTable ? $availableTable->id : null;
        }

        // 1. Verificación de Ocupación / Sobrecupo en el mismo horario y mesa
        if ($mesaId && empty($data['force'])) {
            $table = RestaurantTable::find($mesaId);
            
            // Check if another accepted reservation exists for the same table on the same date within a 2-hour window
            $resTime = strtotime($reservation->reservation_time);
            $conflict = Reservation::where('id', '!=', $reservation->id)
                ->where('mesa_id', $mesaId)
                ->where('reservation_date', $reservation->reservation_date)
                ->whereIn('status', ['aceptada', 'confirmed', 'accepted'])
                ->get()
                ->first(function ($existing) use ($resTime) {
                    $existingTime = strtotime($existing->reservation_time);
                    return abs($existingTime - $resTime) < (2 * 3600); // within 2 hours
                });

            if ($conflict) {
                return response()->json([
                    'success' => false,
                    'conflict' => true,
                    'message' => "⚠️ ¡Conflicto de Ocupación! La {$table->name} ya se encuentra ACEPTADA para el cliente '{$conflict->customer_name}' a las {$conflict->reservation_time} hrs.",
                    'conflict_reservation' => $conflict
                ], 422);
            }
        }

        // 2. Transacción de aprobación
        return DB::transaction(function () use ($reservation, $mesaId, $data) {
            $reservation->status = 'aceptada';
            $reservation->mesa_id = $mesaId;
            if (!empty($data['admin_notes'])) {
                $reservation->admin_notes = $data['admin_notes'];
            }
            $reservation->save();

            $mesa = RestaurantTable::find($mesaId);
            $mesaName = $mesa ? $mesa->name : 'Mesa Asignada';

            // 3. Crear Notificación al Cliente
            $message = "🎉 ¡Excelente noticia! Tu reservación #{$reservation->reservation_code} para {$reservation->party_size} persona(s) el {$reservation->reservation_date} a las {$reservation->reservation_time} hrs ha sido ACEPTADA en {$mesaName}.";

            ReservationNotification::create([
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->id,
                'type' => 'accepted',
                'title' => '🎉 ¡Reservación Aceptada!',
                'message' => $message,
                'is_read' => false
            ]);

            // 4. Registro de Auditoría
            AuditLog::record(
                'Aprobación de Reservación',
                'Reservas',
                [
                    'reservation_code' => $reservation->reservation_code,
                    'customer' => $reservation->customer_name,
                    'table' => $mesaName,
                    'date' => $reservation->reservation_date,
                    'time' => $reservation->reservation_time
                ]
            );

            return response()->json([
                'success' => true,
                'message' => "La reservación #{$reservation->reservation_code} ha sido ACEPTADA exitosamente y se notificó al cliente.",
                'reservation' => $reservation->load(['user', 'mesa.serviceArea'])
            ]);
        });
    }

    /**
     * POST /api/admin/reservas/{id}/reject
     * Rejects client reservation & notifies customer
     */
    public function rejectReservation(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);

        $data = $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        $reason = !empty($data['reason']) ? $data['reason'] : 'Sobrecupo o falta de disponibilidad en el horario seleccionado.';

        return DB::transaction(function () use ($reservation, $reason) {
            $reservation->status = 'rechazada';
            $reservation->admin_notes = $reason;
            $reservation->save();

            // Crear Notificación al Cliente
            $message = "❌ Tu solicitud de reservación #{$reservation->reservation_code} para el {$reservation->reservation_date} a las {$reservation->reservation_time} hrs ha sido RECHAZADA. Motivo: {$reason}";

            ReservationNotification::create([
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->id,
                'type' => 'rejected',
                'title' => '❌ Reservación Rechazada',
                'message' => $message,
                'is_read' => false
            ]);

            // Auditoría
            AuditLog::record(
                'Rechazo de Reservación',
                'Reservas',
                [
                    'reservation_code' => $reservation->reservation_code,
                    'customer' => $reservation->customer_name,
                    'reason' => $reason
                ]
            );

            return response()->json([
                'success' => true,
                'message' => "La reservación #{$reservation->reservation_code} ha sido RECHAZADA y se notificó al cliente.",
                'reservation' => $reservation->load(['user', 'mesa.serviceArea'])
            ]);
        });
    }

    /**
     * POST /api/reservaciones
     * Client endpoint to request a new reservation
     */
    public function storeClientReservation(Request $request)
    {
        $data = $request->validate([
            'customer_name' => 'required|string|max:100',
            'customer_email' => 'required|email|max:100',
            'customer_phone' => 'required|string|max:30',
            'reservation_date' => 'required|date|after_or_equal:today',
            'reservation_time' => 'required|string',
            'party_size' => 'required|integer|min:1|max:30',
            'mesa_id' => 'nullable|exists:restaurant_tables,id',
            'notes' => 'nullable|string|max:500'
        ]);

        $code = 'RES-' . date('Y') . '-' . strtoupper(Str::random(5));

        $reservation = Reservation::create([
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'customer_phone' => $data['customer_phone'],
            'reservation_date' => $data['reservation_date'],
            'reservation_time' => $data['reservation_time'],
            'party_size' => $data['party_size'],
            'mesa_id' => $data['mesa_id'] ?? null,
            'notes' => $data['notes'] ?? null,
            'reservation_code' => $code,
            'status' => 'pendiente',
            'user_id' => auth()->id()
        ]);

        AuditLog::record(
            'Nueva Solicitud de Reservación',
            'Reservas',
            [
                'reservation_code' => $code,
                'customer_name' => $data['customer_name'],
                'date' => $data['reservation_date'],
                'time' => $data['reservation_time']
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "¡Tu solicitud de reservación #{$code} ha sido enviada exitosamente! Te notificaremos tan pronto el restaurante confirme tu mesa.",
            'reservation' => $reservation
        ], 201);
    }

    /**
     * GET /api/cliente/notificaciones
     * Client endpoint to retrieve status notifications
     */
    public function getClientNotifications()
    {
        $userId = auth()->id();
        $notifications = ReservationNotification::with('reservation')
            ->where(function ($query) use ($userId) {
                if ($userId) {
                    $query->where('user_id', $userId)->orWhereNull('user_id');
                }
            })
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $notifications->where('is_read', false)->count()
        ]);
    }
}
