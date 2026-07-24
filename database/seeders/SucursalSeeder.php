<?php

namespace Database\Seeders;

use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class SucursalSeeder extends Seeder
{
    public function run(): void
    {
        $defaultSucursales = [
            [
                'nombre' => 'Tixco - Sucursal Matriz Centro',
                'direccion_calle' => 'Av. Reforma #405, Col. Centro Histórico',
                'colonia_ciudad' => 'Ciudad de México, CDMX',
                'codigo_postal' => '06000',
                'telefono_contacto' => '(55) 5512-3456',
                'email_contacto' => 'matriz.centro@tixco.com',
                'rfc_identificacion_fiscal' => 'TIX260701ABC',
                'horario_apertura' => '08:00',
                'horario_cierre' => '23:00',
                'dias_operacion' => ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'],
                'is_matriz' => true,
                'is_active' => true,
            ],
            [
                'nombre' => 'Tixco - Plaza Norte',
                'direccion_calle' => 'Av. Insurgentes Norte #1250, Plaza Comercial Local 4',
                'colonia_ciudad' => 'Gustavo A. Madero, CDMX',
                'codigo_postal' => '07300',
                'telefono_contacto' => '(55) 5789-0123',
                'email_contacto' => 'plazanorte@tixco.com',
                'rfc_identificacion_fiscal' => 'TIX260701ABC',
                'horario_apertura' => '09:00',
                'horario_cierre' => '22:00',
                'dias_operacion' => ['Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'],
                'is_matriz' => false,
                'is_active' => true,
            ],
        ];

        foreach ($defaultSucursales as $sData) {
            Sucursal::updateOrCreate(
                ['nombre' => $sData['nombre']],
                $sData
            );
        }
    }
}
