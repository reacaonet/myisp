<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->nullable();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('contract_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('plan_id')->nullable()->constrained()->onDelete('set null');
            $table->unsignedBigInteger('technician_id')->nullable();
            $table->string('situacao', 2)->default('O');
            $table->enum('status', ['active', 'closed', 'canceled'])->default('active');
            $table->boolean('encerrado')->default(false);
            $table->string('servico')->nullable();
            $table->enum('tipo_servico', ['instalacao', 'manutencao', 'cancelamento', 'recuperacao', 'orcamento', 'visita_tecnica', 'outro'])->nullable();
            $table->date('emissao')->nullable();
            $table->time('hora_abertura')->nullable();
            $table->date('orcamento')->nullable();
            $table->date('aprovacao')->nullable();
            $table->date('saida')->nullable();
            $table->date('data_agendamento')->nullable();
            $table->time('hora_agendamento')->nullable();
            $table->text('problema')->nullable();
            $table->text('diagnostico')->nullable();
            $table->text('solucao')->nullable();
            $table->string('atendente')->nullable();
            $table->decimal('preco', 10, 2)->default(0);
            $table->string('serie')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};
