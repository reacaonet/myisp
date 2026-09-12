<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ftth_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();
            $table->string('prefix', 10)->nullable();
            $table->string('status')->default('active');
            $table->integer('total_streets')->default(0);
            $table->integer('total_ctos')->default(0);
            $table->integer('total_caixas')->default(0);
            $table->decimal('total_distance_km', 10, 2)->default(0);
            $table->boolean('has_bounds')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('ctos', function (Blueprint $table) {
            $table->foreignId('ftth_project_id')->nullable()->after('caixa_emenda_id')->constrained('ftth_projects')->nullOnDelete();
        });

        Schema::table('caixas_emenda', function (Blueprint $table) {
            $table->foreignId('ftth_project_id')->nullable()->after('id')->constrained('ftth_projects')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('caixas_emenda', function (Blueprint $table) {
            $table->dropForeign(['ftth_project_id']);
            $table->dropColumn('ftth_project_id');
        });

        Schema::table('ctos', function (Blueprint $table) {
            $table->dropForeign(['ftth_project_id']);
            $table->dropColumn('ftth_project_id');
        });

        Schema::dropIfExists('ftth_projects');
    }
};