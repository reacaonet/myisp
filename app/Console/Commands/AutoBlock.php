<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\CRM\Models\Contract;
use Modules\Billing\Models\Invoice;

class AutoBlock extends Command
{
    protected $signature = 'clients:auto-block {--days=5 : Days past due before blocking}';
    protected $description = 'Auto-suspend contracts with overdue invoices';

    public function handle()
    {
        $days = (int) $this->option('days');
        $blocked = 0;

        $contracts = Contract::where('status', 'active')
            ->where('autobloqueio', true)
            ->where('insento', false)
            ->get();

        foreach ($contracts as $contract) {
            $overdue = Invoice::where('contract_id', $contract->id)
                ->where('status', 'overdue')
                ->where('due_date', '<', now()->subDays($days))
                ->exists();

            if ($overdue) {
                $contract->update([
                    'status' => 'suspended',
                    'situacao' => 'S',
                ]);
                $blocked++;
            }
        }

        $this->info("{$blocked} contracts suspended due to overdue invoices.");
    }
}
