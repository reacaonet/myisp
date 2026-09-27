<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;

class BillingSetting extends Model
{
    protected $fillable = [
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

    public static function get(): static
    {
        $settings = static::first();
        if (!$settings) {
            $settings = static::create([
                'dias_bloqueio' => 10,
                'dias_geracao_fatura' => 5,
                'bloqueio_automatico' => true,
                'plano_minimo_habilitado' => true,
                'plano_minimo_kbps' => 512,
                'plano_minimo_upload_kbps' => 128,
            ]);
        }

        $sys = \Modules\Core\Models\SystemSetting::getGroup('block');

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
