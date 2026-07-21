<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->text('motivo')->nullable()->after('notes');
            $table->integer('mes_parcela')->nullable()->after('motivo');
            $table->boolean('avulso')->default(false)->after('contract_id');
            $table->string('ref_os')->nullable()->after('avulso');
            $table->string('link_boleto')->nullable()->after('transaction_id');
            $table->string('chave_boleto')->nullable()->after('link_boleto');
            $table->string('boleto_numero')->nullable()->after('chave_boleto');
            $table->integer('dia')->nullable()->after('due_date');
            $table->integer('mes')->nullable()->after('dia');
            $table->integer('ano')->nullable()->after('mes');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'motivo', 'mes_parcela', 'avulso', 'ref_os',
                'link_boleto', 'chave_boleto', 'boleto_numero',
                'dia', 'mes', 'ano',
            ]);
        });
    }
};
