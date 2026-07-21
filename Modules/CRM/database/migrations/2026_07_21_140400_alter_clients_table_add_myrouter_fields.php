<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('codigo')->nullable()->after('id');
            $table->string('login')->nullable()->unique()->after('email');
            $table->string('senha')->nullable()->after('login');
            $table->string('rg')->nullable()->after('document');
            $table->string('estado_civil')->nullable()->after('birth_date');
            $table->string('naturalidade')->nullable()->after('estado_civil');
            $table->date('data_entrada')->nullable()->after('naturalidade');
            $table->date('vcto_contrato')->nullable()->after('data_entrada');
            $table->string('pai')->nullable()->after('vcto_contrato');
            $table->string('mae')->nullable()->after('pai');
            $table->boolean('nf')->default(false)->after('mae');
            $table->string('cfop')->nullable()->after('nf');
            $table->string('tipo_assinante')->nullable()->after('cfop');
            $table->string('tipo_utilizacao')->nullable()->after('tipo_assinante');
            $table->string('grupo')->nullable()->after('tipo_utilizacao');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'codigo', 'login', 'senha', 'rg', 'estado_civil', 'naturalidade',
                'data_entrada', 'vcto_contrato', 'pai', 'mae', 'nf', 'cfop',
                'tipo_assinante', 'tipo_utilizacao', 'grupo',
            ]);
        });
    }
};
