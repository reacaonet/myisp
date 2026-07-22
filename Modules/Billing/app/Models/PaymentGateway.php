<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $fillable = [
        'name', 'slug', 'status', 'supports_boleto', 'supports_pix',
        'supports_credit_card', 'supports_recurrence', 'config', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'supports_boleto' => 'boolean',
            'supports_pix' => 'boolean',
            'supports_credit_card' => 'boolean',
            'supports_recurrence' => 'boolean',
            'config' => 'array',
        ];
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'gateway_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getConfigValue(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }
}
