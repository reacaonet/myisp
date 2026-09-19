<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ftth_fiber_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ftth_project_id')->nullable()->constrained('ftth_projects')->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('type')->default('distribuicao')->comment('tronco|distribuicao|drop');
            $table->json('geometry')->comment('Polilinha [[lat,lng],...] da fibra lancada');
            $table->decimal('length_meters', 12, 2)->default(0)->comment('Comprimento calculado em metros');
            $table->string('fiber_count', 10)->nullable()->comment('Numero de fibras do cabo');
            $table->string('tube_color', 20)->nullable();
            $table->string('status')->default('ativo');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ftth_fiber_links');
    }
};