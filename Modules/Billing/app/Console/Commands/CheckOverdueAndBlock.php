<?php

namespace Modules\Billing\Console\Commands;

use Illuminate\Console\Command;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\BillingSetting;
use Modules\CRM\Models\Contract;
use Modules\CRM\Models\MikrotikServer;
use Modules\CRM\Services\MikrotikService;

class CheckOverdueAndBlock extends Command
{
    protected $signature = 'billing:check-overdue';
    protected $description = 'Verificar faturas vencidas e bloquear clientes inadimplentes no MikroTik';

    public function handle(): int
    {
        $settings = BillingSetting::get();

        if (!$settings->bloqueio_automatico) {
            $this->info('Bloqueio automatico desativado. Nenhuma acao tomada.');
            return self::SUCCESS;
        }

        $deadline = now()->subDays($settings->dias_bloqueio);

        $overdueInvoices = Invoice::with(['contract.mikrotikServer', 'contract.client'])
            ->where('status', 'pending')
            ->where('due_date', '<', $deadline)
            ->where('auto_blocked', false)
            ->get();

        $blocked = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($overdueInvoices as $invoice) {
            $contract = $invoice->contract;

            if (!$contract || $contract->status !== 'active') {
                $skipped++;
                continue;
            }

            if (!$contract->autobloqueio) {
                $skipped++;
                continue;
            }

            if (!$contract->mikrotikServer && !$contract->server) {
                $skipped++;
                continue;
            }

            try {
                $mikrotikServer = $contract->mikrotikServer ?? $this->resolveMikrotikServer($contract);

                if ($mikrotikServer) {
                    $service = new MikrotikService();
                    $service->connect($mikrotikServer);

                    if ($contract->tipo_conexao === 'pppoe' && $contract->pppoe_user) {
                        $service->disconnectPppoeActive($contract->pppoe_user);
                    } elseif ($contract->tipo_conexao === 'hotspot' && $contract->pppoe_user) {
                        $service->disconnectHotspotActive($contract->pppoe_user);
                    }

                    if ($contract->ip_address) {
                        $service->addFirewallAddressList('myisp-blocked', $contract->ip_address);
                    }

                    $service->disconnect();
                }

                $invoice->update([
                    'status' => 'overdue',
                    'blocked_at' => now(),
                    'auto_blocked' => true,
                    'motivo' => "Bloqueio automatico - fatura vencida em {$invoice->due_date->format('d/m/Y')}",
                ]);

                $contract->update(['status' => 'suspended']);

                $blocked++;
                $this->line("Bloqueado: {$contract->client?->name} (Contrato #{$contract->id})");

            } catch (\Exception $e) {
                $errors++;
                $this->error("Erro ao bloquear contrato #{$contract->id}: {$e->getMessage()}");
            }
        }

        $this->info("Resumo: {$blocked} bloqueados, {$skipped} ignorados, {$errors} erros.");
        return self::SUCCESS;
    }

    private function resolveMikrotikServer(Contract $contract): ?MikrotikServer
    {
        if (!$contract->server) {
            return null;
        }

        return MikrotikServer::where('ip', $contract->server->ip)
            ->where('is_active', true)
            ->first();
    }
}
