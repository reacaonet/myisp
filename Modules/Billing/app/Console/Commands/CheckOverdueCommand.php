<?php

namespace Modules\Billing\Console\Commands;

use Illuminate\Console\Command;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Mail\InvoiceOverdue;
use Illuminate\Support\Facades\Mail;

class CheckOverdueCommand extends Command
{
    protected $signature = 'billing:check-overdue-emails';
    protected $description = 'Verificar faturas vencidas e enviar emails de notificacao';

    public function handle(): int
    {
        $overdueInvoices = Invoice::with('client')
            ->where('status', 'pending')
            ->where('due_date', '<', now()->startOfDay())
            ->get();

        $sent = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($overdueInvoices as $invoice) {
            if (!$invoice->client || !$invoice->client->email) {
                $skipped++;
                continue;
            }

            $daysOverdue = now()->diffInDays($invoice->due_date);

            try {
                Mail::to($invoice->client->email)->send(new InvoiceOverdue($invoice, $daysOverdue));
                $sent++;
                $this->line("Email enviado: {$invoice->client->name} - {$invoice->invoice_number} ({$daysOverdue} dias)");
            } catch (\Exception $e) {
                $errors++;
                $this->error("Erro ao enviar para {$invoice->invoice_number}: {$e->getMessage()}");
            }
        }

        $this->info("Resumo: {$sent} emails enviados, {$skipped} ignorados, {$errors} erros.");
        return self::SUCCESS;
    }
}
