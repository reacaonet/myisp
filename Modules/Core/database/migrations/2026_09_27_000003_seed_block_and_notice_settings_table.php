<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Models\SystemSetting;

return new class extends Migration
{
    public function up(): void
    {
        $blockDefaults = [
            'block_grace_days' => ['10', 'number'],
            'plan_min_enabled' => ['1', 'boolean'],
            'plan_min_down_kbps' => ['512', 'number'],
            'plan_min_up_kbps' => ['128', 'number'],
        ];

        foreach ($blockDefaults as $key => [$value, $type]) {
            SystemSetting::firstOrCreate(
                ['key' => $key],
                ['value' => $value, 'type' => $type, 'group' => 'block']
            );
        }

        $noticeDefaults = [
            'notice_page_enabled' => ['1', 'boolean'],
            'notice_page_url' => ['', 'text'],
            'notice_mark_text' => ['MK', 'text'],
            'notice_page_html' => [$this->noticeHtml(), 'textarea'],
        ];

        foreach ($noticeDefaults as $key => [$value, $type]) {
            SystemSetting::firstOrCreate(
                ['key' => $key],
                ['value' => $value, 'type' => $type, 'group' => 'aviso']
            );
        }
    }

    public function down(): void
    {
        SystemSetting::where('group', 'aviso')->delete();
        SystemSetting::whereIn('key', [
            'block_grace_days',
            'plan_min_enabled',
            'plan_min_down_kbps',
            'plan_min_up_kbps',
        ])->delete();
    }

    private function noticeHtml(): string
    {
        return <<<'HTML'
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="color-scheme" content="light">
  <title>Fatura vencida - Regularize</title>
  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0;
      min-height: 100vh;
      font-family: Arial, Helvetica, sans-serif;
      color: #1e293b;
      background:
        radial-gradient(circle at 0% 0%, rgba(245, 158, 11, .22), transparent 34%),
        radial-gradient(circle at 100% 100%, rgba(37, 99, 235, .16), transparent 28%),
        linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    }
    .page {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 32px 16px;
    }
    .panel {
      width: 100%;
      max-width: 820px;
      overflow: hidden;
      border: 1px solid #fde68a;
      border-radius: 28px;
      background: #ffffff;
      box-shadow: 0 28px 80px rgba(120, 53, 15, .18);
    }
    .hero {
      position: relative;
      overflow: hidden;
      padding: 34px;
      color: #ffffff;
      background: linear-gradient(135deg, #78350f 0%, #f59e0b 55%, #1d4ed8 100%);
    }
    .hero::after {
      content: "";
      position: absolute;
      inset: auto -60px -120px auto;
      width: 260px;
      height: 260px;
      border-radius: 999px;
      background: rgba(255, 255, 255, .12);
    }
    .brand {
      position: relative;
      z-index: 1;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      margin-bottom: 28px;
    }
    .mark {
      display: inline-grid;
      width: 48px;
      height: 48px;
      place-items: center;
      border-radius: 16px;
      background: rgba(255,255,255,.15);
      border: 1px solid rgba(255,255,255,.25);
      font-weight: 900;
    }
    .status {
      border-radius: 999px;
      background: rgba(255, 251, 235, .18);
      border: 1px solid rgba(254, 240, 138, .45);
      padding: 8px 12px;
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
    }
    h1 {
      position: relative;
      z-index: 1;
      max-width: 620px;
      margin: 0;
      font-size: 36px;
      line-height: 1.05;
    }
    .subtitle {
      position: relative;
      z-index: 1;
      max-width: 620px;
      margin: 14px 0 0;
      color: #fffbeb;
      font-size: 16px;
      line-height: 1.6;
    }
    .content { padding: 30px 34px 34px; }
    .greeting {
      margin: 0 0 22px;
      color: #475569;
      font-size: 16px;
      line-height: 1.7;
    }
    .greeting strong { color: #1e293b; }
    .summary {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 12px;
      margin-bottom: 26px;
    }
    .item {
      display: block;
      min-height: 94px;
      border: 1px solid #e2e8f0;
      border-radius: 18px;
      background: #f8fafc;
      padding: 16px;
      text-decoration: none;
      transition: border-color .18s ease, background .18s ease;
    }
    .item[href]:hover {
      border-color: #fcd34d;
      background: #fffbeb;
    }
    .item span {
      display: block;
      margin-bottom: 8px;
      color: #64748b;
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
    }
    .item strong {
      display: block;
      color: #1e293b;
      font-size: 18px;
      line-height: 1.25;
    }
    .support-item {
      border-color: #bbf7d0;
      background: #f0fdf4;
    }
    .support-item[href]:hover {
      border-color: #86efac;
      background: #dcfce7;
    }
    .actions {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 12px;
    }
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 48px;
      border-radius: 14px;
      background: #f59e0b;
      color: #ffffff;
      padding: 0 22px;
      font-weight: 800;
      text-decoration: none;
      box-shadow: 0 14px 30px rgba(245, 158, 11, .30);
    }
    .btn:hover { background: #d97706; }
    .btn.secondary {
      background: #16a34a;
      box-shadow: 0 14px 30px rgba(22, 163, 74, .22);
    }
    .btn.secondary:hover { background: #15803d; }
    .support {
      color: #64748b;
      font-size: 13px;
      line-height: 1.5;
    }
    .footnote {
      margin-top: 24px;
      border-top: 1px solid #e2e8f0;
      padding-top: 16px;
      color: #64748b;
      font-size: 12px;
      line-height: 1.6;
    }
    .fine {
      display: inline-block;
      margin-top: 14px;
      padding: 8px 14px;
      border: 1px dashed #fcd34d;
      border-radius: 12px;
      background: #fffbeb;
      font-size: 13px;
      color: #92400e;
    }
    @media (max-width: 680px) {
      .hero, .content { padding: 24px; }
      .brand { align-items: flex-start; }
      h1 { font-size: 28px; }
      .summary { grid-template-columns: 1fr; }
      .btn { width: 100%; }
    }
  </style>
</head>
<body>
  <main class="page">
    <section class="panel" aria-labelledby="titulo-aviso">
      <div class="hero">
        <div class="brand">
          <div class="mark">{{brand_mark}}</div>
          <div class="status">Fatura vencida</div>
        </div>
        <h1 id="titulo-aviso">Sua fatura venceu</h1>
        <p class="subtitle">Regularize agora e evite a suspensão da sua conexão. Durante o período de tolerância a navegação continua liberada.</p>
      </div>
      <div class="content">
        <p class="greeting">Olá, <strong>{{cliente_nome}}</strong>. Identificamos uma pendência no seu cadastro. Regularize o quanto antes para manter seu acesso, pois após o prazo limite a conexão é reduzida automaticamente.</p>

        <div class="summary" aria-label="Resumo do aviso">
          <a class="item" href="{{link_portal}}">
            <span>Central</span>
            <strong>Portal do Cliente</strong>
          </a>
          <a class="item support-item" href="{{link_suporte}}">
            <span>Suporte</span>
            <strong>WhatsApp</strong>
          </a>
          <div class="item">
            <span>Prazo</span>
            <strong>{{dias}} dias</strong>
          </div>
        </div>

        <div class="actions">
          <a class="btn" href="{{link_portal}}">Regularizar agora</a>
          <a class="btn secondary" href="{{link_suporte}}">Falar com suporte</a>
          <div class="support">Após o pagamento, a liberação é processada automaticamente sem necessidade de contato.</div>
        </div>

        <div class="fine">Se você já pagou, desconsidere este aviso: a liberação pode levar alguns minutos.</div>

        <div class="footnote">Esta página é exibida apenas enquanto houver fatura em aberto. Mantenha seus dados de contato atualizados para receber avisos e segunda via de cobrança.</div>
      </div>
    </section>
  </main>
</body>
</html>
HTML;
    }
};