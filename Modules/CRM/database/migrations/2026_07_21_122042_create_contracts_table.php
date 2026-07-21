<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained()->onDelete('restrict');
            $table->date('activation_date');
            $table->date('due_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended', 'canceled'])->default('active');
            $table->enum('billing_type', ['boleto', 'pix', 'credit_card', 'debit_contract'])->default('boleto');
            $table->integer('due_day')->default(5);
            $table->string('pppoe_user')->nullable();
            $table->string('pppoe_password')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('mac_address')->nullable();
            $table->decimal('discount', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
