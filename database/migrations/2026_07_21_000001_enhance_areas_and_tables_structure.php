<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_areas', function (Blueprint $table) {
            if (!Schema::hasColumn('service_areas', 'max_tables')) {
                $table->integer('max_tables')->default(0)->after('description');
            }
            if (!Schema::hasColumn('service_areas', 'max_capacity')) {
                $table->integer('max_capacity')->default(0)->after('max_tables');
            }
            if (!Schema::hasColumn('service_areas', 'allows_smoking')) {
                $table->boolean('allows_smoking')->default(false)->after('max_capacity');
            }
            if (!Schema::hasColumn('service_areas', 'is_vip')) {
                $table->boolean('is_vip')->default(false)->after('allows_smoking');
            }
        });

        Schema::table('restaurant_tables', function (Blueprint $table) {
            if (!Schema::hasColumn('restaurant_tables', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_areas', function (Blueprint $table) {
            $table->dropColumn(['max_tables', 'max_capacity', 'allows_smoking', 'is_vip']);
        });

        Schema::table('restaurant_tables', function (Blueprint $table) {
            $table->dropColumn(['is_active']);
        });
    }
};
