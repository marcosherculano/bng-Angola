<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pharmacy_id')->constrained('pharmacies')->cascadeOnDelete();

            $table->string('name', 200);
            $table->string('barcode', 50)->nullable();
            $table->string('category', 100)->nullable();
            $table->longText('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('stock');
            $table->boolean('requires_prescription')->default(false);
            $table->string('image_path')->nullable();
            $table->boolean('is_available')->default(true);

            $table->timestamps();

            $table->index(['name']);
            $table->index(['pharmacy_id', 'stock']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
