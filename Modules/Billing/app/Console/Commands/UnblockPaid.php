<?php

namespace Modules\Billing\Console\Commands;

use Illuminate\Console\Command;
use Modules\Billing\Models\Invoice;
use Modules\CRM\Models\Contract;

class UnblockPaid extends Command
{
    protected $signature = 'billing:unblock-paid';
    protected $description = 'Reativar contratos de clientes que pagaram todas as faturas pendentes';

    public function handle(): int
    {
        $blockedContracts = Contract::with('client')
            ->where('status', 'suspended')
            ->get();

        $unblocked = 0;

        foreach ($blockedContracts as $contract) {
            $hasPending = Invoice::where('contract_id', $contract->id)
                ->whereIn('status', ['pending', 'overdue'])
                ->exists();

            if (!$hasPending) {
                $contract->update(['status' => 'active']);

                Invoice::where('contract_id', $contract->id)
                    ->where('auto_blocked', true)
                    ->update(['auto_blocked' => false]);

                $unblocked++;
                $this->line("Reativado: {$contract->client?->name} (Contrato #{$contract->id})");
            }
        }

        $this->info("{$unblocked} contratos reativados.");
        return self::SUCCESS;
    }
}
