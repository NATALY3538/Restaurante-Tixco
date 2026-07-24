<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('status', 50)->default('pendiente')->change();
            
            if (!Schema::hasColumn('reservations', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('customer_id');
            }
            if (!Schema::hasColumn('reservations', 'mesa_id')) {
                $table->unsignedBigInteger('mesa_id')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('reservations', 'sucursal_id')) {
                $table->unsignedBigInteger('sucursal_id')->nullable()->after('mesa_id');
            }
            if (!Schema::hasColumn('reservations', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('notes');
            }
        });

        if (!Schema::hasTable('reservation_notifications')) {
            Schema::create('reservation_notifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('reservation_id');
                $table->string('type')->default('info'); // accepted, rejected, info
                $table->string('title');
                $table->text('message');
                $table->boolean('is_read')->default(false);
                $table->timestamps();

                $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_notifications');
    }
};
