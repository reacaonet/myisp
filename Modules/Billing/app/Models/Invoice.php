<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\Concerns\BelongsToTenant;
use Modules\Core\Services\TenantContext;
use Modules\CRM\Models\Client;
use Modules\CRM\Models\Contract;

class Invoice extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id', 'branch_id',
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

    public static function bootBelongsToTenant(): void
    {
        static::creating(function ($invoice) {
            $companyId = null;
            $branchId = null;

            if ($invoice->client_id) {
                $client = Client::query()->find($invoice->client_id);
                if ($client) {
                    $companyId = $client->company_id;
                    $branchId = $client->branch_id;
                }
            }

            if (! $invoice->company_id) {
                $invoice->company_id = $companyId ?? TenantContext::companyId();
            }

            if (in_array('branch_id', $invoice->getFillable(), true) && ! $invoice->branch_id) {
                $invoice->branch_id = $branchId ?? TenantContext::branchId();
            }
        });
    }

    public static function nextNumber(int $companyId, ?string $date = null): string
    {
        $year = $date ? date('Y', strtotime($date)) : date('Y');

        $seq = static::query()
            ->where('company_id', $companyId)
            ->whereYear('created_at', $year)
            ->count();

        return 'FAT-'.$year.'-'.str_pad($seq + 1, 4, '0', STR_PAD_LEFT);
    }

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
