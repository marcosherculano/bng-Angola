<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacies', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();

            $table->string('business_name');
            $table->string('nif', 20)->unique();
            $table->string('alvara', 50)->unique();
            $table->string('phone', 20)->nullable();
            $table->string('email', 255)->nullable();

            $table->string('province', 100);
            $table->string('city', 100)->nullable();
            $table->string('neighborhood', 150)->nullable();
            $table->string('street', 200)->nullable();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);

            $table->enum('type', ['normal', 'matrix'])->default('normal');
            $table->foreignId('matrix_id')->nullable()->constrained('pharmacies')->nullOnDelete();

            $table->boolean('is_active')->default(false);

            $table->enum('subscription_plan', ['basic', 'pro', 'premium'])->default('basic');
            $table->decimal('monthly_fee', 10, 2)->default(0);

            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('trial_starts_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();

            $table->timestamps();

            $table->index(['type', 'province', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacies');
    }
};
