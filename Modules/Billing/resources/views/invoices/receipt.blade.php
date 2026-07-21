<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo - {{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; font-size: 12px; color: #000; padding: 20px; }
        .receipt { max-width: 320px; margin: 0 auto; }
        .header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 12px; margin-bottom: 12px; }
        .header h1 { font-size: 18px; margin-bottom: 4px; }
        .header p { font-size: 10px; }
        .title { text-align: center; font-size: 14px; font-weight: bold; margin: 12px 0; text-transform: uppercase; letter-spacing: 2px; }
        .info { margin-bottom: 12px; }
        .info p { line-height: 1.6; }
        .info .label { display: inline-block; width: 90px; }
        .line { border-top: 1px dashed #000; margin: 8px 0; }
        .total-row { display: flex; justify-content: space-between; font-weight: bold; font-size: 14px; margin: 4px 0; }
        .payment-details { margin: 8px 0; }
        .payment-details p { line-height: 1.6; }
        .footer { text-align: center; margin-top: 16px; font-size: 10px; border-top: 1px dashed #000; padding-top: 8px; }
        .footer p { line-height: 1.4; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
            @page { margin: 10mm; }
        }
        .no-print { text-align: center; margin-top: 20px; }
        .no-print button { padding: 8px 24px; font-size: 14px; cursor: pointer; background: #2563eb; color: #fff; border: none; border-radius: 6px; }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h1>MyISP</h1>
            <p>Provedor de Internet</p>
            <p>CNPJ: 00.000.000/0001-00</p>
            <p>Rua Exemplo, 123 - Centro</p>
            <p>Sao Paulo - SP</p>
        </div>

        <div class="title">Recibo de Pagamento</div>

        <div class="info">
            <p><span class="label">Cliente:</span> {{ $invoice->client->name }}</p>
            <p><span class="label">Documento:</span> {{ $invoice->client->document }}</p>
            <p><span class="label">Endereco:</span> {{ $invoice->client->addresses->first()?->street ?? '' }}, {{ $invoice->client->addresses->first()?->number ?? '' }}</p>
            <p><span class="label">Bairro:</span> {{ $invoice->client->addresses->first()?->neighborhood ?? '' }}</p>
            <p><span class="label">Cidade:</span> {{ $invoice->client->addresses->first()?->city ?? '' }}/{{ $invoice->client->addresses->first()?->state ?? '' }}</p>
        </div>

        <div class="line"></div>

        <div class="info">
            <p><span class="label">Fatura:</span> {{ $invoice->invoice_number }}</p>
            <p><span class="label">Vencimento:</span> {{ $invoice->due_date->format('d/m/Y') }}</p>
            <p><span class="label">Pago em:</span> {{ $invoice->paid_date?->format('d/m/Y') }}</p>
            <p><span class="label">Valor:</span> R$ {{ number_format($invoice->amount, 2, ',', '.') }}</p>
            @if($invoice->discount > 0)
            <p><span class="label">Desconto:</span> -R$ {{ number_format($invoice->discount, 2, ',', '.') }}</p>
            @endif
        </div>

        <div class="line"></div>

        <div class="total-row">
            <span>Total Pago</span>
            <span>R$ {{ number_format($invoice->total, 2, ',', '.') }}</span>
        </div>

        <div class="line"></div>

        <div class="payment-details">
            <p><strong>Pagamento(s):</strong></p>
            @foreach($invoice->payments as $payment)
            <p>{{ $payment->payment_date->format('d/m/Y') }} - {{ strtoupper($payment->payment_method) }} - R$ {{ number_format($payment->amount, 2, ',', '.') }}{{ $payment->transaction_id ? ' ('.$payment->transaction_id.')' : '' }}</p>
            @endforeach
        </div>

        <div class="line"></div>

        <div style="text-align: center; margin-top: 8px;">
            <p>Recebemos a importancia acima para</p>
            <p>total quitação desta fatura.</p>
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <p>____________________________________</p>
            <p>MyISP - Provedor de Internet</p>
        </div>

        <div class="footer">
            <p>Emitido em {{ now()->format('d/m/Y H:i') }}</p>
            <p>Este documento e comprovante de pagamento.</p>
        </div>
    </div>

    <div class="no-print">
        <button onclick="window.print()">Imprimir Recibo</button>
        <br><br>
        <a href="{{ route('billing.invoices.show', $invoice) }}" style="color: #2563eb; font-size: 13px;">Voltar para fatura</a>
    </div>
</body>
</html>
