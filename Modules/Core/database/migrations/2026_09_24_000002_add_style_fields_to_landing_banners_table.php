<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_banners', function (Blueprint $table) {
            $table->integer('title_font_size')->nullable()->after('highlight');
            $table->string('title_color')->nullable()->after('title_font_size');
            $table->integer('subtitle_font_size')->nullable()->after('title_color');
            $table->string('subtitle_color')->nullable()->after('subtitle_font_size');
        });
    }

    public function down(): void
    {
        Schema::table('landing_banners', function (Blueprint $table) {
            $table->dropColumn(['title_font_size', 'title_color', 'subtitle_font_size', 'subtitle_color']);
        });
    }
};