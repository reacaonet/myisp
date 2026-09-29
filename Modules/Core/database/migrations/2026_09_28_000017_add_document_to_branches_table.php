<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A rede e uma empresa so. As filiais sao as unidades operacionais e cada
     * uma pode ter CNPJ proprio para emitir nota em seu nome. Filial sem CNPJ
     * continua valida: o index unique do Postgres ignora os NULLs, entao varias
     * filiais podem ficar sem documento.
     */
    public function up(): void
    {
        if (! Schema::hasTable('branches') || Schema::hasColumn('branches', 'document')) {
            return;
        }

        Schema::table('branches', function (Blueprint $table) {
            $table->string('document')->nullable()->unique()->after('name');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('branches') || ! Schema::hasColumn('branches', 'document')) {
            return;
        }

        Schema::table('branches', function (Blueprint $table) {
            $table->dropUnique(['document']);
            $table->dropColumn('document');
        });
    }
};
