<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fatura Gerada</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6;padding:40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:8px;overflow:hidden;">
                    <tr>
                        <td style="background-color:#2563eb;padding:24px;text-align:center;">
                            <h1 style="color:#ffffff;margin:0;font-size:20px;">MyISP</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <h2 style="color:#1f2937;margin:0 0 16px;font-size:18px;">Fatura Gerada</h2>
                            <p style="color:#4b5563;line-height:1.6;margin:0 0 16px;">
                                Olá <strong>{{ $invoice->client->name ?? 'Cliente' }}</strong>,
                            </p>
                            <p style="color:#4b5563;line-height:1.6;margin:0 0 24px;">
                                Uma nova fatura foi gerada para a sua conta.
                            </p>
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f9fafb;border-radius:8px;margin-bottom:24px;">
                                <tr>
                                    <td style="padding:20px;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding:4px 0;color:#6b7280;font-size:14px;">Numero da Fatura</td>
                                                <td style="padding:4px 0;color:#1f2937;font-size:14px;font-weight:bold;text-align:right;">{{ $invoice->invoice_number }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:4px 0;color:#6b7280;font-size:14px;">Valor</td>
                                                <td style="padding:4px 0;color:#1f2937;font-size:14px;font-weight:bold;text-align:right;">R$ {{ number_format($invoice->total, 2, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:4px 0;color:#6b7280;font-size:14px;">Data de Vencimento</td>
                                                <td style="padding:4px 0;color:#1f2937;font-size:14px;font-weight:bold;text-align:right;">{{ $invoice->due_date->format('d/m/Y') }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            <p style="color:#4b5563;line-height:1.6;margin:0 0 16px;">
                                Para efetuar o pagamento, acesse o portal do cliente e selecione a forma de pagamento desejada (PIX ou Boleto).
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
