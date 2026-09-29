<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 4: ancora tabelas de infraestrutura na empresa.
     *
     * `hotspot_coupons` e `uptime_monitors` aceitam `server_id` nulo, entao
     * so o filtro por servidor deixaria registros invisiveis para o tenant.
     */
    public function up(): void
    {
        $rootCompanyId = Schema::hasTable('companies')
            ? DB::table('companies')->whereNull('parent_id')->orderBy('id')->value('id')
            : null;

        foreach (['hotspot_coupons', 'uptime_monitors', 'mikrotik_backups'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            if (! Schema::hasColumn($table, 'company_id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->foreignId('company_id')->nullable()->after('id');
                });
            }

            DB::table($table)->whereNull('company_id')->whereNotNull('server_id')->update([
                'company_id' => DB::raw('(SELECT ms.company_id FROM mikrotik_servers ms WHERE ms.id = '.$table.'.server_id)'),
            ]);

            if ($rootCompanyId) {
                DB::table($table)->whereNull('company_id')->update(['company_id' => $rootCompanyId]);
            }

            DB::table($table)->whereNull('company_id')->update(['company_id' => DB::raw('(SELECT ms.company_id FROM mikrotik_servers ms WHERE ms.id = '.$table.'.server_id)')]);

            if (! Schema::hasColumn($table, 'company_id')) {
                continue;
            }

            $remaining = DB::table($table)->whereNull('company_id')->count();

            if ($remaining === 0) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
                });

                DB::statement('ALTER TABLE '.$table.' ALTER COLUMN company_id SET NOT NULL');
            }
        }
    }

    public function down(): void
    {
        foreach (['hotspot_coupons', 'uptime_monitors', 'mikrotik_backups'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'company_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropForeign(['company_id']);
            });

            DB::statement('ALTER TABLE '.$table.' ALTER COLUMN company_id DROP NOT NULL');

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('company_id');
            });
        }
    }
};
