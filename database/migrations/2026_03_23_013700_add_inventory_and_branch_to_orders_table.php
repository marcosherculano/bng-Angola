<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'medicine_inventory_id')) {
                $table->foreignId('medicine_inventory_id')
                    ->nullable()
                    ->after('pharmacy_id')
                    ->constrained('medicine_inventories')
                    ->nullOnDelete();
                $table->index(['medicine_inventory_id']);
            }

            if (! Schema::hasColumn('orders', 'pharmacy_branch_id')) {
                $table->foreignId('pharmacy_branch_id')
                    ->nullable()
                    ->after('medicine_inventory_id')
                    ->constrained('pharmacy_branches')
                    ->nullOnDelete();
                $table->index(['pharmacy_branch_id']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'pharmacy_branch_id')) {
                $table->dropConstrainedForeignId('pharmacy_branch_id');
            }

            if (Schema::hasColumn('orders', 'medicine_inventory_id')) {
                $table->dropConstrainedForeignId('medicine_inventory_id');
            }
        });
    }
};
