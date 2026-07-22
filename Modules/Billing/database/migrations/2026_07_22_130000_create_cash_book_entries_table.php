<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_book_entries', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['entrada', 'saida']);
            $table->decimal('amount', 10, 2);
            $table->string('description');
            $table->string('category')->nullable();
            $table->date('entry_date');
            $table->string('reference')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('notes')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->foreign('invoice_id')->references('id')->on('invoices')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_book_entries');
    }
};
