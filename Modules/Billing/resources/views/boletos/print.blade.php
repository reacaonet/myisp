<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Boleto - {{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; padding: 20px; font-size: 12px; color: #333; }
        .boleto { border: 2px solid #000; padding: 20px; max-width: 700px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px; }
        .header h2 { font-size: 18px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px; }
        .info-grid div { padding: 5px; }
        .info-grid .label { font-weight: bold; font-size: 10px; text-transform: uppercase; color: #666; }
        .info-grid .value { font-size: 14px; }
        .valor-box { border: 2px solid #000; text-align: center; padding: 10px; margin: 15px 0; }
        .valor-box .label { font-size: 10px; text-transform: uppercase; }
        .valor-box .amount { font-size: 28px; font-weight: bold; }
        .barcode { border: 1px solid #000; padding: 10px; text-align: center; margin: 15px 0; font-family: monospace; font-size: 14px; letter-spacing: 2px; }
        .footer { border-top: 2px solid #000; padding-top: 10px; font-size: 10px; color: #666; }
        .gateway-badge { background: #dbeafe; color: #1e40af; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: bold; }
        .pix-box { border: 2px dashed #22c55e; padding: 15px; text-align: center; margin: 15px 0; }
        .pix-box img { max-width: 200px; }
        .pix-box .copy-paste { font-family: monospace; font-size: 10px; word-break: break-all; background: #f3f4f6; padding: 8px; margin-top: 8px; border-radius: 4px; }
        @media print { body { padding: 0; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px;">Imprimir Boleto</button>
        @if($invoice->link_boleto)
            <a href="{{ $invoice->link_boleto }}" target="_blank" style="padding: 10px 20px; background: #059669; color: white; border: none; border-radius: 5px; text-decoration: none; font-size: 14px; margin-left: 10px;">Abrir no Gateway</a>
        @endif
    </div>

    @if($invoice->gateway && $invoice->pix_copy_paste && !empty($invoice->gateway_qr_code))
    <div style="max-width: 700px; margin: 0 auto 20px;">
        <div class="pix-box">
            <p style="font-size: 14px; font-weight: bold; color: #16a34a; margin-bottom: 10px;">Pague via PIX</p>
            <img src="data:image/png;base64,{{ $invoice->gateway_qr_code }}" alt="QR Code PIX">
            <div class="copy-paste">
                <strong>Chave PIX Copia e Cola:</strong><br>
                {{ $invoice->pix_copy_paste }}
            </div>
        </div>
    </div>
    @endif

    <div class="boleto">
        <div class="header">
            <h2>{{ $bankSettings['company'] ?? 'MyISP' }}</h2>
            <div>
                @if($invoice->gateway)
                    <span class="gateway-badge">{{ $invoice->gateway->name }}</span>
                @endif
                <strong>Banco:</strong> {{ $bankSettings['bank'] ?? '001' }}
            </div>
        </div>
        <div class="info-grid">
            <div><div class="label">Cedente</div><div class="value">{{ $bankSettings['company'] ?? 'MyISP' }}</div></div>
            <div><div class="label">CNPJ</div><div class="value">{{ $bankSettings['cnpj'] ?? '00.000.000/0001-00' }}</div></div>
            <div><div class="label">Sacado</div><div class="value">{{ $invoice->client->name ?? 'N/A' }}</div></div>
            <div><div class="label">CPF/CNPJ</div><div class="value">{{ $invoice->client->document ?? 'N/A' }}</div></div>
            <div><div class="label">Data de Vencimento</div><div class="value" style="font-size:16px; font-weight:bold;">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</div></div>
            <div><div class="label">Numero do Documento</div><div class="value">{{ $invoice->invoice_number }}</div></div>
            <div><div class="label">Nosso Numero</div><div class="value">{{ $invoice->boleto_numero ?? str_pad($invoice->id, 10, '0', STR_PAD_LEFT) }}</div></div>
            <div><div class="label">Agencia / Conta</div><div class="value">{{ $bankSettings['agency'] ?? '0000' }} / {{ $bankSettings['account'] ?? '000000' }}</div></div>
        </div>
        <div class="valor-box">
            <div class="label">Valor do documento</div>
            <div class="amount">R$ {{ number_format($invoice->total, 2, ',', '.') }}</div>
        </div>
        <div class="barcode">| {{ str_pad($invoice->id, 4, '0') }}.{{ str_pad($invoice->total * 100, 10, '0') }}.{{ \Carbon\Carbon::parse($invoice->due_date)->format('dmy') }} |</div>
        <div class="footer">
            <p><strong>Observacoes:</strong></p>
            <p>Sr. Caixa, este boleto nao pode ser recolhido apos o vencimento.</p>
            <p>Apos o vencimento cobrar multa de 2% e juros de 1% ao mes.</p>
            @if($invoice->boleto_numero)
                <p>Codigo do boleto no gateway: {{ $invoice->boleto_numero }}</p>
            @endif
            <p style="margin-top: 10px;">Documento gerado em {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>
</body>
</html>
