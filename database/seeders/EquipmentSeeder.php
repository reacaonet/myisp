<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\CRM\Models\Manufacturer;
use Modules\CRM\Models\Equipment;
use Modules\CRM\Models\EquipmentAssignment;
use Modules\CRM\Models\Client;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $manufacturers = [
            ['name' => 'Intelbras', 'website' => 'https://www.intelbras.com.br', 'phone' => '0800 727 9999', 'email' => 'contato@intelbras.com.br'],
            ['name' => 'MikroTik', 'website' => 'https://mikrotik.com', 'phone' => '+371 6740 1400', 'email' => 'info@mikrotik.com'],
            ['name' => 'TP-Link', 'website' => 'https://www.tp-link.com.br', 'phone' => '0800 887 5678', 'email' => 'suporte@tp-link.com.br'],
            ['name' => 'Huawei', 'website' => 'https://www.huawei.com', 'phone' => '+86 755 2878 0808', 'email' => 'enterprise@huawei.com'],
            ['name' => 'Nokia', 'website' => 'https://www.nokia.com', 'phone' => '+358 7180 08000', 'email' => 'info@nokia.com'],
            ['name' => 'ZTE', 'website' => 'https://www.zte.com.cn', 'phone' => '+86 755 2677 9999', 'email' => 'info@zte.com.cn'],
        ];

        $createdManufacturers = collect();
        foreach ($manufacturers as $m) {
            $createdManufacturers[] = Manufacturer::create($m);
        }

        $equipmentData = [
            ['name' => 'ONU Intelbras FV1100G', 'model' => 'FV1100G', 'type' => 'onu', 'manufacturer_id' => $createdManufacturers[0]->id, 'cost' => 189.90, 'sale_price' => 299.90, 'supplier' => 'Distribuidora TI', 'quantity' => 50, 'status' => 'available'],
            ['name' => 'ONU Huawei HG8245H', 'model' => 'HG8245H', 'type' => 'onu', 'manufacturer_id' => $createdManufacturers[3]->id, 'cost' => 220.00, 'sale_price' => 350.00, 'supplier' => 'Distribuidora TI', 'quantity' => 30, 'status' => 'available'],
            ['name' => 'ONU ZTE F670L', 'model' => 'F670L', 'type' => 'onu', 'manufacturer_id' => $createdManufacturers[5]->id, 'cost' => 175.00, 'sale_price' => 280.00, 'supplier' => 'Distribuidora TI', 'quantity' => 40, 'status' => 'available'],
            ['name' => 'Roteador MikroTik hAP ac2', 'model' => 'hAP ac2', 'type' => 'router', 'manufacturer_id' => $createdManufacturers[1]->id, 'cost' => 320.00, 'sale_price' => 499.90, 'supplier' => 'MikroTik BR', 'quantity' => 20, 'status' => 'available'],
            ['name' => 'Roteador MikroTik RB750Gr3', 'model' => 'RB750Gr3', 'type' => 'router', 'manufacturer_id' => $createdManufacturers[1]->id, 'cost' => 280.00, 'sale_price' => 420.00, 'supplier' => 'MikroTik BR', 'quantity' => 15, 'status' => 'available'],
            ['name' => 'Switch Intelbras FS2024', 'model' => 'FS2024', 'type' => 'switch', 'manufacturer_id' => $createdManufacturers[0]->id, 'cost' => 450.00, 'sale_price' => 699.90, 'supplier' => 'Distribuidora TI', 'quantity' => 10, 'status' => 'available'],
            ['name' => 'Switch TP-Link TL-SG1024DE', 'model' => 'TL-SG1024DE', 'type' => 'switch', 'manufacturer_id' => $createdManufacturers[2]->id, 'cost' => 380.00, 'sale_price' => 580.00, 'supplier' => 'Distribuidora TI', 'quantity' => 8, 'status' => 'available'],
            ['name' => 'Access Point Intelbras IW150', 'model' => 'IW150', 'type' => 'access_point', 'manufacturer_id' => $createdManufacturers[0]->id, 'cost' => 150.00, 'sale_price' => 249.90, 'supplier' => 'Distribuidora TI', 'quantity' => 25, 'status' => 'available'],
            ['name' => 'Access Point TP-Link EAP245', 'model' => 'EAP245', 'type' => 'access_point', 'manufacturer_id' => $createdManufacturers[2]->id, 'cost' => 350.00, 'sale_price' => 520.00, 'supplier' => 'Distribuidora TI', 'quantity' => 12, 'status' => 'available'],
            ['name' => 'Antena Ubiquiti LiteBeam 5AC', 'model' => 'LiteBeam 5AC', 'type' => 'antenna', 'manufacturer_id' => null, 'cost' => 280.00, 'sale_price' => 450.00, 'supplier' => 'Distribuidora TI', 'quantity' => 15, 'status' => 'available'],
            ['name' => 'Cabo Cat6 305m', 'model' => 'Cat6 UTP', 'type' => 'cable', 'manufacturer_id' => null, 'cost' => 320.00, 'sale_price' => 480.00, 'supplier' => 'Cabo Brasil', 'quantity' => 20, 'status' => 'available'],
            ['name' => 'MikroTik CCR1036-12G-4S', 'model' => 'CCR1036-12G-4S', 'type' => 'router', 'manufacturer_id' => $createdManufacturers[1]->id, 'cost' => 3500.00, 'sale_price' => 5200.00, 'supplier' => 'MikroTik BR', 'quantity' => 2, 'status' => 'allocated'],
        ];

        $createdEquipment = collect();
        foreach ($equipmentData as $e) {
            $e['available_quantity'] = $e['quantity'];
            $e['purchase_date'] = now()->subMonths(rand(1, 12))->format('Y-m-d');
            $e['warranty_until'] = now()->addMonths(rand(6, 24))->format('Y-m-d');
            $createdEquipment[] = Equipment::create($e);
        }

        $clients = Client::inRandomOrder()->limit(5)->get();
        if ($clients->count() > 0) {
            $assignments = [
                ['equipment_id' => $createdEquipment[3]->id, 'client_id' => $clients[0]->id, 'quantity' => 1, 'assigned_at' => now()->subDays(30)],
                ['equipment_id' => $createdEquipment[0]->id, 'client_id' => $clients[0]->id, 'quantity' => 1, 'assigned_at' => now()->subDays(25)],
                ['equipment_id' => $createdEquipment[1]->id, 'client_id' => $clients[1]->id, 'quantity' => 1, 'assigned_at' => now()->subDays(20)],
                ['equipment_id' => $createdEquipment[7]->id, 'client_id' => $clients[2]->id, 'quantity' => 2, 'assigned_at' => now()->subDays(15)],
                ['equipment_id' => $createdEquipment[11]->id, 'client_id' => $clients[3]->id, 'quantity' => 1, 'assigned_at' => now()->subDays(10)],
            ];

            foreach ($assignments as $a) {
                EquipmentAssignment::create($a);
                Equipment::where('id', $a['equipment_id'])->decrement('available_quantity', $a['quantity']);
            }
        }

        $this->command->info('Equipamentos e fabricantes criados com sucesso.');
    }
}
