<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add fields to roles table if missing
        Schema::table('roles', function (Blueprint $table) {
            if (!Schema::hasColumn('roles', 'slug')) {
                $table->string('slug', 50)->nullable()->after('name');
            }
            if (!Schema::hasColumn('roles', 'description')) {
                $table->text('description')->nullable()->after('display_name');
            }
            if (!Schema::hasColumn('roles', 'permissions_json')) {
                $table->json('permissions_json')->nullable()->after('description');
            }
            if (!Schema::hasColumn('roles', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('permissions_json');
            }
            if (!Schema::hasColumn('roles', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // 2. Add role_id to users table if missing
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role_id')) {
                $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role_id')) {
                $table->dropForeign(['role_id']);
                $table->dropColumn('role_id');
            }
        });

        Schema::table('roles', function (Blueprint $table) {
            $columns = ['slug', 'description', 'permissions_json', 'is_active', 'deleted_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('roles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
