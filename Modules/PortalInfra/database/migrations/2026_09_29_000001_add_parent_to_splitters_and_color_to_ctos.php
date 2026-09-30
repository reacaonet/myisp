<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Splitter deixa de ser um elemento solto no mapa: passa a pertencer a
        // uma CEO (caixa de emenda) ou a uma CTO, que e quem o alimenta e
        // distribui. parent_type + parent_id sao a referencia polimorfica.
        Schema::table('ftth_splitters', function (Blueprint $table) {
            $table->string('parent_type')->nullable()->after('code');
            $table->unsignedBigInteger('parent_id')->nullable()->after('parent_type');
        });

        Schema::table('ftth_splitters', function (Blueprint $table) {
            $table->index(['parent_type', 'parent_id'], 'ftth_splitters_parent_idx');
        });

        // Cor por CTO, para o tecnico diferenciar as CTOs de uma mesma CEO no
        // mapa e salvar o projeto mais rapido.
        Schema::table('ctos', function (Blueprint $table) {
            $table->string('color', 7)->nullable()->after('capacity');
        });
    }

    public function down(): void
    {
        Schema::table('ftth_splitters', function (Blueprint $table) {
            $table->dropIndex('ftth_splitters_parent_idx');
            $table->dropColumn(['parent_type', 'parent_id']);
        });

        Schema::table('ctos', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
