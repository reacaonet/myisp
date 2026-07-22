<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;

class BillingSetting extends Model
{
    protected $fillable = [
        'dias_bloqueio',
        'dias_geracao_fatura',
        'bloqueio_automatico',
    ];

    protected function casts(): array
    {
        return [
            'bloqueio_automatico' => 'boolean',
            'dias_bloqueio' => 'integer',
            'dias_geracao_fatura' => 'integer',
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
            ]);
        }
        return $settings;
    }
}
