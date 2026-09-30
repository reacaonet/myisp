<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // CEO/CTO so saem de inativo quando o tecnico lanca a fibra e ativa.
        // O que ja foi gerado e nunca teve fibra fica inativo tambem, senao a
        // rede inteira nasce ativa e o tecnico nao sabe o que ja foi lancado.
        $this->deactivateWithoutFiber('ctos', 'cto');
        $this->deactivateWithoutFiber('caixas_emenda', 'caixa');
    }

    public function down(): void
    {
        // Sem volta: nao da para saber quais registros estavam ativos por causa
        // do tecnico e quais eram so placeholder gerado.
    }

    private function deactivateWithoutFiber(string $table, string $type): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        // Antes de mudar status, guarda o estado atual: se a migration rodar em
        // um banco onde ja existe fibra lançada, o no continua como estava.
        $comFibra = collect();

        if (Schema::hasTable('ftth_connections')) {
            // Um no pode receber fibra como destino (entrada) ou estar ligado
            // como origem (saida). Nos dois casos ele foi lancado.
            $comoDestino = DB::table('ftth_connections')
                ->whereNotNull('fiber_link_id')
                ->where('target_type', $type)
                ->pluck('target_id');

            $comoOrigem = DB::table('ftth_connections')
                ->whereNotNull('fiber_link_id')
                ->where('source_type', $type)
                ->pluck('source_id');

            $comFibra = $comoDestino->merge($comoOrigem)->filter()->unique()->values();
        }

        $query = DB::table($table)
            ->where('status', 'active')
            ->whereNull('deleted_at');

        if ($comFibra->isNotEmpty()) {
            $query->whereNotIn('id', $comFibra);
        }

        $query->update(['status' => 'inactive']);
    }
};
