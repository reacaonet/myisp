<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('pedido')->nullable()->unique()->after('id');
            $table->string('route_ip')->nullable()->after('ip_address');
            $table->string('ipv6')->nullable()->after('route_ip');
            $table->string('mac_wireless')->nullable()->after('mac_address');
            $table->string('wpa_key')->nullable()->after('mac_wireless');
            $table->text('observacao')->nullable()->after('notes');
            $table->boolean('insento')->default(false)->after('discount');
            $table->boolean('autobloqueio')->default(true)->after('insento');
            $table->boolean('alterar_senha')->default(false)->after('autobloqueio');
            $table->decimal('acrescimo', 10, 2)->default(0)->after('discount');
            $table->string('ip_pool')->nullable()->after('acrescimo');
            $table->string('ip_ubnt')->nullable()->after('ip_pool');
            $table->string('porta_ubnt')->nullable()->after('ip_ubnt');
            $table->string('login_ubnt')->nullable()->after('porta_ubnt');
            $table->string('senha_ubnt')->nullable()->after('login_ubnt');
            $table->string('situacao', 1)->default('S')->after('status');
            $table->enum('tipo_conexao', ['pppoe', 'hotspot', 'iparp', 'dhcp'])->default('pppoe')->after('situacao');
            $table->unsignedBigInteger('server_id')->nullable()->after('plan_id');

            $table->string('install_street')->nullable()->after('observacao');
            $table->string('install_number')->nullable()->after('install_street');
            $table->string('install_complement')->nullable()->after('install_number');
            $table->string('install_neighborhood')->nullable()->after('install_complement');
            $table->string('install_city')->nullable()->after('install_neighborhood');
            $table->string('install_state', 2)->nullable()->after('install_city');
            $table->string('install_zipcode', 9)->nullable()->after('install_state');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn([
                'pedido', 'route_ip', 'ipv6', 'mac_wireless', 'wpa_key',
                'observacao', 'insento', 'autobloqueio', 'alterar_senha',
                'acrescimo', 'ip_pool', 'ip_ubnt', 'porta_ubnt',
                'login_ubnt', 'senha_ubnt', 'situacao', 'tipo_conexao',
                'server_id', 'install_street', 'install_number', 'install_complement',
                'install_neighborhood', 'install_city', 'install_state', 'install_zipcode',
            ]);
        });
    }
};
