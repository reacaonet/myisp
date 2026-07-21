<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\CRM\Models\Contract;
use Modules\Billing\Models\Invoice;
use Carbon\Carbon;

class GenerateInvoices extends Command
{
    protected $signature = 'invoices:generate {--date= : Reference date (default: today)}';
    protected $description = 'Generate invoices for all active contracts based on due day';

    public function handle()
    {
        $refDate = $this->option('date') ? Carbon::parse($this->option('date')) : now()->startOfMonth();
        $month = (int) $refDate->format('m');
        $year = (int) $refDate->format('Y');
        $generated = 0;

        $contracts = Contract::with('plan')->where('status', 'active')->get();

        foreach ($contracts as $contract) {
            $exists = Invoice::where('contract_id', $contract->id)
                ->where('mes', $month)
                ->where('ano', $year)
                ->exists();

            if ($exists) {
                continue;
            }

            $dueDay = min($contract->due_day, 28);
            $dueDate = Carbon::createFromDate($year, $month, $dueDay);

            $amount = $contract->plan->price - $contract->discount + $contract->acrescimo;
            if ($amount < 0) {
                $amount = 0;
            }

            $invoiceNumber = 'INV-' . $year . str_pad($month, 2, '0') . '-' . str_pad(Invoice::max('id') + 1, 5, '0', STR_PAD_LEFT);

            Invoice::create([
                'client_id' => $contract->client_id,
                'contract_id' => $contract->id,
                'invoice_number' => $invoiceNumber,
                'amount' => $contract->plan->price,
                'discount' => $contract->discount,
                'acrescimo' => $contract->acrescimo,
                'total' => $amount,
                'due_date' => $dueDate,
                'dia' => $contract->due_day,
                'mes' => $month,
                'ano' => $year,
                'status' => 'pending',
                'insento' => $contract->insento,
            ]);

            $generated++;
        }

        $this->info("Generated {$generated} invoices for {$month}/{$year}.");
    }
}
