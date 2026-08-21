<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pharmacy_branches', function (Blueprint $table) {
            if (! Schema::hasColumn('pharmacy_branches', 'opening_hours')) {
                $table->string('opening_hours', 255)->nullable()->after('street');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pharmacy_branches', function (Blueprint $table) {
            if (Schema::hasColumn('pharmacy_branches', 'opening_hours')) {
                $table->dropColumn('opening_hours');
            }
        });
    }
};
