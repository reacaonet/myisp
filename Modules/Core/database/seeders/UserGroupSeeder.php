<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\GroupPermission;
use Modules\Core\Models\UserGroup;

class UserGroupSeeder extends Seeder
{
    public function run(): void
    {
        $allPermissions = array_keys(GroupPermission::MENU_PERMISSIONS());

        $groupDefs = [
            'superadmin' => [
                'name' => 'Super Administrador',
                'description' => 'Acesso total ao sistema. Controle completo de todas as funcionalidades.',
                'except' => [],
            ],
            'admin' => [
                'name' => 'Administrador',
                'description' => 'Acesso a quase tudo, exceto Configuracoes do Sistema.',
                'except' => ['settings'],
            ],
            'gerente' => [
                'name' => 'Gerente',
                'description' => 'Gestao de clientes, contratos, financeiro e relatorios.',
                'except' => [],
            ],
            'tecnico' => [
                'name' => 'Tecnico',
                'description' => 'Acesso a ordens de servico, clientes (leitura) e chamados.',
                'except' => [],
            ],
            'operador' => [
                'name' => 'Operador',
                'description' => 'Acesso basico: dashboard, clientes, planos, contratos e chamados.',
                'except' => [],
            ],
        ];

        $groupPermissions = [
            'superadmin' => array_fill_keys($allPermissions, true),

            'admin' => array_fill_keys(array_diff($allPermissions, ['settings']), true),

            'gerente' => [
                'dashboard' => true,
                'clients' => true,
                'plans' => true,
                'contracts' => true,
                'service_orders' => true,
                'technicians' => true,
                'equipment' => true,
                'manufacturers' => false,
                'suppliers' => false,
                'hotspot_coupons' => false,
                'mikrotik_servers' => false,
                'olts' => true,
                'ftth' => true,
                'provisioning' => false,
                'uptime' => false,
                'network_monitor' => false,
                'tickets' => true,
                'invoices' => true,
                'cash_book' => true,
                'reports' => true,
                'boleto' => true,
                'gateways' => false,
                'newsletter' => true,
                'backups' => false,
                'site_blocking' => false,
                'settings' => false,
                'stock' => true,
            ],

            'tecnico' => [
                'dashboard' => true,
                'clients' => true,
                'plans' => false,
                'contracts' => false,
                'service_orders' => true,
                'technicians' => false,
                'equipment' => false,
                'manufacturers' => false,
                'suppliers' => false,
                'hotspot_coupons' => false,
                'mikrotik_servers' => false,
                'olts' => false,
                'ftth' => true,
                'provisioning' => false,
                'uptime' => false,
                'network_monitor' => false,
                'tickets' => true,
                'invoices' => false,
                'cash_book' => false,
                'reports' => false,
                'boleto' => false,
                'gateways' => false,
                'newsletter' => false,
                'backups' => false,
                'site_blocking' => false,
                'settings' => false,
                'stock' => false,
            ],

            'operador' => [
                'dashboard' => true,
                'clients' => true,
                'plans' => true,
                'contracts' => true,
                'service_orders' => false,
                'technicians' => false,
                'equipment' => false,
                'manufacturers' => false,
                'suppliers' => false,
                'hotspot_coupons' => false,
                'mikrotik_servers' => false,
                'olts' => false,
                'ftth' => true,
                'provisioning' => false,
                'uptime' => false,
                'network_monitor' => false,
                'tickets' => true,
                'invoices' => false,
                'cash_book' => false,
                'reports' => false,
                'boleto' => false,
                'gateways' => false,
                'newsletter' => false,
                'backups' => false,
                'site_blocking' => false,
                'settings' => false,
                'stock' => false,
            ],
        ];

        foreach ($groupDefs as $slug => $def) {
            $group = UserGroup::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $def['name'],
                    'description' => $def['description'],
                    'is_active' => true,
                ]
            );

            $group->permissions()->delete();

            foreach ($allPermissions as $permKey) {
                $granted = $groupPermissions[$slug][$permKey] ?? false;
                GroupPermission::create([
                    'group_id' => $group->id,
                    'permission_key' => $permKey,
                    'granted' => $granted,
                ]);
            }
        }
    }
}
