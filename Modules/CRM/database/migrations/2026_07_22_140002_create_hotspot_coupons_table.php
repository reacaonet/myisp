<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotspot_coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('profile')->nullable();
            $table->integer('duration_hours')->default(24);
            $table->decimal('price', 8, 2)->default(0);
            $table->enum('status', ['active', 'used', 'expired'])->default('active');
            $table->unsignedBigInteger('server_id')->nullable();
            $table->foreign('server_id')->references('id')->on('servers')->nullOnDelete();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotspot_coupons');
    }
};
