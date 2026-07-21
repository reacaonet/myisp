<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Modules\CRM\Models\Client;

class HashClientPasswords extends Command
{
    protected $signature = 'clients:hash-passwords';
    protected $description = 'Hash existing plain-text senha fields for all clients';

    public function handle()
    {
        $count = 0;
        foreach (Client::whereNotNull('senha')->cursor() as $client) {
            if (!Hash::isHashed($client->senha)) {
                $client->senha = Hash::make($client->senha);
                $client->save();
                $count++;
            }
        }

        $this->info("{$count} client passwords hashed.");
    }
}
