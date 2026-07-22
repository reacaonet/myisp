<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\CRM\Models\Client;
use Modules\CRM\Models\Contract;

class Invoice extends Model
{
    protected $fillable = [
        'client_id', 'contract_id', 'invoice_number',
        'amount', 'discount', 'acrescimo', 'total',
        'due_date', 'dia', 'mes', 'ano',
        'paid_date', 'blocked_at', 'auto_blocked',
        'status', 'payment_method',
        'transaction_id', 'link_boleto', 'chave_boleto', 'boleto_numero',
        'notes', 'motivo', 'mes_parcela', 'avulso', 'ref_os',
        'gateway_id', 'gateway_status', 'gateway_payment_url', 'gateway_qr_code', 'pix_copy_paste',
        'barcode', 'digitable_line',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'paid_date' => 'date',
            'blocked_at' => 'datetime',
            'avulso' => 'boolean',
            'auto_blocked' => 'boolean',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function gateway()
    {
        return $this->belongsTo(PaymentGateway::class, 'gateway_id');
    }
}
