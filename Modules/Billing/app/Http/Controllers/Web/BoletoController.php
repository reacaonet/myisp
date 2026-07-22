<?php

namespace Modules\Billing\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Models\Invoice;
use Modules\Core\Models\SystemSetting;

class BoletoController extends Controller
{
    public function index(Request $request)
    {
        $invoices = Invoice::with('client', 'contract.plan')
            ->where('status', 'pending')
            ->orderBy('due_date')
            ->paginate(20);

        $bankSettings = [
            'bank' => SystemSetting::get('bank_name', 'Banco do Brasil'),
            'agency' => SystemSetting::get('bank_agency', ''),
            'account' => SystemSetting::get('bank_account', ''),
            'company' => SystemSetting::get('company_name', 'Minha ISP'),
            'cnpj' => SystemSetting::get('company_document', ''),
        ];

        return view('billing::boletos.index', compact('invoices', 'bankSettings'));
    }

    public function print($id)
    {
        $invoice = Invoice::with('client')->findOrFail($id);

        $bankSettings = [
            'bank' => SystemSetting::get('bank_name', 'Banco do Brasil'),
            'agency' => SystemSetting::get('bank_agency', ''),
            'account' => SystemSetting::get('bank_account', ''),
            'company' => SystemSetting::get('company_name', 'Minha ISP'),
            'cnpj' => SystemSetting::get('company_document', ''),
        ];

        return view('billing::boletos.print', compact('invoice', 'bankSettings'));
    }
}
