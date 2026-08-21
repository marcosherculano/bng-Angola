<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->unique();

            $table->string('partner', 60)->nullable();
            $table->string('external_id', 120)->nullable();

            $table->string('driver_name', 120)->nullable();
            $table->string('driver_phone', 60)->nullable();

            $table->decimal('estimated_price', 12, 2)->nullable();
            $table->string('currency', 10)->default('Kz');

            $table->text('notes')->nullable();

            $table->string('status', 40)->default('draft');

            $table->timestamp('requested_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->json('raw_payload')->nullable();

            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_deliveries');
    }
};
