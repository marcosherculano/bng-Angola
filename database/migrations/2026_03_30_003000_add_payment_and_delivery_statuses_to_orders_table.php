<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('orders') || ! Schema::hasColumn('orders', 'status')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `orders` MODIFY `status` ENUM('pending','paid','schedule_requested','schedule_confirmed','ready_for_pickup','delivery_requested','delivery_in_progress','delivered','cancelled') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('orders') || ! Schema::hasColumn('orders', 'status')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `orders` MODIFY `status` ENUM('pending','schedule_requested','schedule_confirmed','ready_for_pickup','delivered','cancelled') NOT NULL DEFAULT 'pending'");
        }
    }
};
