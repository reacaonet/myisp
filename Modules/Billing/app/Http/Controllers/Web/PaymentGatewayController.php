<?php

namespace Modules\Billing\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Models\PaymentGateway;

class PaymentGatewayController extends Controller
{
    public function index()
    {
        $gateways = PaymentGateway::orderBy('name')->get();
        return view('billing::gateways.index', compact('gateways'));
    }

    public function create()
    {
        return view('billing::gateways.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:payment_gateways,slug|in:mercado-pago,asaas,gerencianet',
            'status' => 'required|in:active,inactive',
            'supports_boleto' => 'boolean',
            'supports_pix' => 'boolean',
            'supports_credit_card' => 'boolean',
            'supports_recurrence' => 'boolean',
            'config' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['supports_boleto'] = $request->boolean('supports_boleto');
        $validated['supports_pix'] = $request->boolean('supports_pix');
        $validated['supports_credit_card'] = $request->boolean('supports_credit_card');
        $validated['supports_recurrence'] = $request->boolean('supports_recurrence');

        if (!empty($validated['config'])) {
            $validated['config'] = json_decode($validated['config'], true);
        }

        PaymentGateway::create($validated);

        return redirect()->route('billing.gateways.index')
            ->with('success', 'Gateway criado com sucesso.');
    }

    public function edit($id)
    {
        $gateway = PaymentGateway::findOrFail($id);
        return view('billing::gateways.edit', compact('gateway'));
    }

    public function update(Request $request, $id)
    {
        $gateway = PaymentGateway::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'supports_boleto' => 'boolean',
            'supports_pix' => 'boolean',
            'supports_credit_card' => 'boolean',
            'supports_recurrence' => 'boolean',
            'config' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['supports_boleto'] = $request->boolean('supports_boleto');
        $validated['supports_pix'] = $request->boolean('supports_pix');
        $validated['supports_credit_card'] = $request->boolean('supports_credit_card');
        $validated['supports_recurrence'] = $request->boolean('supports_recurrence');

        if (!empty($validated['config'])) {
            $decoded = json_decode($validated['config'], true);
            if ($decoded !== null) {
                $validated['config'] = $decoded;
            } else {
                $validated->offsetUnset('config');
            }
        }

        $gateway->update($validated);

        return redirect()->route('billing.gateways.index')
            ->with('success', 'Gateway atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $gateway = PaymentGateway::findOrFail($id);

        if ($gateway->invoices()->count() > 0) {
            return back()->with('error', 'Nao e possivel excluir um gateway com faturas vinculadas.');
        }

        $gateway->delete();

        return redirect()->route('billing.gateways.index')
            ->with('success', 'Gateway removido com sucesso.');
    }

    public function test($id)
    {
        $gateway = PaymentGateway::findOrFail($id);

        $testResults = [
            'name' => $gateway->name,
            'slug' => $gateway->slug,
            'status' => $gateway->status,
            'config' => !empty($gateway->config) ? array_keys($gateway->config) : [],
        ];

        if ($gateway->config) {
            $hasRequired = match ($gateway->slug) {
                'mercado-pago' => isset($gateway->config['access_token']),
                'asaas' => isset($gateway->config['api_key']),
                'gerencianet' => isset($gateway->config['client_id']) && isset($gateway->config['client_secret']),
                default => false,
            };
            $testResults['config_valid'] = $hasRequired;
        } else {
            $testResults['config_valid'] = false;
        }

        return response()->json($testResults);
    }
}
