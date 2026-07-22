<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manufacturer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable()->unique();
            $table->string('barcode')->nullable()->unique();
            $table->string('mac_address')->nullable();
            $table->string('ip_address')->nullable();
            $table->enum('type', ['onu', 'router', 'switch', 'access_point', 'antenna', 'cable', 'other'])->default('other');
            $table->string('invoice_number')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('warranty_until')->nullable();
            $table->string('supplier')->nullable();
            $table->integer('quantity')->default(1);
            $table->integer('available_quantity')->default(1);
            $table->enum('status', ['available', 'allocated', 'maintenance', 'defective', 'retired'])->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
