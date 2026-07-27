<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ctos', function (Blueprint $table) {
            $table->dropUnique(['code']);
            if (!Schema::hasColumn('ctos', 'city')) {
                $table->string('city', 100)->nullable()->after('name');
            }
            if (!Schema::hasColumn('ctos', 'state')) {
                $table->string('state', 2)->nullable()->after('city');
            }
            $table->index(['code', 'city'], 'ctos_code_city_idx');
        });

        Schema::table('caixas_emenda', function (Blueprint $table) {
            $table->dropUnique(['code']);
            if (!Schema::hasColumn('caixas_emenda', 'city')) {
                $table->string('city', 100)->nullable()->after('name');
            }
            if (!Schema::hasColumn('caixas_emenda', 'state')) {
                $table->string('state', 2)->nullable()->after('city');
            }
            $table->index(['code', 'city'], 'caixas_code_city_idx');
        });
    }

    public function down(): void
    {
        Schema::table('ctos', function (Blueprint $table) {
            if (Schema::hasIndex('ctos', 'ctos_code_city_idx')) {
                $table->dropIndex('ctos_code_city_idx');
            }
            $table->dropColumn(['city', 'state']);
            $table->unique('code');
        });

        Schema::table('caixas_emenda', function (Blueprint $table) {
            if (Schema::hasIndex('caixas_emenda', 'caixas_code_city_idx')) {
                $table->dropIndex('caixas_code_city_idx');
            }
            $table->dropColumn(['city', 'state']);
            $table->unique('code');
        });
    }
};
