<?php

namespace Database\Seeders;

use App\Models\Insumo;
use Illuminate\Database\Seeder;

class InsumoSeeder extends Seeder
{
    public function run(): void
    {
        $defaultInsumos = [
            [
                'code' => 'INS-CAR-001',
                'name' => 'Arrachera Marinada de Res',
                'category' => 'Carnes',
                'unit_of_measure' => 'kg',
                'stock_quantity' => 15.500,
                'min_stock_alert' => 5.000,
                'unit_cost' => 180.00,
                'description' => 'Carne de res magra marinda para tacos y platillos fuertes.',
                'is_active' => true,
            ],
            [
                'code' => 'INS-CAR-002',
                'name' => 'Carne de Res Molida 80/20 (Hamburguesa)',
                'category' => 'Carnes',
                'unit_of_measure' => 'kg',
                'stock_quantity' => 12.000,
                'min_stock_alert' => 4.000,
                'unit_cost' => 140.00,
                'description' => 'Molida de res especial para medallones de hamburguesa Tixco.',
                'is_active' => true,
            ],
            [
                'code' => 'INS-LAC-001',
                'name' => 'Queso Gouda Fundible',
                'category' => 'Lácteos',
                'unit_of_measure' => 'kg',
                'stock_quantity' => 8.200,
                'min_stock_alert' => 2.500,
                'unit_cost' => 125.00,
                'description' => 'Queso madurado de fácil fundición para hamburguesas y quesadillas.',
                'is_active' => true,
            ],
            [
                'code' => 'INS-LAC-002',
                'name' => 'Leche Entera Pasteurizada',
                'category' => 'Lácteos',
                'unit_of_measure' => 'lt',
                'stock_quantity' => 24.000,
                'min_stock_alert' => 6.000,
                'unit_cost' => 22.50,
                'description' => 'Leche fresca para capuchinos, lattes y repostería.',
                'is_active' => true,
            ],
            [
                'code' => 'INS-BEB-001',
                'name' => 'Grano de Café Altura Tixco',
                'category' => 'Bebidas Base',
                'unit_of_measure' => 'kg',
                'stock_quantity' => 10.000,
                'min_stock_alert' => 3.000,
                'unit_cost' => 210.00,
                'description' => 'Café tostado artesanal 100% arábica de Chiapas.',
                'is_active' => true,
            ],
            [
                'code' => 'INS-VER-001',
                'name' => 'Jitomate Saladette Fresco',
                'category' => 'Verduras',
                'unit_of_measure' => 'kg',
                'stock_quantity' => 6.000,
                'min_stock_alert' => 2.000,
                'unit_cost' => 24.00,
                'description' => 'Jitomate rojo maduro para ensaladas, aderezos y guarniciones.',
                'is_active' => true,
            ],
            [
                'code' => 'INS-PAN-001',
                'name' => 'Pan Brioche Artesanal (Caja 24 pza)',
                'category' => 'Panadería',
                'unit_of_measure' => 'pza',
                'stock_quantity' => 48.000,
                'min_stock_alert' => 12.000,
                'unit_cost' => 8.50,
                'description' => 'Pan suave horneado diariamente para hamburguesas.',
                'is_active' => true,
            ],
            [
                'code' => 'INS-EMP-001',
                'name' => 'Empaque Biodegradable para Llevar',
                'category' => 'Empaques',
                'unit_of_measure' => 'pza',
                'stock_quantity' => 150.000,
                'min_stock_alert' => 30.000,
                'unit_cost' => 4.20,
                'description' => 'Contenedores ecológicos de fécula de maíz para pedidos to-go.',
                'is_active' => true,
            ],
        ];

        foreach ($defaultInsumos as $data) {
            Insumo::updateOrCreate(
                ['code' => $data['code']],
                $data
            );
        }
    }
}
