<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pharmacy_branches', function (Blueprint $table) {
            if (! Schema::hasColumn('pharmacy_branches', 'document_path')) {
                $table->string('document_path', 500)->nullable()->after('monthly_fee');
            }
            if (! Schema::hasColumn('pharmacy_branches', 'image_path')) {
                $table->string('image_path', 500)->nullable()->after('document_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pharmacy_branches', function (Blueprint $table) {
            if (Schema::hasColumn('pharmacy_branches', 'image_path')) {
                $table->dropColumn('image_path');
            }
            if (Schema::hasColumn('pharmacy_branches', 'document_path')) {
                $table->dropColumn('document_path');
            }
        });
    }
};
