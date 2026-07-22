<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\CRM\Models\MikrotikServer;

class MikrotikServerSeeder extends Seeder
{
    public function run(): void
    {
        $servers = [
            [
                'name' => 'MikroTik RB1100AHX4 - Principal',
                'ip' => '192.168.1.1',
                'port' => 8728,
                'login' => 'admin',
                'senha' => 'admin',
                'type' => 'both',
                'is_active' => true,
                'notes' => 'Servidor principal de concentracao PPPoE e Hotspot',
            ],
            [
                'name' => 'MikroTik hAP ac2 - Filial',
                'ip' => '192.168.2.1',
                'port' => 8728,
                'login' => 'admin',
                'senha' => 'admin',
                'type' => 'hotspot',
                'is_active' => true,
                'notes' => 'Hotspot para area de clientes',
            ],
        ];

        foreach ($servers as $s) {
            MikrotikServer::create($s);
        }

        $this->command->info('Servidores MikroTik criados com sucesso.');
    }
}
