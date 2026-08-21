<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicine_inventories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('medicine_id')->constrained('medicines')->cascadeOnDelete();

            $table->string('owner_type', 50);
            $table->unsignedBigInteger('owner_id');

            $table->decimal('price', 10, 2);
            $table->unsignedInteger('stock')->default(0);
            $table->boolean('is_available')->default(true);

            $table->timestamps();

            $table->unique(['owner_type', 'owner_id', 'medicine_id'], 'mi_owner_medicine_unique');
            $table->index(['owner_type', 'owner_id'], 'mi_owner_idx');
            $table->index(['medicine_id'], 'mi_medicine_idx');
            $table->index(['owner_type', 'owner_id', 'stock'], 'mi_owner_stock_idx');
        });

        if (Schema::hasTable('medicines')) {
            DB::statement("INSERT INTO medicine_inventories (medicine_id, owner_type, owner_id, price, stock, is_available, created_at, updated_at)
                SELECT m.id, 'pharmacy', m.pharmacy_id, m.price, m.stock, m.is_available, NOW(), NOW()
                FROM medicines m
                LEFT JOIN medicine_inventories mi
                    ON mi.medicine_id = m.id
                    AND mi.owner_type = 'pharmacy'
                    AND mi.owner_id = m.pharmacy_id
                WHERE mi.id IS NULL");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('medicine_inventories');
    }
};
