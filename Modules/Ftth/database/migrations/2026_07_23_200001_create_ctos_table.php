<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ctos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caixa_emenda_id')->nullable()->constrained('caixas_emenda')->nullOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->integer('capacity')->default(8);
            $table->integer('used_ports')->default(0);
            $table->string('street')->nullable();
            $table->string('number')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();
            $table->string('zipcode', 9)->nullable();
            $table->string('status')->default('active');
            $table->decimal('distance_from_start', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ctos');
    }
};
