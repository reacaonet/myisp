<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentAssignment extends Model
{
    protected $fillable = [
        'equipment_id', 'client_id', 'contract_id', 'quantity',
        'serial_number_used', 'mac_address_used', 'assigned_at',
        'returned_at', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'date',
            'returned_at' => 'date',
            'quantity' => 'integer',
        ];
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
