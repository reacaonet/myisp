<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mikrotik_backups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('server_id');
            $table->foreign('server_id')->references('id')->on('servers')->cascadeOnDelete();
            $table->string('filename');
            $table->text('content')->nullable();
            $table->integer('file_size')->nullable();
            $table->enum('type', ['auto', 'manual'])->default('manual');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mikrotik_backups');
    }
};
