<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WhatsAppService;
use Modules\Billing\Models\Invoice;

class NotifyOverdue extends Command
{
    protected $signature = 'notifications:overdue {--days=1 : Only notify invoices overdue by this many days}';
    protected $description = 'Send WhatsApp notifications for overdue invoices';

    public function handle(WhatsAppService $whatsapp)
    {
        $days = (int) $this->option('days');
        $sent = 0;

        $invoices = Invoice::with('client')
            ->where('status', 'overdue')
            ->whereDate('due_date', '<=', now()->subDays($days))
            ->get();

        foreach ($invoices as $invoice) {
            $client = $invoice->client;
            if (!$client || !$client->cellphone) {
                continue;
            }

            $ok = $whatsapp->notifyOverdue(
                ['name' => $client->name, 'cellphone' => $client->cellphone, 'phone' => $client->phone],
                $invoice->total ?? $invoice->amount,
                $invoice->due_date->format('d/m/Y')
            );

            if ($ok) {
                $sent++;
            }
        }

        $this->info("Notifications sent: {$sent}");
    }
}
