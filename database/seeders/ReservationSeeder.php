<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use App\Models\Sucursal;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        $table1 = RestaurantTable::first();
        $sucursal1 = Sucursal::first();

        Reservation::firstOrCreate(
            ['reservation_code' => 'RES-2026-001'],
            [
                'customer_name' => 'Carlos Mendoza',
                'customer_email' => 'carlos.mendoza@gmail.com',
                'customer_phone' => '555-123-4567',
                'reservation_date' => date('Y-m-d', strtotime('+1 day')),
                'reservation_time' => '14:30:00',
                'party_size' => 4,
                'mesa_id' => $table1 ? $table1->id : null,
                'sucursal_id' => $sucursal1 ? $sucursal1->id : null,
                'status' => 'pendiente',
                'notes' => 'Mesa cerca de la ventana si es posible. Celebración de cumpleaños.'
            ]
        );

        Reservation::firstOrCreate(
            ['reservation_code' => 'RES-2026-002'],
            [
                'customer_name' => 'Sofía Ramírez',
                'customer_email' => 'sofia.ramirez@hotmail.com',
                'customer_phone' => '555-987-6543',
                'reservation_date' => date('Y-m-d', strtotime('+2 days')),
                'reservation_time' => '20:00:00',
                'party_size' => 2,
                'mesa_id' => $table1 ? $table1->id : null,
                'sucursal_id' => $sucursal1 ? $sucursal1->id : null,
                'status' => 'aceptada',
                'notes' => 'Cena romántica de aniversario.'
            ]
        );

        Reservation::firstOrCreate(
            ['reservation_code' => 'RES-2026-003'],
            [
                'customer_name' => 'Roberto Gómez',
                'customer_email' => 'roberto.gomez@yahoo.com',
                'customer_phone' => '555-456-7890',
                'reservation_date' => date('Y-m-d'),
                'reservation_time' => '19:00:00',
                'party_size' => 6,
                'mesa_id' => null,
                'sucursal_id' => $sucursal1 ? $sucursal1->id : null,
                'status' => 'pendiente',
                'notes' => 'Reunión ejecutiva de negocios.'
            ]
        );
    }
}
