<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrato #{{ $contract->id }} - {{ $contract->client?->name }}</title>
    <style>
        @php $company = $company ?? \Modules\Core\Services\TenantContext::company(); @endphp
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 13px; color: #111; background: #f1f5f9; }
        .page { max-width: 820px; margin: 24px auto; background: #fff; padding: 48px 56px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #111; padding-bottom: 16px; margin-bottom: 24px; }
        .header h1 { font-size: 19px; text-transform: uppercase; letter-spacing: 1px; }
        .header .provider p { font-size: 11px; color: #444; line-height: 1.5; }
        .header .provider p:first-child { font-size: 13px; font-weight: bold; color: #111; }
        .title { text-align: center; font-weight: bold; text-transform: uppercase; font-size: 15px; letter-spacing: 2px; margin-bottom: 28px; }
        .sec { margin-bottom: 22px; }
        .sec h2 { font-size: 12px; text-transform: uppercase; letter-spacing: 1px; border-left: 4px solid #111; padding-left: 8px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
        table td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        table td.k { width: 34%; color: #475569; }
        table td.v { font-weight: 600; }
        .clausulas p { text-align: justify; line-height: 1.6; margin-bottom: 8px; }
        .assinaturas { display: flex; gap: 48px; margin-top: 48px; }
        .assinatura { flex: 1; text-align: center; }
        .assinatura .line { border-top: 1px solid #111; margin-bottom: 6px; }
        .assinatura p { font-size: 11.5px; color: #333; line-height: 1.5; }
        .footer { margin-top: 40px; text-align: center; font-size: 10.5px; color: #64748b; }
        .no-print { text-align: center; margin: 20px 0 40px; }
        .no-print button { padding: 10px 28px; font-size: 14px; cursor: pointer; background: #2563eb; color: #fff; border: none; border-radius: 6px; }
        .no-print a { color: #2563eb; font-size: 13px; margin-left: 16px; }
        @media print {
            body { background: #fff; }
            .page { box-shadow: none; margin: 0; max-width: none; padding: 0; }
            .no-print { display: none; }
            @page { margin: 14mm; }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <div class="provider">
                <p>{{ $company?->legalName() ?? 'MyISP' }}</p>
                @if($company?->fiscal('document'))
                <p>CNPJ: {{ $company->fiscal('document') }}</p>
                @endif
                @if(($company?->fiscal('address')) || ($company?->fiscal('city')))
                <p>{{ $company->fiscal('address') ?? '' }}{{ ($company->fiscal('city')) ? ' - ' . $company->fiscal('city') : '' }}/{{ $company->fiscal('state') ?? '' }}</p>
                @endif
                <p>{{ $company?->fiscal('phone') ?? '' }}{{ $company?->fiscal('email') ?? '' }}</p>
            </div>
            <div style="text-align:right;">
                <p style="font-size:12px;color:#475569;">Contrato n&ordm; {{ str_pad($contract->id, 4, '0', STR_PAD_LEFT) }}</p>
                @if($contract->pedido)
                <p style="font-size:12px;color:#475569;">Pedido: {{ $contract->pedido }}</p>
                @endif
                <p style="font-size:12px;color:#475569;">Emitido em {{ now()->format('d/m/Y') }}</p>
            </div>
        </div>

        <div class="title">Contrato de Prestacao de Servicos de Internet</div>

        <div class="sec">
            <h2>Identificacao das Partes</h2>
            <table>
                <tr><td class="k">Contratada (Provedor)</td><td class="v">{{ $company['company_name'] ?? $company['company_fantasy'] ?? 'MyISP' }}</td></tr>
                <tr><td class="k">CNPJ</td><td class="v">{{ $company['company_document'] ?? '---' }}</td></tr>
                <tr><td class="k">Contratante</td><td class="v">{{ $contract->client?->name }}</td></tr>
                <tr><td class="k">CPF/CNPJ</td><td class="v">{{ $contract->client?->document }}</td></tr>
                <tr><td class="k">Endereco</td><td class="v">{{ $contract->client?->addresses?->first()?->street ?? '' }}{{ $contract->client?->addresses?->first()?->number ? ', ' . $contract->client?->addresses?->first()->number : '' }} - {{ $contract->client?->addresses?->first()?->neighborhood ?? '' }}, {{ $contract->client?->addresses?->first()?->city ?? '' }}/{{ $contract->client?->addresses?->first()?->state ?? '' }}</td></tr>
                <tr><td class="k">Telefone</td><td class="v">{{ $contract->client?->phone }} {{ $contract->client?->cellphone ?? '' }}</td></tr>
            </table>
        </div>

        <div class="sec">
            <h2>Plano Contratado</h2>
            <table>
                <tr><td class="k">Plano</td><td class="v">{{ $contract->plan?->name ?? '-' }}</td></tr>
                <tr><td class="k">Velocidade</td><td class="v">{{ number_format(($contract->plan?->download_speed ?? 0) / 1024, 0) }} Mbps {{ $contract->plan?->upload_speed ? '/ ' . number_format($contract->plan->upload_speed / 1024, 0) . ' Mbps' : '' }}</td></tr>
                <tr><td class="k">Tipo de Conexao</td><td class="v">{{ strtoupper($contract->tipo_conexao ?? 'FTTH') }}</td></tr>
                <tr><td class="k">Valor Mensal</td><td class="v">R$ {{ number_format($contract->plan?->price ?? 0, 2, ',', '.') }}</td></tr>
                @if($contract->discount > 0)
                <tr><td class="k">Desconto</td><td class="v">- R$ {{ number_format($contract->discount, 2, ',', '.') }}</td></tr>
                @endif
                <tr><td class="k">Valor Final</td><td class="v">R$ {{ number_format(($contract->plan?->price ?? 0) - $contract->discount + $contract->acrescimo, 2, ',', '.') }}</td></tr>
                <tr><td class="k">Cobranca</td><td class="v">{{ strtoupper($contract->billing_type ?? 'MENSAL') }} - Vencimento dia {{ $contract->due_day }}</td></tr>
                <tr><td class="k">Data de Ativacao</td><td class="v">{{ $contract->activation_date->format('d/m/Y') }}</td></tr>
                @if($contract->due_date)
                <tr><td class="k">Vigencia</td><td class="v">Até {{ $contract->due_date->format('d/m/Y') }}</td></tr>
                @endif
            </table>
        </div>

        @if($contract->install_street)
        <div class="sec">
            <h2>Endereco de Instalacao</h2>
            <table>
                <tr><td class="k">Logradouro</td><td class="v">{{ $contract->install_street }}{{ $contract->install_number ? ', ' . $contract->install_number : '' }}{{ $contract->install_complement ? ' - ' . $contract->install_complement : '' }}</td></tr>
                <tr><td class="k">Bairro</td><td class="v">{{ $contract->install_neighborhood }}</td></tr>
                <tr><td class="k">Cidade/UF</td><td class="v">{{ $contract->install_city }}/{{ $contract->install_state }} - CEP {{ $contract->install_zipcode }}</td></tr>
            </table>
        </div>
        @endif

        <div class="sec">
            <h2>Clausulas Gerais</h2>
            <div class="clausulas">
                <p>1. O presente contrato regula a prestacao de servicos de acesso a internet em fibra optica, mediante pagamento da mensalidade descrita acima.</p>
                <p>2. A fatura mensal vence no dia <strong>{{ $contract->due_day }}</strong> de cada mes e o pagamento em atraso sujeita o contratante aos encargos previstos em regulamento.</p>
                @if($contract->autobloqueio)
                <p>3. Em caso de inadimplencia, o acesso podera ser suspenso automaticamente, sendo restabelecido apos a regularizacao do debito.</p>
                @else
                <p>3. O bloqueio adaptativo por inadimplencia esta desabilitado para este contrato.</p>
                @endif
                <p>4. A alteracao de plano podera ser solicitada a qualquer momento, respeitados os valores e condicoes vigentes.</p>
                <p>5. Este contrato tem carater informativo e reproduz os dados cadastrados pelo provedor em seu sistema de gestao.</p>
            </div>
        </div>

        <div class="assinaturas">
            <div class="assinatura">
                <div class="line"></div>
                <p>{{ $company?->legalName() ?? 'MyISP' }}<br>Contratada (Provedor)</p>
            </div>
            <div class="assinatura">
                <div class="line"></div>
                <p>{{ $contract->client?->name }}<br>Contratante</p>
            </div>
        </div>

        <div class="footer">
            <p>Documento emitido em {{ now()->format('d/m/Y H:i') }} pelo sistema de gestao.</p>
        </div>
    </div>

    <div class="no-print">
        <button onclick="window.print()">Imprimir Contrato</button>
        <a href="javascript:history.back()">Voltar</a>
    </div>
    <script>window.print();</script>
</body>
</html>