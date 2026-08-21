<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->unsignedTinyInteger('age')->nullable()->after('phone');

            $table->enum('role', [
                'admin',
                'client',
                'pharmacy_normal',
                'pharmacy_matrix',
                'pharmacy_branch',
            ])->default('client')->after('password');

            $table->enum('status', ['pending', 'approved', 'suspended', 'blocked'])
                ->default('pending')
                ->after('role');

            $table->string('province', 100)->nullable()->after('status');
            $table->decimal('location_lat', 10, 8)->nullable()->after('province');
            $table->decimal('location_lng', 11, 8)->nullable()->after('location_lat');

            $table->timestamp('approved_at')->nullable()->after('location_lng');
            $table->foreignId('approved_by')->nullable()->after('approved_at')
                ->constrained('users')
                ->nullOnDelete();

            $table->index(['role', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropIndex(['role', 'status']);
            $table->dropColumn([
                'phone',
                'age',
                'role',
                'status',
                'province',
                'location_lat',
                'location_lng',
                'approved_at',
            ]);
        });
    }
};
