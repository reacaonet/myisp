<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\SystemSetting;
use Modules\Core\Services\TenantContext;

class BillingSetting extends Model
{
    protected $fillable = [
        'company_id',
        'dias_bloqueio',
        'dias_geracao_fatura',
        'bloqueio_automatico',
        'plano_minimo_habilitado',
        'plano_minimo_kbps',
        'plano_minimo_upload_kbps',
    ];

    protected function casts(): array
    {
        return [
            'bloqueio_automatico' => 'boolean',
            'dias_bloqueio' => 'integer',
            'dias_geracao_fatura' => 'integer',
            'plano_minimo_habilitado' => 'boolean',
            'plano_minimo_kbps' => 'integer',
            'plano_minimo_upload_kbps' => 'integer',
        ];
    }

    /**
     * Regras de faturamento da compania do contexto; cai no template da raiz
     * quando a franquia nao configurou as suas.
     */
    public static function get(): static
    {
        $companyId = TenantContext::companyId();

        $settings = $companyId
            ? static::query()->where('company_id', $companyId)->first()
            : null;

        $settings ??= static::query()->whereNull('company_id')->first();

        if (! $settings) {
            $settings = static::create([
                'company_id' => $companyId ?: null,
                'dias_bloqueio' => 10,
                'dias_geracao_fatura' => 5,
                'bloqueio_automatico' => true,
                'plano_minimo_habilitado' => true,
                'plano_minimo_kbps' => 512,
                'plano_minimo_upload_kbps' => 128,
            ]);
        }

        $sys = SystemSetting::getGroup('block');

        if (array_key_exists('block_grace_days', $sys) && $sys['block_grace_days'] !== '' && $sys['block_grace_days'] !== null) {
            $settings->dias_bloqueio = (int) $sys['block_grace_days'];
        }
        if (array_key_exists('plan_min_enabled', $sys)) {
            $settings->plano_minimo_habilitado = $sys['plan_min_enabled'] == '1';
        }
        if (array_key_exists('plan_min_down_kbps', $sys) && $sys['plan_min_down_kbps'] !== '' && $sys['plan_min_down_kbps'] !== null) {
            $settings->plano_minimo_kbps = (int) $sys['plan_min_down_kbps'];
        }
        if (array_key_exists('plan_min_up_kbps', $sys) && $sys['plan_min_up_kbps'] !== '' && $sys['plan_min_up_kbps'] !== null) {
            $settings->plano_minimo_upload_kbps = (int) $sys['plan_min_up_kbps'];
        }

        return $settings;
    }
}
