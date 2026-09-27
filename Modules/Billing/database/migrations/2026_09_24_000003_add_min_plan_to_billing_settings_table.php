<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_settings', function (Blueprint $table) {
            $table->integer('plano_minimo_kbps')->default(512)->after('bloqueio_automatico');
            $table->integer('plano_minimo_upload_kbps')->default(128)->after('plano_minimo_kbps');
            $table->boolean('plano_minimo_habilitado')->default(true)->after('plano_minimo_upload_kbps');
        });
    }

    public function down(): void
    {
        Schema::table('billing_settings', function (Blueprint $table) {
            $table->dropColumn(['plano_minimo_kbps', 'plano_minimo_upload_kbps', 'plano_minimo_habilitado']);
        });
    }
};