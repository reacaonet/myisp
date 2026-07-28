<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = ['item_id', 'location_id', 'type', 'quantity', 'reference', 'notes', 'performed_by'];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(StockItem::class, 'item_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(StockLocation::class, 'location_id');
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'performed_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'entry' => 'Entrada',
            'exit' => 'Saída',
            'return' => 'Devolução',
            'transfer' => 'Transferência',
            'installation' => 'Instalação',
            default => $this->type,
        };
    }
}
