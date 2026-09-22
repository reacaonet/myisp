<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('olts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('ip');
            $table->string('subnet_mask')->default('24');
            $table->string('type')->default('gpon');
            $table->integer('pon_ports')->default(8);
            $table->integer('used_pon_ports')->default(0);
            $table->string('mgmt_login')->nullable();
            $table->string('mgmt_password')->nullable();
            $table->integer('snmp_port')->default(161);
            $table->string('snmp_community')->nullable();
            $table->string('olt_region')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('olts');
    }
};