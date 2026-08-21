<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->unique()->constrained('orders')->cascadeOnDelete();

            $table->enum('method', ['iban', 'express', 'other'])->default('other');
            $table->string('reference', 120)->nullable();
            $table->string('proof_path', 500)->nullable();

            $table->enum('status', ['pending', 'submitted', 'confirmed', 'rejected'])->default('pending');

            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();

            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            $table->index(['status', 'method']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_payments');
    }
};
