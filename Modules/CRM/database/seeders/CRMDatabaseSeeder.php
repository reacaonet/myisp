<?php

namespace Modules\CRM\Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Modules\CRM\Models\Plan;
use Modules\CRM\Models\Client;
use Modules\CRM\Models\Contract;
use Modules\CRM\Models\Technician;
use Modules\CRM\Models\ServiceOrder;
use Modules\Core\Models\Server;
use Modules\Core\Models\Address;

class CRMDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('pt_BR');

        Server::create(['name' => 'Mikrotik Principal', 'ip' => '10.0.0.1', 'username' => 'admin', 'password' => 'mikrotik123', 'interface' => 'ether1', 'tipo' => 'mikrotik', 'porta_api' => 8728, 'porta_ssh' => 22, 'is_active' => true]);
        Server::create(['name' => 'Mikrotik Backup', 'ip' => '10.0.0.2', 'username' => 'admin', 'password' => 'mikrotik456', 'interface' => 'ether1', 'tipo' => 'mikrotik', 'porta_api' => 8728, 'porta_ssh' => 22, 'is_active' => false]);

        $plans = [
            ['name' => 'Básico 10M', 'slug' => 'basico-10m', 'download_speed' => 10240, 'upload_speed' => 5120, 'price' => 79.90, 'billing_cycle' => 'monthly', 'has_pppoe' => true, 'has_hotspot' => false, 'is_active' => true],
            ['name' => 'Premium 50M', 'slug' => 'premium-50m', 'download_speed' => 51200, 'upload_speed' => 25600, 'price' => 149.90, 'billing_cycle' => 'monthly', 'has_pppoe' => true, 'has_hotspot' => false, 'is_active' => true],
            ['name' => 'Master 100M', 'slug' => 'master-100m', 'download_speed' => 102400, 'upload_speed' => 51200, 'price' => 199.90, 'billing_cycle' => 'monthly', 'has_pppoe' => true, 'has_hotspot' => true, 'is_active' => true],
            ['name' => 'Fibra 300M', 'slug' => 'fibra-300m', 'download_speed' => 307200, 'upload_speed' => 153600, 'price' => 299.90, 'billing_cycle' => 'monthly', 'has_pppoe' => true, 'has_hotspot' => true, 'is_active' => true],
            ['name' => 'Dedicado 500M', 'slug' => 'dedicado-500m', 'download_speed' => 512000, 'upload_speed' => 512000, 'price' => 499.90, 'billing_cycle' => 'monthly', 'has_pppoe' => false, 'has_hotspot' => false, 'is_active' => true],
            ['name' => 'Plano Empresarial 1G', 'slug' => 'empresarial-1g', 'download_speed' => 1048576, 'upload_speed' => 524288, 'price' => 999.90, 'billing_cycle' => 'monthly', 'has_pppoe' => false, 'has_hotspot' => false, 'is_active' => true],
        ];

        foreach ($plans as $data) {
            Plan::create($data);
        }

        $technicians = [
            ['name' => 'Carlos Alberto', 'login' => 'carlos', 'senha' => 'tecnico123', 'cargo' => 'Técnico Senior', 'phone' => '(11) 9999-8888', 'cellphone' => '(11) 98888-7777', 'email' => 'carlos@myisp.com', 'city' => 'Sao Paulo', 'state' => 'SP', 'is_active' => true],
            ['name' => 'Fernanda Lima', 'login' => 'fernanda', 'senha' => 'tecnico123', 'cargo' => 'Técnica Pleno', 'phone' => '(11) 97777-6666', 'cellphone' => '(11) 97777-5555', 'email' => 'fernanda@myisp.com', 'city' => 'Sao Paulo', 'state' => 'SP', 'is_active' => true],
            ['name' => 'Roberto Santos', 'login' => 'roberto', 'senha' => 'tecnico123', 'cargo' => 'Técnico Junior', 'phone' => '(11) 96666-5555', 'cellphone' => '(11) 96666-4444', 'email' => 'roberto@myisp.com', 'city' => 'Guarulhos', 'state' => 'SP', 'is_active' => true],
            ['name' => 'Juliana Costa', 'login' => 'juliana', 'senha' => 'tecnico123', 'cargo' => 'Técnica Nível 1', 'phone' => '(11) 95555-4444', 'cellphone' => '(11) 95555-3333', 'email' => 'juliana@myisp.com', 'city' => 'Osasco', 'state' => 'SP', 'is_active' => true],
            ['name' => 'Marcos Oliveira', 'login' => 'marcos', 'senha' => 'tecnico123', 'cargo' => 'Técnico', 'phone' => '(11) 94444-3333', 'cellphone' => '(11) 94444-2222', 'email' => 'marcos@myisp.com', 'city' => 'Sao Paulo', 'state' => 'SP', 'is_active' => false],
        ];

        foreach ($technicians as $data) {
            Technician::create($data);
        }

        $clientData = [
            ['name' => 'João Silva', 'document' => '12345678909', 'rg' => '12.345.678-9', 'email' => 'joao.silva@email.com', 'login' => 'joao.silva', 'phone' => '(11) 3333-4455', 'cellphone' => '(11) 98877-6655', 'birth_date' => '1990-05-15', 'estado_civil' => 'solteiro', 'naturalidade' => 'São Paulo', 'data_entrada' => '2024-01-15', 'tipo_assinante' => 'pf', 'tipo_utilizacao' => 'residencial', 'grupo' => 'ouro', 'type' => 'individual', 'status' => 'active', 'street' => 'Rua das Flores', 'number' => '123', 'neighborhood' => 'Centro', 'city' => 'São Paulo', 'state' => 'SP', 'zipcode' => '01001-000'],
            ['name' => 'Maria Souza', 'document' => '52998224725', 'rg' => '98.765.432-1', 'email' => 'maria.souza@email.com', 'login' => 'maria.souza', 'phone' => '(11) 4444-5566', 'cellphone' => '(11) 97766-5544', 'birth_date' => '1985-08-22', 'estado_civil' => 'casado', 'naturalidade' => 'São Paulo', 'data_entrada' => '2024-03-10', 'tipo_assinante' => 'pf', 'tipo_utilizacao' => 'residencial', 'grupo' => 'prata', 'type' => 'individual', 'status' => 'active', 'street' => 'Av Paulista', 'number' => '1000', 'neighborhood' => 'Bela Vista', 'city' => 'São Paulo', 'state' => 'SP', 'zipcode' => '01310-100'],
            ['name' => 'Pedro Alves', 'document' => '11144477735', 'rg' => '11.222.333-4', 'email' => 'pedro.alves@email.com', 'login' => 'pedro.alves', 'phone' => '(11) 5555-6677', 'cellphone' => '(11) 96655-4433', 'birth_date' => '1978-12-01', 'estado_civil' => 'casado', 'naturalidade' => 'São Paulo', 'data_entrada' => '2024-06-20', 'tipo_assinante' => 'pf', 'tipo_utilizacao' => 'residencial', 'grupo' => 'bronze', 'type' => 'individual', 'status' => 'active', 'street' => 'Rua Augusta', 'number' => '500', 'neighborhood' => 'Consolação', 'city' => 'São Paulo', 'state' => 'SP', 'zipcode' => '01304-000'],
            ['name' => 'Ana Pereira', 'document' => '40730217027', 'rg' => '55.666.777-8', 'email' => 'ana.pereira@email.com', 'login' => 'ana.pereira', 'phone' => '(11) 6666-7788', 'cellphone' => '(11) 95544-3322', 'birth_date' => '1995-03-10', 'estado_civil' => 'solteiro', 'naturalidade' => 'São Paulo', 'data_entrada' => '2024-08-05', 'tipo_assinante' => 'pf', 'tipo_utilizacao' => 'residencial', 'grupo' => 'ouro', 'type' => 'individual', 'status' => 'active', 'street' => 'Rua Oscar Freire', 'number' => '800', 'neighborhood' => 'Jardins', 'city' => 'São Paulo', 'state' => 'SP', 'zipcode' => '01426-001'],
            ['name' => 'Lucas Oliveira', 'document' => '95512719807', 'rg' => '99.888.777-6', 'email' => 'lucas.oliveira@email.com', 'login' => 'lucas.oliveira', 'phone' => '(11) 7777-8899', 'cellphone' => '(11) 94433-2211', 'birth_date' => '2000-07-18', 'estado_civil' => 'solteiro', 'naturalidade' => 'São Paulo', 'data_entrada' => '2025-01-10', 'tipo_assinante' => 'pf', 'tipo_utilizacao' => 'residencial', 'grupo' => 'prata', 'type' => 'individual', 'status' => 'active', 'street' => 'Rua da Consolação', 'number' => '2000', 'neighborhood' => 'Consolação', 'city' => 'São Paulo', 'state' => 'SP', 'zipcode' => '01302-000'],
            ['name' => 'Empresa Tech Ltda', 'document' => '11222333000181', 'rg' => null, 'email' => 'contato@techltda.com.br', 'login' => 'techltda', 'phone' => '(11) 8888-9900', 'cellphone' => '(11) 98888-9900', 'birth_date' => null, 'estado_civil' => null, 'naturalidade' => null, 'data_entrada' => '2024-02-01', 'tipo_assinante' => 'pj', 'tipo_utilizacao' => 'comercial', 'grupo' => 'diamante', 'type' => 'legal', 'status' => 'active', 'street' => 'Av Brigadeiro Faria Lima', 'number' => '1500', 'neighborhood' => 'Pinheiros', 'city' => 'São Paulo', 'state' => 'SP', 'zipcode' => '01452-001'],
            ['name' => 'Comercio Digital ME', 'document' => '33444555000181', 'rg' => null, 'email' => 'adm@comerciodigital.com', 'login' => 'comerciodigital', 'phone' => '(11) 9999-0011', 'cellphone' => '(11) 99999-0011', 'birth_date' => null, 'estado_civil' => null, 'naturalidade' => null, 'data_entrada' => '2024-05-15', 'tipo_assinante' => 'pj', 'tipo_utilizacao' => 'comercial', 'grupo' => 'ouro', 'type' => 'legal', 'status' => 'active', 'street' => 'Rua 25 de Março', 'number' => '300', 'neighborhood' => 'Centro', 'city' => 'São Paulo', 'state' => 'SP', 'zipcode' => '01021-000'],
            ['name' => 'Carla Mendes', 'document' => '87459499137', 'rg' => '77.888.999-0', 'email' => 'carla.mendes@email.com', 'login' => 'carla.mendes', 'phone' => '(11) 1111-2233', 'cellphone' => '(11) 91111-2233', 'birth_date' => '1992-11-30', 'estado_civil' => 'divorciado', 'naturalidade' => 'São Paulo', 'data_entrada' => '2025-03-01', 'tipo_assinante' => 'pf', 'tipo_utilizacao' => 'residencial', 'grupo' => 'bronze', 'type' => 'individual', 'status' => 'suspended', 'street' => 'Rua Vergueiro', 'number' => '2500', 'neighborhood' => 'Vila Mariana', 'city' => 'São Paulo', 'state' => 'SP', 'zipcode' => '04101-000'],
            ['name' => 'Rafael Costa', 'document' => '00506887235', 'rg' => '12.312.312-3', 'email' => 'rafael.costa@email.com', 'login' => 'rafael.costa', 'phone' => '(11) 2222-3344', 'cellphone' => '(11) 92222-3344', 'birth_date' => '1988-09-05', 'estado_civil' => 'casado', 'naturalidade' => 'São Paulo', 'data_entrada' => '2025-05-20', 'tipo_assinante' => 'pf', 'tipo_utilizacao' => 'residencial', 'grupo' => 'prata', 'type' => 'individual', 'status' => 'active', 'street' => 'Rua Teodoro Sampaio', 'number' => '1800', 'neighborhood' => 'Pinheiros', 'city' => 'São Paulo', 'state' => 'SP', 'zipcode' => '05405-000'],
            ['name' => 'Escola Aprender Ltda', 'document' => '55667788000186', 'rg' => null, 'email' => 'admin@escolaaprender.com.br', 'login' => 'escolaaprender', 'phone' => '(11) 3333-4455', 'cellphone' => '(11) 93333-4455', 'birth_date' => null, 'estado_civil' => null, 'naturalidade' => null, 'data_entrada' => '2024-09-01', 'tipo_assinante' => 'pj', 'tipo_utilizacao' => 'institucional', 'grupo' => 'diamante', 'type' => 'legal', 'status' => 'active', 'street' => 'Rua das Acácias', 'number' => '700', 'neighborhood' => 'Morumbi', 'city' => 'São Paulo', 'state' => 'SP', 'zipcode' => '05615-000'],
        ];

        $planIds = Plan::pluck('id')->toArray();
        $technicianIds = Technician::where('is_active', true)->pluck('id')->toArray();
        $cities = ['São Paulo', 'Guarulhos', 'Osasco', 'Santo André', 'Barueri'];
        $billingTypes = ['boleto', 'pix', 'credit_card'];
        $statuses = ['open', 'pending', 'in_progress', 'resolved', 'closed'];

        foreach ($clientData as $data) {
            $addressData = [
                'street' => $data['street'],
                'number' => $data['number'],
                'neighborhood' => $data['neighborhood'],
                'city' => $data['city'],
                'state' => $data['state'],
                'zipcode' => $data['zipcode'],
            ];

            $client = Client::create([
                'codigo' => str_pad($faker->unique()->numberBetween(100, 999), 3, '0', STR_PAD_LEFT),
                'name' => $data['name'],
                'document' => $data['document'],
                'rg' => $data['rg'],
                'email' => $data['email'],
                'login' => $data['login'],
                'senha' => '123456',
                'phone' => $data['phone'],
                'cellphone' => $data['cellphone'],
                'birth_date' => $data['birth_date'],
                'estado_civil' => $data['estado_civil'],
                'naturalidade' => $data['naturalidade'],
                'data_entrada' => $data['data_entrada'],
                'vcto_contrato' => $faker->boolean(70) ? $faker->dateTimeBetween('+1 month', '+6 months')->format('Y-m-d') : null,
                'pai' => $faker->boolean(60) ? $faker->name() : null,
                'mae' => $faker->boolean(80) ? $faker->name('female') : null,
                'type' => $data['type'],
                'nf' => $data['tipo_assinante'] === 'pj',
                'tipo_assinante' => $data['tipo_assinante'],
                'tipo_utilizacao' => $data['tipo_utilizacao'],
                'grupo' => $data['grupo'],
                'status' => $data['status'],
                'notes' => $faker->optional(0.3)->sentence(),
            ]);

            $client->addresses()->create($addressData);

            if ($client->type === 'legal' && $faker->boolean(70)) {
                $client->addresses()->create([
                    'street' => $faker->streetName,
                    'number' => (string) $faker->buildingNumber,
                    'complement' => 'Sala ' . $faker->randomNumber(2),
                    'neighborhood' => $faker->citySuffix,
                    'city' => $faker->city,
                    'state' => 'SP',
                    'zipcode' => str_replace('-', '', $faker->postcode),
                ]);
            }

            $numContracts = $client->type === 'legal' ? rand(1, 2) : 1;
            for ($i = 0; $i < $numContracts; $i++) {
                $planId = $faker->randomElement($planIds);
                $plan = Plan::find($planId);
                $activationDate = $faker->dateTimeBetween($client->data_entrada, 'now');
                $dueDay = $faker->randomElement([5, 10, 15, 20, 25]);
                $status = $faker->randomElement(['active', 'active', 'active', 'active', 'suspended', 'canceled']);

                Contract::create([
                    'pedido' => 'PED-' . str_pad($faker->unique()->numberBetween(1000, 9999), 4, '0', STR_PAD_LEFT),
                    'client_id' => $client->id,
                    'plan_id' => $planId,
                    'activation_date' => $activationDate->format('Y-m-d'),
                    'due_date' => (clone $activationDate)->modify('+12 months')->format('Y-m-d'),
                    'due_day' => $dueDay,
                    'status' => $status,
                    'situacao' => $status === 'active' ? 'A' : ($status === 'suspended' ? 'S' : 'C'),
                    'billing_type' => $faker->randomElement($billingTypes),
                    'pppoe_user' => $client->login . ($i > 0 ? ($i + 1) : ''),
                    'pppoe_password' => $faker->password(8, 12),
                    'ip_address' => $faker->boolean(80) ? $faker->localIpv4() : null,
                    'route_ip' => $faker->boolean(30) ? $faker->localIpv4() : null,
                    'mac_address' => $faker->boolean(60) ? $faker->macAddress() : null,
                    'mac_wireless' => $faker->boolean(40) ? $faker->macAddress() : null,
                    'discount' => $faker->boolean(20) ? $faker->randomFloat(2, 5, 30) : 0,
                    'acrescimo' => $faker->boolean(10) ? $faker->randomFloat(2, 5, 20) : 0,
                    'observacao' => $faker->boolean(30) ? $faker->sentence() : null,
                    'install_street' => $addressData['street'],
                    'install_number' => $addressData['number'],
                    'install_neighborhood' => $addressData['neighborhood'],
                    'install_city' => $faker->randomElement($cities),
                    'install_state' => 'SP',
                    'install_zipcode' => $addressData['zipcode'],
                    'tipo_conexao' => $faker->randomElement(['pppoe', 'dhcp', 'hotspot', 'iparp']),
                    'autobloqueio' => $faker->boolean(80),
                ]);
            }
        }

        $contractIds = Contract::pluck('id')->toArray();
        $clientIds = Client::pluck('id')->toArray();

        $osStatusOptions = ['O', 'A', 'F', 'C'];
        for ($i = 0; $i < 15; $i++) {
            $clientId = $faker->randomElement($clientIds);
            $clientContracts = Contract::where('client_id', $clientId)->pluck('id')->toArray();
            $contractId = !empty($clientContracts) ? $faker->randomElement($clientContracts) : null;
            $planId = $contractId ? Contract::find($contractId)->plan_id : $faker->randomElement($planIds);
            $emissao = $faker->dateTimeBetween('-6 months', 'now');
            $encerrado = $faker->boolean(60);
            $situacao = $encerrado ? 'F' : $faker->randomElement(['O', 'A']);

            ServiceOrder::create([
                'codigo' => 'OS-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'client_id' => $clientId,
                'contract_id' => $contractId,
                'plan_id' => $planId,
                'technician_id' => $faker->boolean(70) ? $faker->randomElement($technicianIds) : null,
                'situacao' => $situacao,
                'status' => $encerrado ? 'closed' : $faker->randomElement(['active', 'active', 'active', 'canceled']),
                'encerrado' => $encerrado,
                'servico' => $faker->randomElement(['Instalação', 'Manutenção', 'Troca de Equipamento', 'Suporte Técnico', 'Remoção de Pendências']),
                'tipo_servico' => $faker->randomElement(['instalacao', 'manutencao', 'cancelamento', 'recuperacao', 'orcamento', 'visita_tecnica']),
                'emissao' => $emissao->format('Y-m-d'),
                'hora_abertura' => $faker->randomElement(['08:00', '09:30', '10:15', '13:00', '14:45', '16:30']),
                'orcamento' => $faker->boolean(40) ? $faker->dateTimeBetween($emissao, '+1 month')->format('Y-m-d') : null,
                'aprovacao' => $faker->boolean(30) ? $faker->dateTimeBetween($emissao, '+1 month')->format('Y-m-d') : null,
                'saida' => $faker->boolean(50) ? $faker->dateTimeBetween($emissao, '+2 months')->format('Y-m-d') : null,
                'data_agendamento' => $faker->boolean(60) ? $faker->dateTimeBetween($emissao, '+1 week')->format('Y-m-d') : null,
                'hora_agendamento' => $faker->boolean(60) ? $faker->randomElement(['08:00', '09:00', '10:00', '13:00', '14:00', '15:00']) : null,
                'problema' => $faker->boolean(80) ? $faker->sentence(6) : null,
                'diagnostico' => $faker->boolean(60) ? $faker->sentence(8) : null,
                'solucao' => $faker->boolean(50) ? $faker->sentence(10) : null,
                'atendente' => $faker->boolean(80) ? $faker->name() : null,
                'preco' => $faker->boolean(60) ? $faker->randomFloat(2, 0, 500) : 0,
            ]);
        }
    }
}
