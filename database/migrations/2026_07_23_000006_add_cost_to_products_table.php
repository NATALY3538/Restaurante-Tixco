<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'cost')) {
                $table->decimal('cost', 10, 2)->nullable()->after('price');
            }
            if (!Schema::hasColumn('products', 'costo_produccion')) {
                $table->decimal('costo_produccion', 10, 2)->nullable()->after('cost');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'costo_produccion')) {
                $table->dropColumn('costo_produccion');
            }
            if (Schema::hasColumn('products', 'cost')) {
                $table->dropColumn('cost');
            }
        });
    }
};
