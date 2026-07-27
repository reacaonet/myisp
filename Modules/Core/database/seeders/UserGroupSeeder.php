<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\UserGroup;
use Modules\Core\Models\GroupPermission;

class UserGroupSeeder extends Seeder
{
    public function run(): void
    {
        $allPermissions = [
            'dashboard', 'clients', 'plans', 'contracts', 'service_orders',
            'technicians', 'equipment', 'manufacturers', 'suppliers',
            'hotspot_coupons', 'mikrotik_servers', 'provisioning', 'uptime',
            'network_monitor', 'tickets', 'invoices', 'cash_book', 'reports',
            'boleto', 'newsletter', 'backups', 'site_blocking', 'settings',
        ];

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
                'provisioning' => false,
                'uptime' => false,
                'network_monitor' => false,
                'tickets' => true,
                'invoices' => true,
                'cash_book' => true,
                'reports' => true,
                'boleto' => true,
                'newsletter' => true,
                'backups' => false,
                'site_blocking' => false,
                'settings' => false,
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
                'provisioning' => false,
                'uptime' => false,
                'network_monitor' => false,
                'tickets' => true,
                'invoices' => false,
                'cash_book' => false,
                'reports' => false,
                'boleto' => false,
                'newsletter' => false,
                'backups' => false,
                'site_blocking' => false,
                'settings' => false,
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
                'provisioning' => false,
                'uptime' => false,
                'network_monitor' => false,
                'tickets' => true,
                'invoices' => false,
                'cash_book' => false,
                'reports' => false,
                'boleto' => false,
                'newsletter' => false,
                'backups' => false,
                'site_blocking' => false,
                'settings' => false,
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
