<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dominio publico da empresa. Permite servir a landing de cada franchise
     * por um host proprio; vazio significa "cai na matriz".
     */
    public function up(): void
    {
        if (! Schema::hasTable('companies') || Schema::hasColumn('companies', 'domain')) {
            return;
        }

        Schema::table('companies', function (Blueprint $table) {
            $table->string('domain')->nullable()->unique()->after('slug');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('companies') || ! Schema::hasColumn('companies', 'domain')) {
            return;
        }

        Schema::table('companies', function (Blueprint $table) {
            $table->dropUnique(['domain']);
            $table->dropColumn('domain');
        });
    }
};
