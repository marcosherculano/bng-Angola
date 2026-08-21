<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_deliveries', function (Blueprint $table) {
            if (! Schema::hasColumn('order_deliveries', 'eta_at')) {
                $table->timestamp('eta_at')->nullable()->after('delivered_at');
            }

            if (! Schema::hasColumn('order_deliveries', 'partner_status')) {
                $table->string('partner_status', 80)->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_deliveries', function (Blueprint $table) {
            if (Schema::hasColumn('order_deliveries', 'eta_at')) {
                $table->dropColumn('eta_at');
            }

            if (Schema::hasColumn('order_deliveries', 'partner_status')) {
                $table->dropColumn('partner_status');
            }
        });
    }
};
