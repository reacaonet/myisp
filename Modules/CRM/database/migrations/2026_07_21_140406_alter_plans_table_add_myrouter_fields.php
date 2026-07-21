<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->string('url_advertise')->nullable()->after('address_list');
            $table->integer('advertise_intervalo')->nullable()->after('url_advertise');
            $table->integer('max_session_time')->nullable()->after('max_simultaneous');
            $table->string('police_in')->nullable()->after('max_session_time');
            $table->string('police_out')->nullable()->after('police_in');
            $table->string('tipo_servidor')->nullable()->after('police_out');
            $table->string('interface')->nullable()->after('tipo_servidor');
            $table->string('plano_id_externo')->nullable()->after('interface');
            $table->unsignedBigInteger('server_id')->nullable()->after('plano_id_externo');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'url_advertise', 'advertise_intervalo', 'max_session_time',
                'police_in', 'police_out', 'tipo_servidor', 'interface',
                'plano_id_externo', 'server_id',
            ]);
        });
    }
};
