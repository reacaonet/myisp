<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // O endereco novo fica no proprio chamado, em jsonb, e so vira endereco
        // cadastrado quando um admin aprova. Antes disso o `tickets` nao tinha
        // onde guardar uma solicitacao estruturada: ou o cliente alterava o
        // endereco sozinho (sem aprovacao e sem rastro), ou o pedido ia diluido
        // no texto da descricao.
        Schema::table('tickets', function (Blueprint $table) {
            $table->jsonb('proposed_address')->nullable()->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('proposed_address');
        });
    }
};
