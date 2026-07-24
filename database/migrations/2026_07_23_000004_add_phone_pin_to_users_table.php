<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 30)->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'pin_code')) {
                $table->string('pin_code', 10)->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('pin_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['phone', 'pin_code', 'is_active'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
