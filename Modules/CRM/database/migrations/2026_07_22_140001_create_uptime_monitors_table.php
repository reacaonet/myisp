<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uptime_monitors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('host');
            $table->integer('port')->default(80);
            $table->enum('type', ['http', 'ping', 'tcp'])->default('ping');
            $table->integer('interval_seconds')->default(60);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_up')->nullable();
            $table->timestamp('last_check_at')->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->text('last_error')->nullable();
            $table->unsignedBigInteger('server_id')->nullable();
            $table->foreign('server_id')->references('id')->on('servers')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uptime_monitors');
    }
};
