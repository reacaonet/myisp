<?php

namespace Modules\Core\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Models\SystemSetting;

class BlockPageController extends Controller
{
    public function show(Request $request)
    {
        $preview = $request->query('preview') === '1';
        $settings = SystemSetting::getGroup('block');
        $landing = SystemSetting::getGroup('landing');
        $company = SystemSetting::getGroup('company');

        if (!$preview && (string) ($settings['block_page_enabled'] ?? '') !== '1') {
            abort(404);
        }

        $html = $settings['block_page_html'] ?: $this->defaultHtml();

        $whatsapp = preg_replace('/\D/', '', $landing['landing_whatsapp'] ?? '');
        $portalUrl = $settings['block_portal_url'] ?: route('crm.portal.login');
        $supportUrl = $settings['block_support_url']
            ?: ($whatsapp ? 'https://wa.me/' . $whatsapp : route('crm.portal.login'));
        $brandMark = $settings['block_mark_text'] ?: $this->brandInitials($company);
        $clientName = trim((string) $request->query('nome', 'cliente'));
        $providerName = $company['company_fantasy'] ?? ($company['company_name'] ?? 'Provedor');

        $html = strtr($html, [
            '{{link_portal}}' => e($portalUrl),
            '{{link_suporte}}' => e($supportUrl),
            '{{brand_mark}}' => e($brandMark),
            '{{cliente_nome}}' => e($clientName !== '' ? $clientName : 'cliente'),
            '{{provider_name}}' => e($providerName),
        ]);

        return response($html, 200)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    private function brandInitials(array $company): string
    {
        $name = $company['company_fantasy'] ?? ($company['company_name'] ?? '');

        if (!$name) {
            return 'ISP';
        }

        $initials = '';
        foreach (preg_split('/\s+/', trim($name)) as $word) {
            if ($word !== '') {
                $initials .= mb_strtoupper(mb_substr($word, 0, 1));
            }
            if (mb_strlen($initials) >= 3) {
                break;
            }
        }

        return $initials !== '' ? $initials : 'ISP';
    }

    private function defaultHtml(): string
    {
        $brand = 'MK';

        return <<<HTML
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Acesso temporariamente bloqueado</title>
  <style>
    body { margin:0; font-family:Arial,sans-serif; min-height:100vh; display:flex; align-items:center; justify-content:center; background:#f1f5f9; color:#0f172a; }
    .panel { max-width:560px; background:#fff; border:1px solid #e2e8f0; border-radius:24px; padding:40px; box-shadow:0 20px 60px rgba(15,23,42,.15); text-align:center; }
    .mark { display:inline-grid; width:52px; height:52px; place-items:center; border-radius:16px; background:#1d4ed8; color:#fff; font-weight:900; margin-bottom:18px; }
    h1 { font-size:26px; margin:0 0 12px; }
    p { color:#475569; line-height:1.6; margin:0 0 20px; }
    a { display:inline-block; margin:6px; padding:12px 22px; border-radius:12px; background:#2563eb; color:#fff; text-decoration:none; font-weight:700; }
  </style>
</head>
<body>
  <div class="panel">
    <div class="mark">$brand</div>
    <h1>Acesso temporariamente bloqueado</h1>
    <p>Acesse o <strong>Portal do Cliente</strong> para regularizar sua fatura. Após a confirmação do pagamento, a liberação é processada automaticamente.</p>
    <a href="{{link_portal}}">Acessar Portal do Cliente</a>
  </div>
</body>
</html>
HTML;
    }
}