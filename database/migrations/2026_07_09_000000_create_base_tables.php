<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('display_name')->nullable();
                $table->string('slug')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->json('permissions_json')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('category_id');
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->decimal('price', 10, 2);
                $table->decimal('cost', 10, 2)->nullable();
                $table->decimal('costo_produccion', 10, 2)->nullable();
                $table->string('image_url')->nullable();
                $table->integer('estimated_preparation_minutes')->default(10);
                $table->boolean('is_vegetarian')->default(false);
                $table->boolean('is_spicy')->default(false);
                $table->boolean('is_gluten_free')->default(false);
                $table->boolean('is_featured')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('product_images')) {
            Schema::create('product_images', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->string('image_url');
                $table->string('alt_text')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_primary')->default(false);
                $table->timestamps();

                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('payment_methods')) {
            Schema::create('payment_methods', function (Blueprint $table) {
                $table->id();
                $table->string('code');
                $table->string('name');
                $table->string('provider')->nullable();
                $table->boolean('requires_reference')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('delivery_platforms')) {
            Schema::create('delivery_platforms', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code');
                $table->decimal('commission_percentage', 5, 2)->default(0.00);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('modifier_groups')) {
            Schema::create('modifier_groups', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->integer('min_selection')->default(0);
                $table->integer('max_selection')->default(1);
                $table->boolean('is_required')->default(false);
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('modifiers')) {
            Schema::create('modifiers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('modifier_group_id');
                $table->string('name');
                $table->decimal('price_delta', 10, 2)->default(0.00);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('modifier_group_id')->references('id')->on('modifier_groups')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('product_modifier_group')) {
            Schema::create('product_modifier_group', function (Blueprint $table) {
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('modifier_group_id');

                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                $table->foreign('modifier_group_id')->references('id')->on('modifier_groups')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('service_areas')) {
            Schema::create('service_areas', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->integer('max_tables')->default(0);
                $table->integer('max_capacity')->default(0);
                $table->boolean('allows_smoking')->default(false);
                $table->boolean('is_vip')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('restaurant_tables')) {
            Schema::create('restaurant_tables', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('service_area_id');
                $table->string('table_code');
                $table->string('name');
                $table->integer('capacity')->default(4);
                $table->string('shape', 20)->default('square');
                $table->string('qr_token')->unique()->nullable();
                $table->string('status', 20)->default('available');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('service_area_id')->references('id')->on('service_areas')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->unsignedBigInteger('restaurant_table_id')->nullable();
                $table->string('order_number')->unique();
                $table->string('order_type')->default('dine_in'); // dine_in, takeout, delivery
                $table->string('status', 30)->default('pending'); // pending, confirmed, preparing, ready, delivered, cancelled
                $table->string('payment_status', 30)->default('unpaid');
                $table->decimal('subtotal', 10, 2)->default(0.00);
                $table->decimal('modifiers_total', 10, 2)->default(0.00);
                $table->decimal('delivery_fee', 10, 2)->default(0.00);
                $table->decimal('discount_total', 10, 2)->default(0.00);
                $table->decimal('tax_total', 10, 2)->default(0.00);
                $table->decimal('total', 10, 2)->default(0.00);
                $table->text('customer_notes')->nullable();
                $table->timestamp('requested_at')->nullable();
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamps();

                $table->foreign('restaurant_table_id')->references('id')->on('restaurant_tables')->onDelete('set null');
            });
        }

        if (!Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id');
                $table->unsignedBigInteger('product_id')->nullable();
                $table->string('product_name');
                $table->decimal('quantity', 8, 2)->default(1);
                $table->decimal('unit_price', 10, 2)->default(0.00);
                $table->decimal('modifiers_total', 10, 2)->default(0.00);
                $table->decimal('total', 10, 2)->default(0.00);
                $table->string('special_note')->nullable();
                $table->string('special_request_status')->nullable();
                $table->timestamps();

                $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('order_item_modifiers')) {
            Schema::create('order_item_modifiers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_item_id');
                $table->unsignedBigInteger('modifier_id')->nullable();
                $table->string('modifier_name');
                $table->decimal('unit_price_delta', 10, 2)->default(0.00);
                $table->timestamps();

                $table->foreign('order_item_id')->references('id')->on('order_items')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id');
                $table->unsignedBigInteger('payment_method_id')->nullable();
                $table->string('status', 30)->default('pending');
                $table->decimal('amount', 10, 2);
                $table->string('currency', 10)->default('MXN');
                $table->string('reference')->nullable();
                $table->string('provider_payment_id')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();

                $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('reservations')) {
            Schema::create('reservations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('mesa_id')->nullable();
                $table->unsignedBigInteger('sucursal_id')->nullable();
                $table->string('reservation_code')->unique();
                $table->string('customer_name');
                $table->string('customer_phone')->nullable();
                $table->string('customer_email')->nullable();
                $table->date('reservation_date');
                $table->time('reservation_time');
                $table->integer('party_size')->default(2);
                $table->string('status', 50)->default('pendiente');
                $table->text('notes')->nullable();
                $table->text('admin_notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('reservation_tables')) {
            Schema::create('reservation_tables', function (Blueprint $table) {
                $table->unsignedBigInteger('reservation_id');
                $table->unsignedBigInteger('restaurant_table_id');
                $table->timestamps();

                $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('cascade');
                $table->foreign('restaurant_table_id')->references('id')->on('restaurant_tables')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_tables');
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_item_modifiers');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('restaurant_tables');
        Schema::dropIfExists('service_areas');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('product_modifier_group');
        Schema::dropIfExists('modifiers');
        Schema::dropIfExists('modifier_groups');
        Schema::dropIfExists('delivery_platforms');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('users');
    }
};
