<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_branches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('matrix_id')->constrained('pharmacies')->cascadeOnDelete();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();

            $table->string('branch_name');
            $table->string('nif', 20)->nullable();
            $table->string('alvara', 50)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 255)->nullable();

            $table->string('province', 100);
            $table->string('city', 100)->nullable();
            $table->string('neighborhood', 150)->nullable();
            $table->string('street', 200)->nullable();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['matrix_id', 'province']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_branches');
    }
};
