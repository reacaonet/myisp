<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['company_user', 'branch_user'] as $table) {
            if (! Schema::hasColumn($table, 'created_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->timestamp('created_at')->nullable()->after('user_id');
                    $t->timestamp('updated_at')->nullable()->after('created_at');
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['company_user', 'branch_user'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn(['created_at', 'updated_at']);
            });
        }
    }
};
