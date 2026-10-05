<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

class GroupPermission extends Model
{
    protected $fillable = ['group_id', 'permission_key', 'granted'];

    protected function casts(): array
    {
        return ['granted' => 'boolean'];
    }

    public function group()
    {
        return $this->belongsTo(UserGroup::class, 'group_id');
    }

    /**
     * Catalogo canonico de permissoes (usado pelo CRUD de grupos, pelo
     * seeder e pelo middleware `group.permission` das rotas).
     */
    public static function MENU_PERMISSIONS(): array
    {
        return [
            'dashboard' => 'Dashboard',
            'clients' => 'Clientes',
            'plans' => 'Planos',
            'plans_manage' => 'Planos (criar e editar)',
            'contracts' => 'Contratos',
            'service_orders' => 'Ordens de Servico',
            'technicians' => 'Tecnicos',
            'equipment' => 'Equipamentos',
            'manufacturers' => 'Fabricantes',
            'suppliers' => 'Fornecedores',
            'hotspot_coupons' => 'Cupons Hotspot',
            'mikrotik_servers' => 'Servidores MikroTik',
            'olts' => 'OLTs',
            'ftth' => 'FTTH (projetos, CTOs e caixas)',
            'provisioning' => 'Provisionamento',
            'uptime' => 'Uptime',
            'network_monitor' => 'Monitoramento de Rede',
            'tickets' => 'Chamados',
            'invoices' => 'Faturas',
            'cash_book' => 'Livro Caixa',
            'reports' => 'Relatorios',
            'boleto' => 'Boletos',
            'gateways' => 'Gateways de Pagamento',
            'newsletter' => 'Mala Direta',
            'backups' => 'Backups MikroTik',
            'site_blocking' => 'Bloqueio de Sites',
            'franchisees' => 'Franqueados',
            'settings' => 'Configuracoes',
            'stock' => 'Estoque',
        ];
    }
}
