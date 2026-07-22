<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provisioning_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mikrotik_server_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['pppoe', 'hotspot']);
            $table->enum('action', ['add', 'remove', 'disable', 'enable']);
            $table->string('login');
            $table->json('params')->nullable();
            $table->json('response')->nullable();
            $table->boolean('success')->default(false);
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provisioning_records');
    }
};
