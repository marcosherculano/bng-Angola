<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicine_transfers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('medicine_id')->constrained('medicines')->restrictOnDelete();

            $table->string('from_type', 50);
            $table->unsignedBigInteger('from_id');

            $table->string('to_type', 50);
            $table->unsignedBigInteger('to_id');

            $table->unsignedInteger('quantity');
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['from_type', 'from_id', 'created_at'], 'mt_from_idx');
            $table->index(['to_type', 'to_id', 'created_at'], 'mt_to_idx');
            $table->index(['medicine_id', 'created_at'], 'mt_medicine_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicine_transfers');
    }
};
