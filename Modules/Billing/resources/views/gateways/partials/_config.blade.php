@php
    $isEdit = isset($gateway) && $gateway instanceof \Modules\Billing\Models\PaymentGateway;
    $slug = $isEdit ? $gateway->slug : (old('slug') ?? '');
    $cfg = $isEdit ? ($gateway->config ?? []) : (old('config') ?? []);
    if (is_string($cfg)) {
        $cfg = json_decode($cfg, true) ?: [];
    }
    $cfg = is_array($cfg) ? $cfg : [];
    $mpToken = old('config.access_token', $cfg['access_token'] ?? '');
    $mpKey = old('config.public_key', $cfg['public_key'] ?? '');
    $mpWebhook = old('config.webhook_url', $cfg['webhook_url'] ?? '');
    $mpSandbox = old('config.sandbox', !empty($cfg['sandbox']) || empty($cfg['sandbox']) && !$isEdit);
    $jsonRaw = is_string(old('config_raw', null)) ? old('config_raw') : json_encode($cfg, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
@endphp

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Configuracao</label>

    <div class="space-y-3">
        <div id="config-mp-fields" style="display: none;">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">MERCADO_PAGO_ACCESS_TOKEN *</label>
                <input type="text" name="config[access_token]"
                       value="{{ $mpToken }}"
                       placeholder="APP_USR-0000000000000000-000000-0000000000000000"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">MERCADO_PAGO_PUBLIC_KEY</label>
                <input type="text" name="config[public_key]"
                       value="{{ $mpKey }}"
                       placeholder="APP_USR-00000000-0000-0000-0000-000000000000"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Webhook URL (notification_url)</label>
                <input type="text" name="config[webhook_url]"
                       value="{{ $mpWebhook }}"
                       placeholder="https://seudominio.com.br/webhooks/mercadopago"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono">
                <p class="text-xs text-gray-400 mt-1">Deixe vazio para nao enviar. Use uma URL publica via HTTPS, nunca localhost.</p>
            </div>

            <div class="flex gap-6 pt-1">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="config[sandbox]" value="1" {{ (bool) $mpSandbox ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600">
                    <span class="text-sm">Sandbox (teste)</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="config[ssl_verify]" value="1" {{ !empty($cfg['ssl_verify']) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600">
                    <span class="text-sm">Verificar SSL</span>
                </label>
            </div>
        </div>

        <div id="config-json-field">
            <textarea name="config" rows="6" placeholder='{
    "api_key": "x",
    "sandbox": true
}' class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono">{{ $jsonRaw }}</textarea>
        </div>
    </div>

    <p class="text-xs text-gray-400 mt-1">
        <strong>Mercado Pago:</strong> access_token, public_key, webhook_url, sandbox, ssl_verify<br>
        <strong>Asaas:</strong> api_key, sandbox, ssl_verify<br>
        <strong>Gerencianet:</strong> client_id, client_secret, sandbox, ssl_verify<br>
        <strong>ssl_verify:</strong> desmarcado para ambientes sem certificado SSL (ex: WAMP local)
    </p>
</div>

<script>
function toggleGatewayConfig(slug) {
    const mpFields = document.getElementById('config-mp-fields');
    const jsonField = document.getElementById('config-json-field');
    if (slug === 'mercado-pago') {
        mpFields.style.display = '';
        jsonField.style.display = 'none';
        if (jsonField.querySelector('textarea')) jsonField.querySelector('textarea').disabled = true;
    } else {
        mpFields.style.display = 'none';
        jsonField.style.display = '';
        if (jsonField.querySelector('textarea')) jsonField.querySelector('textarea').disabled = false;
    }
}
document.addEventListener('DOMContentLoaded', function () {
    const slugEl = document.getElementById('slug-input');
    if (slugEl) {
        toggleGatewayConfig(slugEl.value);
        slugEl.addEventListener('change', function () { toggleGatewayConfig(this.value); });
    }
});
</script>