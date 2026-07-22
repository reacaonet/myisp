<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashBookEntry extends Model
{
    protected $fillable = [
        'type', 'amount', 'description', 'category',
        'entry_date', 'reference', 'payment_method',
        'notes', 'invoice_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'entry_date' => 'date',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function scopeEntradas($query)
    {
        return $query->where('type', 'entrada');
    }

    public function scopeSaidas($query)
    {
        return $query->where('type', 'saida');
    }

    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('entry_date', [$start, $end]);
    }
}
