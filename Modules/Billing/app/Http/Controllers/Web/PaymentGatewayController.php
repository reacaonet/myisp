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

        if (empty($gateway->config)) {
            return response()->json(['success' => false, 'message' => 'Configuracao vazia. Salve o gateway primeiro.']);
        }

        try {
            $result = match ($gateway->slug) {
                'mercado-pago' => $this->testMercadoPago($gateway),
                'asaas' => $this->testAsaas($gateway),
                'gerencianet' => $this->testGerencianet($gateway),
                default => ['success' => false, 'message' => 'Gateway desconhecido.'],
            };
        } catch (\Exception $e) {
            $result = ['success' => false, 'message' => $e->getMessage()];
        }

        return response()->json($result);
    }

    private function testMercadoPago(PaymentGateway $gateway): array
    {
        $token = $gateway->config['access_token'] ?? '';
        if (!$token) {
            return ['success' => false, 'message' => 'access_token nao configurado.'];
        }

        $url = 'https://api.mercadopago.com/v1/payment_methods';
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => $gateway->config['ssl_verify'] ?? false,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'message' => "Erro cURL: {$error}"];
        }
        if ($httpCode === 401) {
            return ['success' => false, 'message' => 'Token invalido (401).'];
        }
        if ($httpCode >= 400) {
            $body = json_decode($response, true);
            return ['success' => false, 'message' => "Erro HTTP {$httpCode}: " . ($body['message'] ?? substr($response, 0, 200))];
        }

        return ['success' => true, 'message' => "Conexao OK (HTTP {$httpCode}). Token valido."];
    }

    private function testAsaas(PaymentGateway $gateway): array
    {
        $apiKey = $gateway->config['api_key'] ?? '';
        if (!$apiKey) {
            return ['success' => false, 'message' => 'api_key nao configurado.'];
        }

        $baseUrl = ($gateway->config['sandbox'] ?? true)
            ? 'https://sandbox.asaas.com/api/v3/customers'
            : 'https://api.asaas.com/v3/customers';

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $baseUrl . '?limit=1',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => $gateway->config['ssl_verify'] ?? false,
            CURLOPT_HTTPHEADER => ['access_token: ' . $apiKey],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'message' => "Erro cURL: {$error}"];
        }
        if ($httpCode === 401) {
            return ['success' => false, 'message' => 'API key invalida (401).'];
        }
        if ($httpCode >= 400) {
            return ['success' => false, 'message' => "Erro HTTP {$httpCode}."];
        }

        return ['success' => true, 'message' => "Conexao OK (HTTP {$httpCode}). API key valida."];
    }

    private function testGerencianet(PaymentGateway $gateway): array
    {
        $clientId = $gateway->config['client_id'] ?? '';
        $clientSecret = $gateway->config['client_secret'] ?? '';
        if (!$clientId || !$clientSecret) {
            return ['success' => false, 'message' => 'client_id ou client_secret nao configurados.'];
        }

        $baseUrl = ($gateway->config['sandbox'] ?? true)
            ? 'https://sandbox.gerencianet.com.br'
            : 'https://api.gerencianet.com.br';

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $baseUrl . '/oauth/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => $gateway->config['ssl_verify'] ?? false,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'grant_type' => 'client_credentials',
            ]),
            CURLOPT_USERPWD => $clientId . ':' . $clientSecret,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'message' => "Erro cURL: {$error}"];
        }
        if ($httpCode === 401) {
            return ['success' => false, 'message' => 'Credenciais invalidas (401).'];
        }
        if ($httpCode >= 400) {
            return ['success' => false, 'message' => "Erro HTTP {$httpCode}."];
        }

        $body = json_decode($response, true);
        if (!empty($body['access_token'])) {
            return ['success' => true, 'message' => "Conexao OK. OAuth token obtido com sucesso."];
        }

        return ['success' => false, 'message' => 'Resposta inesperada da API.'];
    }
}
