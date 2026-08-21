<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pharmacy_branches', function (Blueprint $table) {
            if (! Schema::hasColumn('pharmacy_branches', 'status')) {
                $table->string('status', 20)->default('pending')->after('is_active');
                $table->index(['matrix_id', 'status']);
            }
        });

        if (Schema::hasColumn('pharmacy_branches', 'status')) {
            DB::statement("UPDATE pharmacy_branches pb\n                LEFT JOIN users u ON u.id = pb.user_id\n                SET pb.status = CASE\n                    WHEN u.status = 'pending' THEN 'pending'\n                    ELSE 'approved'\n                END");
        }
    }

    public function down(): void
    {
        Schema::table('pharmacy_branches', function (Blueprint $table) {
            if (Schema::hasColumn('pharmacy_branches', 'status')) {
                $table->dropIndex(['matrix_id', 'status']);
                $table->dropColumn('status');
            }
        });
    }
};
