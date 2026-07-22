<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mikrotik_servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ip');
            $table->integer('port')->default(8728);
            $table->string('login');
            $table->string('senha');
            $table->enum('type', ['pppoe', 'hotspot', 'both'])->default('both');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mikrotik_servers');
    }
};
