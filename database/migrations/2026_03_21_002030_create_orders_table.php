<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pharmacy_id')->constrained('pharmacies')->cascadeOnDelete();

            $table->enum('status', [
                'pending',
                'schedule_requested',
                'schedule_confirmed',
                'ready_for_pickup',
                'delivered',
                'cancelled',
            ])->default('pending');

            $table->enum('pickup_method', ['pickup', 'external_transport'])->nullable();
            $table->string('external_transport_name')->nullable();

            $table->decimal('total_price', 10, 2)->default(0);
            $table->longText('customer_notes')->nullable();

            $table->timestamp('scheduled_pickup_at')->nullable();
            $table->text('schedule_notes')->nullable();
            $table->timestamp('schedule_confirmed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->timestamps();

            $table->index(['client_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
