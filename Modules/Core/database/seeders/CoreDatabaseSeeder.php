<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\User;

class CoreDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@myisp.com',
            'password' => 'admin',
            'phone' => '(11) 99999-0001',
            'role' => 'admin',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Operador',
            'email' => 'operador@myisp.com',
            'password' => '123456',
            'phone' => '(11) 99999-0002',
            'role' => 'operator',
            'is_active' => true,
        ]);
    }
}
