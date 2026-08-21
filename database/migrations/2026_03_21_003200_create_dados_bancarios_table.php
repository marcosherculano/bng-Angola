<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dados_bancarios', function (Blueprint $table) {
            $table->id();

            $table->string('banco', 150);
            $table->string('titular', 150);
            $table->string('numero_conta', 80);
            $table->string('iban', 80)->nullable();

            $table->timestamp('data_alteracao');
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['data_alteracao']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dados_bancarios');
    }
};
