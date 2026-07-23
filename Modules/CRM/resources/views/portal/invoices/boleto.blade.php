<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boleto - {{ $invoice->invoice_number }} - MyISP</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; padding: 20px; font-size: 12px; color: #333; background: #f3f4f6; }
        .container { max-width: 700px; margin: 0 auto; }
        .toolbar { text-align: center; margin-bottom: 20px; }
        .toolbar button { padding: 10px 24px; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500; }
        .toolbar button:hover { background: #1d4ed8; }
        .toolbar a { display: inline-block; margin-left: 12px; padding: 10px 24px; background: white; color: #374151; border: 1px solid #d1d5db; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500; }
        .toolbar a:hover { background: #f9fafb; }
        .boleto { border: 2px solid #000; padding: 20px; background: white; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px; }
        .header h2 { font-size: 18px; }
        .header .badge { background: #dbeafe; color: #1e40af; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: bold; }
        .sandbox-badge { background: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: bold; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px; }
        .info-grid div { padding: 5px; }
        .info-grid .label { font-weight: bold; font-size: 10px; text-transform: uppercase; color: #666; }
        .info-grid .value { font-size: 14px; }
        .valor-box { border: 2px solid #000; text-align: center; padding: 10px; margin: 15px 0; }
        .valor-box .label { font-size: 10px; text-transform: uppercase; }
        .valor-box .amount { font-size: 28px; font-weight: bold; }
        .barcode-line { border: 1px solid #000; padding: 10px; text-align: center; margin: 15px 0; font-family: monospace; font-size: 14px; letter-spacing: 2px; }
        .digitable-line { border: 1px solid #ccc; padding: 8px; text-align: center; margin: 10px 0; font-family: monospace; font-size: 11px; letter-spacing: 1px; background: #fafafa; word-break: break-all; }
        .footer { border-top: 2px solid #000; padding-top: 10px; font-size: 10px; color: #666; }
        .pix-section { border: 2px dashed #22c55e; padding: 15px; text-align: center; margin-bottom: 20px; background: white; }
        .pix-section img { max-width: 200px; margin: 10px auto; }
        .pix-copy { font-family: monospace; font-size: 10px; word-break: break-all; background: #f3f4f6; padding: 8px; margin-top: 8px; border-radius: 4px; }
        .mp-link { display: inline-block; margin: 10px 0; padding: 8px 16px; background: #009ee3; color: #fff; text-decoration: none; border-radius: 4px; font-size: 12px; }
        .mp-link:hover { background: #0086cc; }
        .copy-btn { display: inline-block; margin-top: 8px; padding: 6px 12px; background: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 11px; }
        .copy-btn:hover { background: #1d4ed8; }
        @media print { body { padding: 0; background: white; } .toolbar { display: none; } .pix-section { page-break-inside: avoid; } .boleto { page-break-inside: avoid; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="toolbar">
            <button onclick="window.print()">Imprimir Boleto</button>
            @if($invoice->gateway_payment_url)
            <a href="{{ $invoice->gateway_payment_url }}" target="_blank">Pagar Online</a>
            @endif
            @if(in_array($invoice->status, ['pending', 'overdue']))
            <a href="{{ route('crm.portal.invoices.pay', $invoice) }}" style="background: #16a34a;">Gerar Novo</a>
            @endif
            <a href="{{ route('crm.portal.invoices.show', $invoice) }}">Voltar a Fatura</a>
        </div>

        {{-- PIX Section --}}
        @if($invoice->pix_copy_paste && !empty($invoice->gateway_qr_code))
        <div class="pix-section">
            <p style="font-size: 16px; font-weight: bold; color: #16a34a; margin-bottom: 10px;">Pague via PIX</p>
            @if($invoice->gateway && $invoice->gateway->slug === 'mercado-pago')
                <span class="sandbox-badge">SANDBOX - Somente para testes</span>
                <p style="font-size: 11px; color: #92400e; margin: 5px 0;">Este QR Code PIX so funciona no ambiente sandbox do Mercado Pago.</p>
            @endif
            <img src="data:image/png;base64,{{ $invoice->gateway_qr_code }}" alt="QR Code PIX">
            <div class="pix-copy">
                <strong>PIX Copia e Cola:</strong><br>
                {{ $invoice->pix_copy_paste }}
            </div>
            <button class="copy-btn" onclick="navigator.clipboard.writeText('{{ $invoice->pix_copy_paste }}').then(() => this.textContent = 'Copiado!')">Copiar Codigo PIX</button>
        </div>
        @endif

        {{-- Boleto Section --}}
        <div class="boleto">
            <div class="header">
                @if($mpAccount)
                    <h2>{{ $mpAccount['name'] }}</h2>
                @else
                    <h2>{{ $bankSettings['company'] ?? 'MyISP' }}</h2>
                @endif
                <div>
                    @if($invoice->gateway)
                        <span class="badge">{{ $invoice->gateway->name }}</span>
                    @endif
                    @if($invoice->gateway && $invoice->gateway->slug === 'mercado-pago')
                        <span class="sandbox-badge">SANDBOX</span>
                    @endif
                </div>
            </div>

            <div class="info-grid">
                <div>
                    <div class="label">Beneficiario (Cedente)</div>
                    <div class="value">{{ $mpAccount['name'] ?? $bankSettings['company'] ?? 'MyISP' }}</div>
                </div>
                <div>
                    <div class="label">{{ $mpAccount['document_type'] ?? 'CNPJ' }}</div>
                    <div class="value">{{ $mpAccount['document_number'] ?? $bankSettings['cnpj'] ?? '00.000.000/0001-00' }}</div>
                </div>
                @if($mpAccount)
                <div>
                    <div class="label">Email</div>
                    <div class="value">{{ $mpAccount['email'] }}</div>
                </div>
                <div>
                    <div class="label">Endereco</div>
                    <div class="value" style="font-size:11px;">{{ $mpAccount['address'] }}</div>
                </div>
                @endif
                <div>
                    <div class="label">Sacado (Pagador)</div>
                    <div class="value">{{ $invoice->client->name ?? 'N/A' }}</div>
                </div>
                <div>
                    <div class="label">CPF/CNPJ</div>
                    <div class="value">{{ $invoice->client->document ?? 'N/A' }}</div>
                </div>
                <div>
                    <div class="label">Data de Vencimento</div>
                    <div class="value" style="font-size:16px; font-weight:bold;">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</div>
                </div>
                <div>
                    <div class="label">Numero do Documento</div>
                    <div class="value">{{ $invoice->invoice_number }}</div>
                </div>
                @if($invoice->boleto_numero)
                <div>
                    <div class="label">ID Pagamento (Gateway)</div>
                    <div class="value">{{ $invoice->boleto_numero }}</div>
                </div>
                @endif
            </div>

            <div class="valor-box">
                <div class="label">Valor do documento</div>
                <div class="amount">R$ {{ number_format($invoice->total, 2, ',', '.') }}</div>
            </div>

            @if($invoice->barcode)
            <div class="barcode-line">
                {{ $invoice->barcode }}
            </div>
            @endif

            @if($invoice->digitable_line)
            <div class="digitable-line">
                <strong>Linha Digitavel:</strong><br>
                {{ $invoice->digitable_line }}
            </div>
            @endif

            @if($invoice->gateway_payment_url)
            <div style="text-align: center; margin: 15px 0;">
                <a href="{{ $invoice->gateway_payment_url }}" target="_blank" class="mp-link">
                    Acessar Boleto no Mercado Pago
                </a>
            </div>
            @endif

            <div class="footer">
                <p><strong>Observacoes:</strong></p>
                <p>Sr. Caixa, este boleto nao pode ser recolhido apos o vencimento.</p>
                <p>Apos o vencimento cobrar multa de 2% e juros de 1% ao mes.</p>
                @if($invoice->boleto_numero)
                    <p>Codigo do pagamento no gateway: {{ $invoice->boleto_numero }}</p>
                @endif
                <p style="margin-top: 10px;">Documento gerado em {{ now()->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>
</body>
</html>
