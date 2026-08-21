<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('dados_bancarios')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `dados_bancarios` MODIFY `numero_conta` VARCHAR(80) NULL");
            DB::statement("ALTER TABLE `dados_bancarios` MODIFY `iban` VARCHAR(80) NOT NULL");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('dados_bancarios')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `dados_bancarios` MODIFY `numero_conta` VARCHAR(80) NOT NULL");
            DB::statement("ALTER TABLE `dados_bancarios` MODIFY `iban` VARCHAR(80) NULL");
        }
    }
};
