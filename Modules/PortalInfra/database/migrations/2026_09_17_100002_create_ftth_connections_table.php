<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ftth_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ftth_project_id')->nullable()->constrained('ftth_projects')->nullOnDelete();
            $table->string('source_type')->comment('origem: cto|caixa|splitter|olt|ponto');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->integer('source_port')->nullable()->comment('Porta na origem (splitter: 0=entrada)');
            $table->foreignId('fiber_link_id')->nullable()->constrained('ftth_fiber_links')->nullOnDelete();
            $table->string('target_type')->comment('destino: cto|caixa|splitter|olt|ponto');
            $table->unsignedBigInteger('target_id')->nullable();
            $table->integer('target_port')->nullable()->comment('Porta no destino');
            $table->string('fiber_number', 10)->nullable()->comment('Numero da fibra dentro do cabo');
            $table->string('status')->default('ativo');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['source_type', 'source_id']);
            $table->index(['target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ftth_connections');
    }
};