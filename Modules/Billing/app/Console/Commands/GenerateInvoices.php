<?php

namespace Modules\Billing\Console\Commands;

use Illuminate\Console\Command;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\BillingSetting;
use Modules\CRM\Models\Contract;

class GenerateInvoices extends Command
{
    protected $signature = 'billing:generate-invoices';
    protected $description = 'Gerar faturas mensais para contratos ativos';

    public function handle(): int
    {
        $settings = BillingSetting::get();
        $today = now();
        $count = 0;

        $contracts = Contract::with('plan', 'client')
            ->where('status', 'active')
            ->where('insento', false)
            ->get();

        foreach ($contracts as $contract) {
            if (!$contract->plan) {
                continue;
            }

            $hasPaidOrPending = Invoice::where('contract_id', $contract->id)
                ->where('avulso', false)
                ->where('status', '!=', 'canceled')
                ->where(function ($q) use ($today) {
                    $q->where('status', '!=', 'paid')
                      ->orWhere(function ($q2) use ($today) {
                          $q2->where('status', 'paid')
                             ->whereYear('paid_date', $today->year)
                             ->whereMonth('paid_date', $today->month);
                      });
                })
                ->exists();

            if ($hasPaidOrPending) {
                continue;
            }

            $dueDay = min($contract->due_day ?? 1, 28);

            $dueDate = $today->copy()->day($dueDay);
            if ($dueDate->isPast()) {
                $dueDate = $dueDate->addMonth();
            }

            $generateDate = $dueDate->copy()->subDays($settings->dias_geracao_fatura);
            if ($today->lt($generateDate)) {
                continue;
            }

            $amount = $contract->plan->price;
            $discount = $contract->discount ?? 0;
            $acrescimo = $contract->acrescimo ?? 0;

            Invoice::create([
                'client_id' => $contract->client_id,
                'contract_id' => $contract->id,
                'invoice_number' => 'FAT-' . $dueDate->format('Ymd') . '-' . str_pad(Invoice::max('id') + 1, 4, '0', STR_PAD_LEFT),
                'amount' => $amount,
                'discount' => $discount,
                'acrescimo' => $acrescimo,
                'total' => $amount - $discount + $acrescimo,
                'due_date' => $dueDate,
                'status' => 'pending',
                'mes_parcela' => $dueDate->month,
            ]);

            $count++;
        }

        $this->info("{$count} faturas geradas com sucesso.");
        return self::SUCCESS;
    }
}
