<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Da ao grupo "franqueados" um escopo operacional de Franchise.
 *
 * A migration 000018 deixou o grupo com apenas `franchisees`, o que fazia
 * TODA pagina responder 403: dashboard, clientes, contratos, chamados e
 * faturas exigem as proprias chaves de permissao.
 *
 * O que mantem o franqueado preso na sua loja nao e esta permissao: e o
 * vinculo do usuario com a empresa e a filial. Ele pode gravar em clientes e
 * contratos, mas so enxerga os registros da propria companhia.
 *
 * Duas ressalvas:
 *
 *  - `settings`, infraestrutura e provisionamento seguem negados: sao areas da
 *    plataforma, nao da franquia.
 *  - `reports` fica NEGADO de proposito. ReportController nao aplica escopo de
 *    tenant, entao concede-lo mostraria ao franqueado o faturamento, os
 *    assinantes e a carteira de clientes da rede inteira. Conceder `reports`
 *    so deve acontecer depois que aquele controller for corrigido.
 */
return new class extends Migration
{
    private const SLUG = 'franqueados';

    /** Espelha Modules\Core\Database\Seeders\UserGroupSeeder (grupo gerente). */
    private const GRANTS = [
        'dashboard' => true,
        'clients' => true,
        'plans' => true,
        'contracts' => true,
        'service_orders' => true,
        'technicians' => true,
        'equipment' => true,
        'olts' => true,
        'ftth' => true,
        'tickets' => true,
        'invoices' => true,
        'cash_book' => true,
        'boleto' => true,
        'newsletter' => true,
        'stock' => true,
        // Painel do franqueado, concedido em 000018.
        'franchisees' => true,
        // Fora do perfil do gerente, mantidos explicitamente desligados para
        // documentar a intencao caso o gerente mude no futuro.
        //
        // `reports` e `suppliers` ficam fora por um motivo especifico, e nao
        // so por copia do gerente: ReportController roda consultas SEM escopo
        // de tenant, entao concede-lo entregaria ao franqueado o faturamento
        // e a carteira de clientes da rede inteira. Enquanto esse controller
        // nao for escopado, a permissao nao pode entrar aqui.
        'reports' => false,
        'manufacturers' => false,
        'suppliers' => false,
        'hotspot_coupons' => false,
        'mikrotik_servers' => false,
        'provisioning' => false,
        'uptime' => false,
        'network_monitor' => false,
        'gateways' => false,
        'backups' => false,
        'site_blocking' => false,
        'settings' => false,
    ];

    public function up(): void
    {
        $groupId = DB::table('user_groups')->where('slug', self::SLUG)->value('id');

        if (! $groupId) {
            // Grupo ausente: a 000018 cria. Nao ha o que conceder aqui.
            return;
        }

        foreach (self::GRANTS as $key => $granted) {
            $existing = DB::table('group_permissions')
                ->where('group_id', $groupId)
                ->where('permission_key', $key)
                ->first();

            if ($existing) {
                // Escreve por cima. Este grupo ja existia com um perfil antigo
                // e incompleto (era so `franchisees`), e foi exatamente essa
                // divergencia que deixou paginas respondendo 403. Preservar o
                // valor antigo aqui manteria o bug; o perfil deste grupo e o
                // do gerente, definido pelo seeder.
                DB::table('group_permissions')
                    ->where('group_id', $groupId)
                    ->where('permission_key', $key)
                    ->update([
                        'granted' => $granted,
                        'updated_at' => now(),
                    ]);

                continue;
            }

            DB::table('group_permissions')->insert([
                'group_id' => $groupId,
                'permission_key' => $key,
                'granted' => $granted,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $groupId = DB::table('user_groups')->where('slug', self::SLUG)->value('id');

        if (! $groupId) {
            return;
        }

        // Volta ao estado da 000018: apenas o painel.
        DB::table('group_permissions')
            ->where('group_id', $groupId)
            ->whereIn('permission_key', array_keys(array_filter(self::GRANTS)))
            ->where('permission_key', '!=', 'franchisees')
            ->delete();
    }
};
