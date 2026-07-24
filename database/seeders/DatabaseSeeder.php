<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Roles
        DB::table('roles')->insertOrIgnore([
            ['id' => 1, 'name' => 'admin', 'display_name' => 'Administrador', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'customer', 'display_name' => 'Comensal', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'host', 'display_name' => 'Recepción / Reservaciones', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'cashier', 'display_name' => 'Cajero', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'name' => 'kitchen', 'display_name' => 'Cocina', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Default Admin User
        User::firstOrCreate(
            ['email' => 'admin@tixco.com'],
            [
                'name' => 'Administrador Tixco',
                'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
            ]
        );

        // 2. Categories
        DB::table('categories')->insertOrIgnore([
            ['id' => 1, 'name' => 'Bebidas', 'slug' => 'bebidas', 'description' => 'Bebidas frías y calientes.', 'sort_order' => 10, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Alimentos', 'slug' => 'alimentos', 'description' => 'Platillos y alimentos preparados.', 'sort_order' => 20, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Postres', 'slug' => 'postres', 'description' => 'Postres y panadería.', 'sort_order' => 30, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'Combos', 'slug' => 'combos', 'description' => 'Paquetes y promociones.', 'sort_order' => 40, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 3. Payment Methods
        DB::table('payment_methods')->insertOrIgnore([
            ['id' => 1, 'code' => 'cash', 'name' => 'Efectivo', 'provider' => null, 'requires_reference' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'code' => 'card', 'name' => 'Tarjeta', 'provider' => 'terminal_or_gateway', 'requires_reference' => true, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'code' => 'transfer', 'name' => 'Transferencia', 'provider' => null, 'requires_reference' => true, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'code' => 'online', 'name' => 'Pago en línea', 'provider' => 'payment_gateway', 'requires_reference' => true, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'code' => 'external_platform', 'name' => 'Pago por plataforma externa', 'provider' => 'delivery_platform', 'requires_reference' => true, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 4. Delivery Platforms
        DB::table('delivery_platforms')->insertOrIgnore([
            ['id' => 1, 'name' => 'Rappi', 'code' => 'rappi', 'commission_percentage' => 0.00, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Uber Eats', 'code' => 'uber_eats', 'commission_percentage' => 0.00, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Didi Food', 'code' => 'didi_food', 'commission_percentage' => 0.00, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 5. Service Areas
        DB::table('service_areas')->insertOrIgnore([
            ['id' => 1, 'name' => 'Salón principal', 'description' => 'Área principal de mesas.', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Terraza', 'description' => 'Área exterior o semi exterior.', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Barra', 'description' => 'Asientos en barra.', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 6. Restaurant Tables
        DB::table('restaurant_tables')->insertOrIgnore([
            ['id' => 1, 'service_area_id' => 1, 'table_code' => 'M1', 'name' => 'Mesa 1', 'capacity' => 4, 'qr_token' => 'mesa1', 'status' => 'available', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'service_area_id' => 1, 'table_code' => 'M2', 'name' => 'Mesa 2', 'capacity' => 4, 'qr_token' => 'mesa2', 'status' => 'available', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'service_area_id' => 1, 'table_code' => 'M3', 'name' => 'Mesa 3', 'capacity' => 6, 'qr_token' => 'mesa3', 'status' => 'available', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'service_area_id' => 2, 'table_code' => 'T1', 'name' => 'Mesa Terraza 1', 'capacity' => 4, 'qr_token' => 'mesa4', 'status' => 'available', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'service_area_id' => 2, 'table_code' => 'T2', 'name' => 'Mesa Terraza 2', 'capacity' => 2, 'qr_token' => 'mesa5', 'status' => 'available', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'service_area_id' => 3, 'table_code' => 'B1', 'name' => 'Barra 1', 'capacity' => 1, 'qr_token' => 'barra1', 'status' => 'available', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'service_area_id' => 3, 'table_code' => 'B2', 'name' => 'Barra 2', 'capacity' => 1, 'qr_token' => 'barra2', 'status' => 'available', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 7. Modifier Groups
        DB::table('modifier_groups')->insertOrIgnore([
            ['id' => 1, 'name' => 'Tipo de Leche', 'description' => 'Selecciona la leche para tu bebida.', 'min_selection' => 0, 'max_selection' => 1, 'is_required' => false, 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Extras para Hamburguesa', 'description' => 'Añade ingredientes adicionales.', 'min_selection' => 0, 'max_selection' => 3, 'is_required' => false, 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Término de la Carne', 'description' => 'Especifica el término de cocción.', 'min_selection' => 1, 'max_selection' => 1, 'is_required' => true, 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 8. Modifiers
        DB::table('modifiers')->insertOrIgnore([
            // Milk
            ['id' => 1, 'modifier_group_id' => 1, 'name' => 'Leche Entera', 'price_delta' => 0.00, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'modifier_group_id' => 1, 'name' => 'Leche Deslactosada', 'price_delta' => 0.00, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'modifier_group_id' => 1, 'name' => 'Leche de Almendras', 'price_delta' => 10.00, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'modifier_group_id' => 1, 'name' => 'Leche de Coco', 'price_delta' => 12.00, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            // Extras
            ['id' => 5, 'modifier_group_id' => 2, 'name' => 'Queso Cheddar Extra', 'price_delta' => 15.00, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'modifier_group_id' => 2, 'name' => 'Tocino Crujiente', 'price_delta' => 20.00, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'modifier_group_id' => 2, 'name' => 'Aguacate', 'price_delta' => 15.00, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            // Meat cooking
            ['id' => 8, 'modifier_group_id' => 3, 'name' => 'Término Medio', 'price_delta' => 0.00, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9, 'modifier_group_id' => 3, 'name' => 'Tres Cuartos', 'price_delta' => 0.00, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 10, 'modifier_group_id' => 3, 'name' => 'Bien Cocido', 'price_delta' => 0.00, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 9. Products
        DB::table('products')->insertOrIgnore([
            [
                'id' => 1,
                'category_id' => 1,
                'name' => 'Café Americano',
                'slug' => 'cafe-americano',
                'description' => 'Café negro clásico preparado con granos selectos de altura.',
                'price' => 35.00,
                'image_url' => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=500&q=80',
                'estimated_preparation_minutes' => 5,
                'is_vegetarian' => true,
                'is_spicy' => false,
                'is_gluten_free' => true,
                'is_featured' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 2,
                'category_id' => 1,
                'name' => 'Capuchino Italiano',
                'slug' => 'capuchino-italiano',
                'description' => 'Café espresso con leche vaporizada y abundante espuma de leche.',
                'price' => 45.00,
                'image_url' => 'https://images.unsplash.com/photo-1572442388796-11668a67e53d?w=500&q=80',
                'estimated_preparation_minutes' => 7,
                'is_vegetarian' => true,
                'is_spicy' => false,
                'is_gluten_free' => true,
                'is_featured' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 3,
                'category_id' => 1,
                'name' => 'Té Matcha Latte',
                'slug' => 'te-matcha-latte',
                'description' => 'Matcha ceremonial japonés batido con leche caliente.',
                'price' => 55.00,
                'image_url' => 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?w=500&q=80',
                'estimated_preparation_minutes' => 6,
                'is_vegetarian' => true,
                'is_spicy' => false,
                'is_gluten_free' => true,
                'is_featured' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 4,
                'category_id' => 2,
                'name' => 'Hamburguesa Premium Tixco',
                'slug' => 'hamburguesa-premium-tixco',
                'description' => '200g de carne de res de primera, queso fundido, lechuga, jitomate y aderezo especial en pan brioche artesanal.',
                'price' => 135.00,
                'image_url' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=500&q=80',
                'estimated_preparation_minutes' => 15,
                'is_vegetarian' => false,
                'is_spicy' => false,
                'is_gluten_free' => false,
                'is_featured' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 5,
                'category_id' => 2,
                'name' => 'Tacos de Arrachera',
                'slug' => 'tacos-de-arrachera',
                'description' => 'Tres deliciosos tacos de arrachera asada al carbón en tortillas de maíz con cebolla asada y cilantro.',
                'price' => 120.00,
                'image_url' => 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?w=500&q=80',
                'estimated_preparation_minutes' => 12,
                'is_vegetarian' => false,
                'is_spicy' => true,
                'is_gluten_free' => true,
                'is_featured' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 6,
                'category_id' => 2,
                'name' => 'Ensalada César con Pollo',
                'slug' => 'ensalada-cesar-con-pollo',
                'description' => 'Lechuga orejona fresca, aderezo César de la casa, croutones de ajo, queso parmesano y pechuga de pollo a la plancha.',
                'price' => 95.00,
                'image_url' => 'https://images.unsplash.com/photo-1550304943-4f24f54ddde9?w=500&q=80',
                'estimated_preparation_minutes' => 10,
                'is_vegetarian' => false,
                'is_spicy' => false,
                'is_gluten_free' => false,
                'is_featured' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 7,
                'category_id' => 3,
                'name' => 'Pastel de Fresa Especial',
                'slug' => 'pastel-de-fresa-especial',
                'description' => 'Esponjoso pastel de vainilla relleno de crema chantilly fresca y deliciosas fresas selectas.',
                'price' => 50.00,
                'image_url' => 'https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=500&q=80',
                'estimated_preparation_minutes' => 5,
                'is_vegetarian' => true,
                'is_spicy' => false,
                'is_gluten_free' => false,
                'is_featured' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 8,
                'category_id' => 3,
                'name' => 'Brownie de Chocolate con Helado',
                'slug' => 'brownie-de-chocolate-con-helado',
                'description' => 'Brownie de chocolate fudge calientito acompañado de una bola de helado de vainilla y jarabe de chocolate.',
                'price' => 55.00,
                'image_url' => 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=500&q=80',
                'estimated_preparation_minutes' => 6,
                'is_vegetarian' => true,
                'is_spicy' => false,
                'is_gluten_free' => false,
                'is_featured' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 9,
                'category_id' => 4,
                'name' => 'Combo Desayuno Tixco',
                'slug' => 'combo-desayuno-tixco',
                'description' => 'Café Americano o Capuchino acompañado de un Sándwich Club clásico y papas fritas.',
                'price' => 145.00,
                'image_url' => 'https://images.unsplash.com/photo-1525351484163-7529414344d8?w=500&q=80',
                'estimated_preparation_minutes' => 15,
                'is_vegetarian' => false,
                'is_spicy' => false,
                'is_gluten_free' => false,
                'is_featured' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // 10. Product Modifier Group (Many to many join table)
        DB::table('product_modifier_group')->insertOrIgnore([
            ['product_id' => 1, 'modifier_group_id' => 1],
            ['product_id' => 2, 'modifier_group_id' => 1],
            ['product_id' => 3, 'modifier_group_id' => 1],
            ['product_id' => 4, 'modifier_group_id' => 2],
            ['product_id' => 4, 'modifier_group_id' => 3],
            ['product_id' => 5, 'modifier_group_id' => 3],
        ]);

        // 11. Initial Inventories
        DB::table('product_inventories')->insertOrIgnore([
            ['id' => 1, 'product_id' => 1, 'stock' => 50, 'min_stock' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'product_id' => 2, 'stock' => 30, 'min_stock' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'product_id' => 3, 'stock' => 40, 'min_stock' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'product_id' => 4, 'stock' => 25, 'min_stock' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'product_id' => 5, 'stock' => 60, 'min_stock' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'product_id' => 6, 'stock' => 35, 'min_stock' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'product_id' => 7, 'stock' => 15, 'min_stock' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8, 'product_id' => 8, 'stock' => 20, 'min_stock' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9, 'product_id' => 9, 'stock' => 10, 'min_stock' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
