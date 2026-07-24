<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('insumos')) {
            Schema::create('insumos', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('name');
                $table->string('category', 50)->default('General'); // ej. Carnes, Lácteos, Verduras, Especias, Empaques
                $table->string('unit_of_measure', 20)->default('pza'); // kg, gr, lt, ml, pza, cja
                $table->decimal('stock_quantity', 12, 3)->default(0.000);
                $table->decimal('min_stock_alert', 12, 3)->default(5.000);
                $table->decimal('unit_cost', 10, 2)->default(0.00);
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('insumos');
    }
};
