<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 4: consolida a tabela legado `servers` em `mikrotik_servers`
     * e vincula `provisioning_records` ao contrato.
     *
     * O vinculo e feito por nome (sem adivinhar por IP) e os dados antigos
     * sao copiados para a tabela canonica, que ja e escopada por company_id.
     */
    public function up(): void
    {
        if (! Schema::hasTable('servers') || ! Schema::hasTable('mikrotik_servers')) {
            $this->addContractColumn();

            return;
        }

        $rootCompanyId = Schema::hasTable('companies')
            ? DB::table('companies')->whereNull('parent_id')->orderBy('id')->value('id')
            : null;

        $map = [];
        $nameIndex = DB::table('mikrotik_servers')
            ->select('id', 'name')
            ->get()
            ->mapWithKeys(fn ($row) => [mb_strtolower(trim((string) $row->name)) => $row->id])
            ->all();

        foreach (DB::table('servers')->orderBy('id')->get() as $legacy) {
            $key = mb_strtolower(trim((string) $legacy->name));

            if (isset($nameIndex[$key])) {
                $map[$legacy->id] = $nameIndex[$key];

                DB::table('mikrotik_servers')->where('id', $nameIndex[$key])->update([
                    'ip' => $legacy->ip ?? DB::raw('ip'),
                    'login' => $legacy->username ?: DB::raw('login'),
                    'senha' => $legacy->password ?: DB::raw('senha'),
                    'port' => $legacy->porta_api ?: 8728,
                    'is_active' => (bool) $legacy->is_active,
                    'updated_at' => now(),
                ]);

                continue;
            }

            $newId = DB::table('mikrotik_servers')->insertGetId([
                'company_id' => $rootCompanyId,
                'name' => $legacy->name,
                'ip' => $legacy->ip ?: '0.0.0.0',
                'port' => $legacy->porta_api ?: 8728,
                'login' => $legacy->username ?: 'admin',
                'senha' => $legacy->password ?: '',
                'type' => 'both',
                'is_active' => (bool) $legacy->is_active,
                'notes' => $legacy->interface ? "Interface: {$legacy->interface}" : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $nameIndex[$key] = $newId;
            $map[$legacy->id] = $newId;
        }

        // as FKs precisam sair antes do remapeamento, senao o id novo viola a constraint antiga
        Schema::table('mikrotik_backups', function (Blueprint $table) {
            $table->dropForeign(['server_id']);
        });
        Schema::table('hotspot_coupons', function (Blueprint $table) {
            $table->dropForeign(['server_id']);
        });
        Schema::table('uptime_monitors', function (Blueprint $table) {
            $table->dropForeign(['server_id']);
        });

        $this->remap($map, 'mikrotik_backups', 'server_id');
        $this->remap($map, 'hotspot_coupons', 'server_id');
        $this->remap($map, 'uptime_monitors', 'server_id');
        $this->remap($map, 'plans', 'server_id');
        $this->remap($map, 'contracts', 'server_id');

        Schema::dropIfExists('servers');

        Schema::table('mikrotik_backups', function (Blueprint $table) {
            $table->foreign('server_id')->references('id')->on('mikrotik_servers')->cascadeOnDelete();
        });
        Schema::table('hotspot_coupons', function (Blueprint $table) {
            $table->foreign('server_id')->references('id')->on('mikrotik_servers')->nullOnDelete();
        });
        Schema::table('uptime_monitors', function (Blueprint $table) {
            $table->foreign('server_id')->references('id')->on('mikrotik_servers')->nullOnDelete();
        });

        if (! Schema::hasIndex('mikrotik_servers', 'mikrotik_servers_company_ip_index')) {
            Schema::table('mikrotik_servers', function (Blueprint $table) {
                $table->index(['company_id', 'ip'], 'mikrotik_servers_company_ip_index');
            });
        }

        $this->addContractColumn();
    }

    public function down(): void
    {
        if (Schema::hasIndex('mikrotik_servers', 'mikrotik_servers_company_ip_index')) {
            Schema::table('mikrotik_servers', function (Blueprint $table) {
                $table->dropIndex('mikrotik_servers_company_ip_index');
            });
        }

        if (Schema::hasColumn('provisioning_records', 'contract_id')) {
            Schema::table('provisioning_records', function (Blueprint $table) {
                $table->dropForeign(['contract_id']);
                $table->dropColumn('contract_id');
            });
        }

        if (Schema::hasTable('mikrotik_servers') && ! Schema::hasTable('servers')) {
            Schema::create('servers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('ip')->nullable();
                $table->string('username')->nullable();
                $table->string('password')->nullable();
                $table->string('interface')->nullable();
                $table->string('secret')->nullable();
                $table->enum('tipo', ['mikrotik', 'ubiquiti', 'juniper', 'radius'])->default('mikrotik');
                $table->string('porta_api')->default('8728');
                $table->string('porta_ssh')->default('22');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            foreach (DB::table('mikrotik_servers')->orderBy('id')->get() as $row) {
                DB::table('servers')->insert([
                    'name' => $row->name,
                    'ip' => $row->ip,
                    'username' => $row->login,
                    'password' => $row->senha,
                    'porta_api' => $row->port,
                    'is_active' => (bool) $row->is_active,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }
    }

    /**
     * @param  array<int, int>  $map
     */
    protected function remap(array $map, string $table, string $column): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column) || $map === []) {
            return;
        }

        foreach ($map as $oldId => $newId) {
            DB::table($table)->where($column, $oldId)->update([$column => $newId]);
        }
    }

    protected function addContractColumn(): void
    {
        if (! Schema::hasTable('provisioning_records') || Schema::hasColumn('provisioning_records', 'contract_id')) {
            return;
        }

        Schema::table('provisioning_records', function (Blueprint $table) {
            $table->foreignId('contract_id')->nullable()->after('client_id')->constrained('contracts')->nullOnDelete();
        });

        $this->backfillContract();
    }

    /**
     * Preenche `contract_id` com o contrato ativo mais recente do cliente.
     */
    protected function backfillContract(): void
    {
        if (! Schema::hasTable('contracts') || ! Schema::hasColumn('contracts', 'status')) {
            return;
        }

        DB::table('provisioning_records as pr')
            ->whereNull('pr.contract_id')
            ->whereNotNull('pr.client_id')
            ->update([
                'pr.contract_id' => DB::raw('(SELECT c.id FROM contracts c WHERE c.client_id = pr.client_id AND c.status = \'active\' ORDER BY c.id DESC LIMIT 1)'),
            ]);
    }
};
