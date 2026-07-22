<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('technicians', function (Blueprint $table) {
            $table->string('login')->nullable()->unique()->after('name');
            $table->string('senha')->nullable()->after('login');
            $table->rememberToken()->after('senha');
        });
    }

    public function down(): void
    {
        Schema::table('technicians', function (Blueprint $table) {
            $table->dropColumn(['login', 'senha', 'remember_token']);
        });
    }
};
