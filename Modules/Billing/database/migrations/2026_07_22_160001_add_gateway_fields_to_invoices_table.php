<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('gateway_id')->nullable()->after('payment_method')->constrained('payment_gateways')->nullOnDelete();
            $table->string('gateway_status')->nullable()->after('gateway_id');
            $table->string('gateway_payment_url')->nullable()->after('gateway_status');
            $table->text('gateway_qr_code')->nullable()->after('gateway_payment_url');
            $table->text('pix_copy_paste')->nullable()->after('gateway_qr_code');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['gateway_id']);
            $table->dropColumn(['gateway_id', 'gateway_status', 'gateway_payment_url', 'gateway_qr_code', 'pix_copy_paste']);
        });
    }
};
