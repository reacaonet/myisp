<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Confirmado</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6;padding:40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:8px;overflow:hidden;">
                    <tr>
                        <td style="background-color:#16a34a;padding:24px;text-align:center;">
                            <h1 style="color:#ffffff;margin:0;font-size:20px;">MyISP</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <h2 style="color:#1f2937;margin:0 0 16px;font-size:18px;">Pagamento Confirmado</h2>
                            <p style="color:#4b5563;line-height:1.6;margin:0 0 16px;">
                                Olá <strong>{{ $invoice->client->name ?? 'Cliente' }}</strong>,
                            </p>
                            <p style="color:#4b5563;line-height:1.6;margin:0 0 24px;">
                                Seu pagamento foi confirmado com sucesso!
                            </p>
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;margin-bottom:24px;">
                                <tr>
                                    <td style="padding:20px;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding:4px 0;color:#6b7280;font-size:14px;">Numero da Fatura</td>
                                                <td style="padding:4px 0;color:#1f2937;font-size:14px;font-weight:bold;text-align:right;">{{ $invoice->invoice_number }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:4px 0;color:#6b7280;font-size:14px;">Valor Pago</td>
                                                <td style="padding:4px 0;color:#16a34a;font-size:14px;font-weight:bold;text-align:right;">R$ {{ number_format($invoice->total, 2, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:4px 0;color:#6b7280;font-size:14px;">Data do Pagamento</td>
                                                <td style="padding:4px 0;color:#1f2937;font-size:14px;font-weight:bold;text-align:right;">{{ $invoice->paid_date ? $invoice->paid_date->format('d/m/Y') : now()->format('d/m/Y') }}</td>
                                            </tr>
                                            @if($invoice->payment_method)
                                            <tr>
                                                <td style="padding:4px 0;color:#6b7280;font-size:14px;">Forma de Pagamento</td>
                                                <td style="padding:4px 0;color:#1f2937;font-size:14px;font-weight:bold;text-align:right;">{{ strtoupper($invoice->payment_method) }}</td>
                                            </tr>
                                            @endif
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            <p style="color:#4b5563;line-height:1.6;margin:0 0 16px;">
                                Obrigado por manter sua conta em dia!
                            </p>
                            <p style="color:#9ca3af;font-size:12px;margin:0;">
                                Em caso de duvidas, entre em contato conosco.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#f9fafb;padding:16px;text-align:center;">
                            <p style="color:#9ca3af;font-size:12px;margin:0;">MyISP - Gestao de ISP</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
