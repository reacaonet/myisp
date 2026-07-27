<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\User;
use Modules\Core\Models\UserGroup;

class CoreDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserGroupSeeder::class,
        ]);

        $superadmin = UserGroup::where('slug', 'superadmin')->first();
        $operador = UserGroup::where('slug', 'operador')->first();

        User::create([
            'name' => 'Administrador',
            'email' => 'admin@myisp.com',
            'password' => 'admin',
            'phone' => '(11) 99999-0001',
            'role' => 'superadmin',
            'user_group_id' => $superadmin?->id,
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Operador',
            'email' => 'operador@myisp.com',
            'password' => '123456',
            'phone' => '(11) 99999-0002',
            'role' => 'operador',
            'user_group_id' => $operador?->id,
            'is_active' => true,
        ]);
    }
}
