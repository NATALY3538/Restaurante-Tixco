<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(5);
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity_delta');
            $table->string('type')->default('adjustment'); // purchase, sale, adjustment, damage
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('product_inventories');
    }
};
