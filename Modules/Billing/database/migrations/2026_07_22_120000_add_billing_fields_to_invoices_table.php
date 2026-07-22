<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->timestamp('blocked_at')->nullable()->after('paid_date');
            $table->boolean('auto_blocked')->default(false)->after('blocked_at');
            $table->decimal('acrescimo', 10, 2)->default(0)->after('discount');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['blocked_at', 'auto_blocked', 'acrescimo']);
        });
    }
};
