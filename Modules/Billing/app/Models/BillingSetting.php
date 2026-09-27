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
        return $settings;
    }
}
