<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('system_settings')->updateOrInsert(
            ['key' => 'landing_cities'],
            ['key' => 'landing_cities', 'value' => '', 'type' => 'textarea', 'group' => 'landing']
        );
    }

    public function down(): void
    {
        DB::table('system_settings')->where('key', 'landing_cities')->delete();
    }
};