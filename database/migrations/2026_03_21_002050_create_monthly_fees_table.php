<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_fees', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pharmacy_id')->constrained('pharmacies')->cascadeOnDelete();

            $table->date('cycle_start');
            $table->date('cycle_end');
            $table->timestamp('due_at');

            $table->decimal('amount', 10, 2);

            $table->enum('status', ['pending', 'submitted', 'approved', 'rejected', 'overdue'])
                ->default('pending');

            $table->string('proof_path')->nullable();
            $table->timestamp('submitted_at')->nullable();

            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            $table->unique(['pharmacy_id', 'cycle_start']);
            $table->index(['status', 'due_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_fees');
    }
};
