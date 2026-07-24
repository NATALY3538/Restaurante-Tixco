<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waste_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->decimal('quantity', 10, 2);
            $table->decimal('cost_unit', 10, 2)->default(0.00);
            $table->decimal('cost_total', 10, 2)->default(0.00);
            $table->string('reason', 50); // caducidad, accidente, error_preparacion, muestra, otro
            $table->text('notes')->nullable();
            $table->string('registered_by')->default('Admin');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_records');
    }
};
