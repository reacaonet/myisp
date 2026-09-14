<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ctos', function (Blueprint $table) {
            $table->string('olt_port')->nullable()->after('splitter_config');
        });

        Schema::table('caixas_emenda', function (Blueprint $table) {
            $table->string('olt_port')->nullable()->after('splitter_config');
        });
    }

    public function down(): void
    {
        Schema::table('ctos', function (Blueprint $table) {
            $table->dropColumn('olt_port');
        });

        Schema::table('caixas_emenda', function (Blueprint $table) {
            $table->dropColumn('olt_port');
        });
    }
};