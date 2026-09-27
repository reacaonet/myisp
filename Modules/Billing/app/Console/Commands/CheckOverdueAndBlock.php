<?php

namespace Modules\Billing\Console\Commands;

use Illuminate\Console\Command;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\BillingSetting;
use Modules\CRM\Models\Contract;
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
        $noticeEnabled = \Modules\Core\Models\SystemSetting::get('notice_page_enabled') == '1';

        $blocked = 0;
        $skipped = 0;
        $errors = 0;
        $notice = 0;
        $blockedContractIds = [];

        // FASE 1: bloqueio (plano reduzido ou bloqueio total) apos o prazo de tolerancia
        $overdueInvoices = Invoice::with(['contract.client'])
            ->where('status', 'pending')
            ->where('due_date', '<', $deadline)
            ->where('auto_blocked', false)
            ->get();

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

            $mikrotikServer = $contract->provisionedMikrotikServer();

            if (!$mikrotikServer) {
                $skipped++;
                continue;
            }

            try {
                $service = new MikrotikService();
                $service->connect($mikrotikServer);

                $login = $contract->provisionedLogin();
                $blockedIp = $contract->provisionedIp();

                if ($settings->plano_minimo_habilitado && $login) {
                    $service->applyMinimumPlan(
                        $contract->tipo_conexao === 'hotspot' ? 'hotspot' : 'pppoe',
                        $login,
                        (int) ($settings->plano_minimo_kbps ?: 512),
                        (int) ($settings->plano_minimo_upload_kbps ?: 128)
                    );
                } else {
                    if ($login) {
                        if ($contract->tipo_conexao === 'pppoe') {
                            $service->disconnectPppoeActive($login);
                        } elseif ($contract->tipo_conexao === 'hotspot') {
                            $service->disconnectHotspotActive($login);
                        }
                    }

                    if ($blockedIp) {
                        $service->addFirewallAddressList('myisp-blocked', $blockedIp);
                    }
                }

                if ($blockedIp) {
                    $service->removeFirewallAddressList('myisp-vencida', $blockedIp);
                }

                $service->disconnect();

                $invoice->update([
                    'status' => 'overdue',
                    'blocked_at' => now(),
                    'auto_blocked' => true,
                    'motivo' => $settings->plano_minimo_habilitado
                        ? "Bloqueio automatico - fatura vencida em {$invoice->due_date->format('d/m/Y')}. Liberado plano minimo de navegacao."
                        : "Bloqueio automatico - fatura vencida em {$invoice->due_date->format('d/m/Y')}",
                ]);

                $contract->update(['status' => 'suspended']);

                $blockedContractIds[] = $contract->id;
                $blocked++;
                $this->line("Bloqueado: {$contract->client?->name} (Contrato #{$contract->id})");

            } catch (\Exception $e) {
                $errors++;
                $this->error("Erro ao bloquear contrato #{$contract->id}: {$e->getMessage()}");
            }
        }

        // FASE 2: periodo de tolerancia - lista myisp-vencida para o aviso (HTTP)
        if ($noticeEnabled) {
            $graceInvoices = Invoice::with('contract')
                ->where('status', 'pending')
                ->where('due_date', '>=', $deadline)
                ->where('due_date', '<', now())
                ->where('auto_blocked', false)
                ->when($blockedContractIds, fn ($q) => $q->whereNotIn('contract_id', $blockedContractIds))
                ->get();

            $handled = [];

            foreach ($graceInvoices as $invoice) {
                $contract = $invoice->contract;

                if (!$contract || !$contract->autobloqueio) {
                    continue;
                }

                if ($contract->status !== 'active' || in_array($contract->id, $handled)) {
                    continue;
                }

                $handled[] = $contract->id;

                $mikrotikServer = $contract->provisionedMikrotikServer();

                if (!$mikrotikServer) {
                    continue;
                }

                $ip = $contract->provisionedIp();

                if (!$ip) {
                    continue;
                }

                try {
                    $service = new MikrotikService();
                    $service->connect($mikrotikServer);
                    $service->addFirewallAddressList('myisp-vencida', $ip);
                    $service->disconnect();
                    $notice++;
                } catch (\Exception $e) {
                    $errors++;
                    $this->error("Erro ao adicionar aviso para contrato #{$contract->id}: {$e->getMessage()}");
                }
            }
        }

        $this->info("Resumo: {$blocked} bloqueados, {$notice} em aviso, {$skipped} ignorados, {$errors} erros.");
        return self::SUCCESS;
    }
}
