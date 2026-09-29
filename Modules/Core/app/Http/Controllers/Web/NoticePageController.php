<?php

namespace Modules\Core\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Models\SystemSetting;
use Modules\Core\Services\TenantContext;

class NoticePageController extends Controller
{
    public function show(Request $request)
    {
        $preview = $request->query('preview') === '1';
        $settings = SystemSetting::getGroup('aviso');
        $block = SystemSetting::getGroup('block');
        $landing = SystemSetting::getGroup('landing');
        $company = TenantContext::company();

        if (! $preview && (string) ($settings['notice_page_enabled'] ?? '') !== '1') {
            abort(404);
        }

        $html = $settings['notice_page_html'] ?: $this->defaultHtml();

        $whatsapp = preg_replace('/\D/', '', $landing['landing_whatsapp'] ?? '');
        $portalUrl = $block['block_portal_url'] ?: route('crm.portal.login');
        $supportUrl = $block['block_support_url']
            ?: ($whatsapp ? 'https://wa.me/'.$whatsapp : route('crm.portal.login'));
        $brandMark = $settings['notice_mark_text'] ?: $company?->initials();
        $clientName = trim((string) $request->query('nome', 'cliente'));
        $vencimento = trim((string) $request->query('vencimento', ''));
        $dias = trim((string) $request->query('dias', ''));

        $html = strtr($html, [
            '{{link_portal}}' => e($portalUrl),
            '{{link_suporte}}' => e($supportUrl),
            '{{brand_mark}}' => e($brandMark ?: 'ISP'),
            '{{cliente_nome}}' => e($clientName !== '' ? $clientName : 'cliente'),
            '{{vencimento}}' => e($vencimento),
            '{{dias}}' => e($dias !== '' ? $dias : '10'),
        ]);

        return response($html, 200)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    private function defaultHtml(): string
    {
        $brand = 'MK';

        return <<<HTML
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Fatura vencida - Regularize</title>
  <style>
    body { margin:0; font-family:Arial,sans-serif; min-height:100vh; display:flex; align-items:center; justify-content:center; background:#fffbeb; color:#1e293b; }
    .panel { max-width:560px; background:#fff; border:1px solid #fde68a; border-radius:24px; padding:40px; box-shadow:0 20px 60px rgba(120,53,15,.15); text-align:center; }
    .mark { display:inline-grid; width:52px; height:52px; place-items:center; border-radius:16px; background:#f59e0b; color:#fff; font-weight:900; margin-bottom:18px; }
    h1 { font-size:26px; margin:0 0 12px; }
    p { color:#475569; line-height:1.6; margin:0 0 20px; }
    a { display:inline-block; margin:6px; padding:12px 22px; border-radius:12px; background:#f59e0b; color:#fff; text-decoration:none; font-weight:700; }
  </style>
</head>
<body>
  <div class="panel">
    <div class="mark">$brand</div>
    <h1>Sua fatura venceu</h1>
    <p>Regularize agora pelo <strong>Portal do Cliente</strong> para manter seu acesso. Após o pagamento, a liberação é processada automaticamente.</p>
    <a href="{{link_portal}}">Regularizar agora</a>
  </div>
</body>
</html>
HTML;
    }
}
