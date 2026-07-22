<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'manufacturer_id', 'name', 'model', 'serial_number', 'barcode',
        'mac_address', 'ip_address', 'type', 'invoice_number', 'cost',
        'sale_price', 'purchase_date', 'warranty_until', 'supplier',
        'quantity', 'available_quantity', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'purchase_date' => 'date',
            'warranty_until' => 'date',
            'quantity' => 'integer',
            'available_quantity' => 'integer',
        ];
    }

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function assignments()
    {
        return $this->hasMany(EquipmentAssignment::class);
    }

    public function activeAssignments()
    {
        return $this->assignments()->where('status', 'active');
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'onu' => 'ONU',
            'router' => 'Roteador',
            'switch' => 'Switch',
            'access_point' => 'Access Point',
            'antenna' => 'Antena',
            'cable' => 'Cabo',
            default => 'Outro',
        };
    }
}
