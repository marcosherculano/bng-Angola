<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('database_backups', function (Blueprint $table) {
            $table->id();

            $table->string('filename', 255);
            $table->string('disk', 50)->default('local');
            $table->string('path', 500);
            $table->unsignedBigInteger('size_bytes')->nullable();

            $table->string('status', 30)->default('pending');
            $table->text('error_message')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('restored_by')->nullable();
            $table->timestamp('restored_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['created_by']);
            $table->index(['restored_by']);

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('restored_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('database_backups');
    }
};
