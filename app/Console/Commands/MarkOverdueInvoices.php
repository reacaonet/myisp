<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Billing\Models\Invoice;

class MarkOverdueInvoices extends Command
{
    protected $signature = 'invoices:mark-overdue';
    protected $description = 'Mark pending invoices as overdue when past due date';

    public function handle()
    {
        $updated = Invoice::where('status', 'pending')
            ->where('due_date', '<', now()->startOfDay())
            ->update(['status' => 'overdue']);

        $this->info("{$updated} invoices marked as overdue.");
    }
}
