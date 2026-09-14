<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ctos', function (Blueprint $table) {
            $table->text('technician_notes')->nullable()->after('project_notes');
        });
        Schema::table('caixas_emenda', function (Blueprint $table) {
            $table->text('technician_notes')->nullable()->after('project_notes');
        });
    }

    public function down(): void
    {
        Schema::table('ctos', fn (Blueprint $table) => $table->dropColumn('technician_notes'));
        Schema::table('caixas_emenda', fn (Blueprint $table) => $table->dropColumn('technician_notes'));
    }
};