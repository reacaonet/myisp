<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ftth_splitters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ftth_project_id')->nullable()->constrained('ftth_projects')->nullOnDelete();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->integer('input_ports')->default(1)->comment('Portas de entrada (comumente 1)');
            $table->integer('output_ports')->default(8)->comment('Portas de saida (8/16/32)');
            $table->string('ratio', 10)->nullable()->comment('Ex.: 1x8, 1x16, 1x32');
            $table->string('status')->default('ativo');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ftth_splitters');
    }
};