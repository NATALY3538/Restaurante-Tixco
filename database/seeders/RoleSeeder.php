<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $defaultRoles = [
            [
                'name' => 'admin',
                'slug' => 'admin',
                'display_name' => 'Administrador / Gerente',
                'description' => 'Acceso total al sistema ERP, módulos de inventario, auditoría, salón y configuración.',
                'permissions_json' => ['salon', 'comandas', 'cocina', 'caja', 'inventario', 'mermas', 'bitacora', 'roles'],
                'is_active' => true,
            ],
            [
                'name' => 'host',
                'slug' => 'recepcionista',
                'display_name' => 'Recepcionista / Hostess',
                'description' => 'Gestión de reservaciones, asignación de mesas a clientes y recepción.',
                'permissions_json' => ['salon', 'reservaciones'],
                'is_active' => true,
            ],
            [
                'name' => 'waiter',
                'slug' => 'mesero-capitan',
                'display_name' => 'Mesero / Capitán de Meseros',
                'description' => 'Toma de comandas, consulta de plano 2D de salón, mover/fusionar mesas.',
                'permissions_json' => ['salon', 'comandas'],
                'is_active' => true,
            ],
            [
                'name' => 'kitchen',
                'slug' => 'chef-cocinero',
                'display_name' => 'Chef / Cocinero',
                'description' => 'Acceso al KDS (Pantalla de Cocina) para preparación y despacho de platillos.',
                'permissions_json' => ['cocina'],
                'is_active' => true,
            ],
            [
                'name' => 'cashier',
                'slug' => 'cajero',
                'display_name' => 'Cajero / Cobranza',
                'description' => 'Módulo de cobro, división de cuentas (Split Bill), cortes de caja y facturación.',
                'permissions_json' => ['caja', 'comandas'],
                'is_active' => true,
            ],
            [
                'name' => 'barman',
                'slug' => 'barman',
                'display_name' => 'Barman / Bartender',
                'description' => 'Gestión de barra de bebidas, comandas de bar y stock de cristalería.',
                'permissions_json' => ['cocina', 'inventario'],
                'is_active' => true,
            ],
        ];

        foreach ($defaultRoles as $rData) {
            Role::updateOrCreate(
                ['name' => $rData['name']],
                $rData
            );
        }

        // Link admin user to Admin role
        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            User::where('email', 'admin@tixco.com')->update(['role_id' => $adminRole->id]);
        }
    }
}
