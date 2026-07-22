<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('dias_bloqueio')->default(10);
            $table->integer('dias_geracao_fatura')->default(5);
            $table->boolean('bloqueio_automatico')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_settings');
    }
};
