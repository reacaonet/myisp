<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 5: reserva os campos de plano/licenca/vencimento em `companies`
     * para a cobranca de franquias. Nao ha cobranca automatica ainda; os
     * campos apenas descrevem o estado da assinatura.
     */
    public function up(): void
    {
        if (! Schema::hasTable('companies')) {
            return;
        }

        Schema::table('companies', function (Blueprint $table) {
            if (! Schema::hasColumn('companies', 'plan_slug')) {
                $table->string('plan_slug')->nullable()->after('is_franchise');
            }

            if (! Schema::hasColumn('companies', 'subscription_status')) {
                $table->string('subscription_status')->default('trial')->after('plan_slug');
            }

            if (! Schema::hasColumn('companies', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')->nullable()->after('subscription_status');
            }

            if (! Schema::hasColumn('companies', 'subscription_ends_at')) {
                $table->timestamp('subscription_ends_at')->nullable()->after('trial_ends_at');
            }

            if (! Schema::hasColumn('companies', 'subscription_notes')) {
                $table->text('subscription_notes')->nullable()->after('subscription_ends_at');
            }
        });

        // a matriz (raiz) nao e uma franquia: fica como ilimitado e sem vencimento
        DB::table('companies')->whereNull('parent_id')->update([
            'subscription_status' => 'unlimited',
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('companies')) {
            return;
        }

        $columns = array_values(array_filter(
            ['subscription_notes', 'subscription_ends_at', 'trial_ends_at', 'subscription_status', 'plan_slug'],
            fn (string $column) => Schema::hasColumn('companies', $column)
        ));

        if ($columns === []) {
            return;
        }

        Schema::table('companies', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }
};
