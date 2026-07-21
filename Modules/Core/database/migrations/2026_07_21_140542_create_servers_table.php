<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ip')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('interface')->nullable();
            $table->string('secret')->nullable();
            $table->enum('tipo', ['mikrotik', 'ubiquiti', 'juniper', 'radius'])->default('mikrotik');
            $table->string('porta_api')->default('8728');
            $table->string('porta_ssh')->default('22');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};
