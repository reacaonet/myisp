<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE provisioning_records DROP CONSTRAINT IF EXISTS provisioning_records_action_check');
        DB::statement("ALTER TABLE provisioning_records ADD CONSTRAINT provisioning_records_action_check CHECK (action IN ('add', 'remove', 'update', 'disable', 'enable'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE provisioning_records DROP CONSTRAINT IF EXISTS provisioning_records_action_check');
        DB::statement("ALTER TABLE provisioning_records ADD CONSTRAINT provisioning_records_action_check CHECK (action IN ('add', 'remove', 'disable', 'enable'))");
    }
};