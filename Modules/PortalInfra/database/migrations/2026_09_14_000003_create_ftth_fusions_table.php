<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ftth_fusions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ftth_project_id')->nullable()->constrained('ftth_projects')->nullOnDelete();
            $table->foreignId('cto_id')->nullable()->constrained('ctos')->nullOnDelete();
            $table->foreignId('caixa_emenda_id')->nullable()->constrained('caixas_emenda')->nullOnDelete();
            $table->string('fiber_number')->nullable();
            $table->string('olt_port')->nullable();
            $table->string('tube')->nullable();
            $table->string('destination')->nullable();
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ftth_fusions');
    }
};