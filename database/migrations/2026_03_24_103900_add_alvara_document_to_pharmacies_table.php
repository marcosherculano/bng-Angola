<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pharmacies', 'alvara_document_path')) {
            Schema::table('pharmacies', function (Blueprint $table) {
                $table->string('alvara_document_path', 500)->nullable()->after('alvara');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pharmacies', 'alvara_document_path')) {
            Schema::table('pharmacies', function (Blueprint $table) {
                $table->dropColumn('alvara_document_path');
            });
        }
    }
};
