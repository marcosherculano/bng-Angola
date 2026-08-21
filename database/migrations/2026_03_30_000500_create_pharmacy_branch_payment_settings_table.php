<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_branch_payment_settings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pharmacy_branch_id')->unique()->constrained('pharmacy_branches')->cascadeOnDelete();

            $table->boolean('is_active')->default(true);

            $table->string('bank_name', 150)->nullable();
            $table->string('account_holder', 150)->nullable();
            $table->string('account_number', 80)->nullable();

            $table->string('iban', 80)->nullable();
            $table->string('express_number', 80)->nullable();

            $table->text('instructions')->nullable();

            $table->timestamps();

            $table->index(['is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_branch_payment_settings');
    }
};
