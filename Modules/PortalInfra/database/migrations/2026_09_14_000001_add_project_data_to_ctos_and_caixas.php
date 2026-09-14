<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ctos', function (Blueprint $table) {
            $table->integer('fiber_fusions')->nullable()->after('used_ports');
            $table->string('splitter_config')->nullable()->after('fiber_fusions');
            $table->text('project_notes')->nullable()->after('notes');
        });

        Schema::table('caixas_emenda', function (Blueprint $table) {
            $table->integer('fiber_fusions')->nullable()->after('used_ports');
            $table->string('splitter_config')->nullable()->after('fiber_fusions');
            $table->text('project_notes')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('ctos', function (Blueprint $table) {
            $table->dropColumn(['fiber_fusions', 'splitter_config', 'project_notes']);
        });

        Schema::table('caixas_emenda', function (Blueprint $table) {
            $table->dropColumn(['fiber_fusions', 'splitter_config', 'project_notes']);
        });
    }
};