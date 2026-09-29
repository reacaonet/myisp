<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 4: cupons passam a ser unicos por empresa, e nao globalmente.
     */
    public function up(): void
    {
        if (! Schema::hasTable('hotspot_coupons')) {
            return;
        }

        Schema::table('hotspot_coupons', function (Blueprint $table) {
            $table->dropUnique(['code']);
        });

        Schema::table('hotspot_coupons', function (Blueprint $table) {
            $table->unique(['company_id', 'code'], 'hotspot_coupons_company_code_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('hotspot_coupons')) {
            return;
        }

        Schema::table('hotspot_coupons', function (Blueprint $table) {
            $table->dropUnique('hotspot_coupons_company_code_unique');
        });

        Schema::table('hotspot_coupons', function (Blueprint $table) {
            $table->unique('code');
        });
    }
};
