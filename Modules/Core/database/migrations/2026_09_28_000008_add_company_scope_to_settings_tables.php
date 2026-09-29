<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addCompanyScopeToSettings();
        $this->addLegalNamesToCompanies();
    }

    protected function addCompanyScopeToSettings(): void
    {
        if (! Schema::hasColumn('system_settings', 'company_id')) {
            Schema::table('system_settings', function (Blueprint $t) {
                $t->foreignId('company_id')->nullable()->after('id')
                    ->constrained('companies')->nullOnDelete();
            });
        }

        // unique por company + chave; NULL (company_id) = template da raiz
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE system_settings DROP CONSTRAINT IF EXISTS system_settings_key_unique');
            DB::statement('DROP INDEX IF EXISTS system_settings_key_unique');
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS system_settings_company_key_unique ON system_settings (company_id, key) WHERE company_id IS NOT NULL');
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS system_settings_template_key_unique ON system_settings (key) WHERE company_id IS NULL');
        } else {
            Schema::table('system_settings', function (Blueprint $t) {
                $t->dropUnique(['key']);
                $t->unique(['company_id', 'key']);
            });
        }

        if (! Schema::hasColumn('billing_settings', 'company_id')) {
            Schema::table('billing_settings', function (Blueprint $t) {
                $t->foreignId('company_id')->nullable()->after('id')
                    ->constrained('companies')->cascadeOnDelete();
            });
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS billing_settings_company_unique ON billing_settings (company_id) WHERE company_id IS NOT NULL');
        }
    }

    protected function addLegalNamesToCompanies(): void
    {
        foreach (['legal_name', 'fantasy_name'] as $column) {
            if (! Schema::hasColumn('companies', $column)) {
                Schema::table('companies', function (Blueprint $t) use ($column) {
                    $t->string($column)->nullable();
                });
            }
        }

        // company_* das settings -> colunas canonicas de companies
        $map = [
            'company_name' => 'legal_name',
            'company_fantasy' => 'fantasy_name',
            'company_document' => 'document',
            'company_state_registration' => 'state_registration',
            'company_municipal_registration' => 'municipal_registration',
            'company_phone' => 'phone',
            'company_cellphone' => 'cellphone',
            'company_email' => 'email',
            'company_website' => 'website',
            'company_address' => 'address',
            'company_city' => 'city',
            'company_state' => 'state',
            'company_zip' => 'zip',
        ];

        $settings = DB::table('system_settings')->whereNull('company_id')->pluck('value', 'key');

        foreach ($map as $key => $column) {
            $value = $settings[$key] ?? null;

            if (blank($value)) {
                continue;
            }

            DB::table('companies')
                ->whereNull($column)
                ->update([$column => $value]);
        }
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS system_settings_company_key_unique');
        DB::statement('DROP INDEX IF EXISTS system_settings_template_key_unique');
        DB::statement('DROP INDEX IF EXISTS billing_settings_company_unique');

        Schema::table('system_settings', function (Blueprint $t) {
            $t->dropForeign(['company_id']);
            $t->dropColumn('company_id');
        });

        Schema::table('billing_settings', function (Blueprint $t) {
            $t->dropForeign(['company_id']);
            $t->dropColumn('company_id');
        });

        Schema::table('companies', function (Blueprint $t) {
            $t->dropColumn(['legal_name', 'fantasy_name']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS system_settings_key_unique ON system_settings (key)');
        } else {
            Schema::table('system_settings', function (Blueprint $t) {
                $t->unique('key');
            });
        }
    }
};
