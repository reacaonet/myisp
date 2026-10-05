<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            'block_page_enabled' => ['1', 'boolean'],
            'block_page_url' => ['', 'text'],
            'block_portal_url' => ['', 'text'],
            'block_support_url' => ['', 'text'],
            'block_mark_text' => ['MK', 'text'],
            'block_page_html' => [$this->defaultHtml(), 'textarea'],
        ];

        // Query builder em vez do model: o hook `creating` do SystemSetting
        // consulta `companies` via TenantContext, tabela que ainda nao existe
        // nesta altura da instalacao. Sao linhas de template da raiz.
        foreach ($defaults as $key => [$value, $type]) {
            $this->seedSetting($key, $value, $type, 'block');
        }
    }

    public function down(): void
    {
        DB::table('system_settings')->where('group', 'block')->delete();
    }

    /**
     * Cria a chave somente se ainda nao existir, preservando o valor de quem
     * ja configurou. Equivale ao firstOrCreate do model, sem passar por ele.
     */
    private function seedSetting(string $key, string $value, string $type, string $group): void
    {
        if (DB::table('system_settings')->where('key', $key)->exists()) {
            return;
        }

        DB::table('system_settings')->insert([
            'key' => $key,
            'value' => $value,
            'type' => $type,
            'group' => $group,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function defaultHtml(): string
    {
        return <<<'HTML'
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="color-scheme" content="light">
  <title>Acesso temporariamente bloqueado</title>
  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0;
      min-height: 100vh;
      font-family: Arial, Helvetica, sans-serif;
      color: #0f172a;
      background:
        radial-gradient(circle at 0% 0%, rgba(37, 99, 235, .22), transparent 34%),
        radial-gradient(circle at 100% 100%, rgba(20, 184, 166, .18), transparent 28%),
        linear-gradient(135deg, #f8fafc 0%, #e0f2fe 100%);
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
      border: 1px solid #dbe5f0;
      border-radius: 28px;
      background: #ffffff;
      box-shadow: 0 28px 80px rgba(15, 23, 42, .16);
    }
    .hero {
      position: relative;
      overflow: hidden;
      padding: 34px;
      color: #ffffff;
      background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 60%, #0f766e 100%);
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
      letter-spacing: 0;
    }
    .status {
      border-radius: 999px;
      background: rgba(254, 242, 242, .16);
      border: 1px solid rgba(254, 202, 202, .45);
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
      letter-spacing: 0;
    }
    .subtitle {
      position: relative;
      z-index: 1;
      max-width: 620px;
      margin: 14px 0 0;
      color: #dbeafe;
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
    .greeting strong { color: #0f172a; }
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
      border-color: #bfdbfe;
      background: #eff6ff;
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
      color: #0f172a;
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
      background: #2563eb;
      color: #ffffff;
      padding: 0 22px;
      font-weight: 800;
      text-decoration: none;
      box-shadow: 0 14px 30px rgba(37, 99, 235, .28);
    }
    .btn:hover { background: #1d4ed8; }
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
    <section class="panel" aria-labelledby="titulo-bloqueio">
      <div class="hero">
        <div class="brand">
          <div class="mark">{{brand_mark}}</div>
          <div class="status">Acesso pausado</div>
        </div>
        <h1 id="titulo-bloqueio">Acesso temporariamente bloqueado</h1>
        <p class="subtitle">Regularize sua assinatura pelo Portal do Cliente para liberar a navegação com segurança.</p>
      </div>
      <div class="content">
        <p class="greeting">Olá, <strong>{{cliente_nome}}</strong>. Identificamos uma pendência financeira vinculada ao cadastro. Use o Portal do Cliente para consultar a cobrança e regularizar o acesso. Após a confirmação do pagamento, a liberação da conexão é processada automaticamente.</p>

        <div class="summary" aria-label="Resumo do bloqueio">
          <a class="item" href="{{link_portal}}">
            <span>Central</span>
            <strong>Portal do Cliente</strong>
          </a>
          <a class="item support-item" href="{{link_suporte}}">
            <span>Suporte</span>
            <strong>WhatsApp</strong>
          </a>
          <div class="item">
            <span>Liberação</span>
            <strong>Após confirmação</strong>
          </div>
        </div>

        <div class="actions">
          <a class="btn" href="{{link_portal}}">Acessar Portal do Cliente</a>
          <a class="btn secondary" href="{{link_suporte}}">Falar com suporte</a>
          <div class="support">Se você já pagou, aguarde a baixa automática ou fale com o suporte do seu provedor.</div>
        </div>

        <div class="footnote">Esta página é exibida apenas durante o período de bloqueio. Mantenha seus dados de contato atualizados para receber avisos e segunda via de cobrança.</div>
      </div>
    </section>
  </main>
</body>
</html>
HTML;
    }
};