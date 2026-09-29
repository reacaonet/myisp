<?php

namespace Modules\Core\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Models\SystemSetting;
use Modules\Core\Services\TenantContext;

class SystemSettingController extends Controller
{
    /** company_* das settings -> colunas canonicas de companies (Fase 2). */
    public const COMPANY_FIELDS = [
        'company_name' => 'legal_name',
        'company_fantasy' => 'fantasy_name',
        'company_document' => 'document',
        'company_state_registration' => 'state_registration',
        'company_municipal_registration' => 'municipal_registration',
        'company_phone' => 'phone',
        'company_cellphone' => 'cellphone',
        'company_email' => 'email',
        'company_website' => 'website',
        'company_address' => 'address',
        'company_city' => 'city',
        'company_state' => 'state',
        'company_zip' => 'zip',
    ];

    public function index()
    {
        $settings = SystemSetting::effective()->sortBy(
            fn ($setting) => $setting->group.'|'.$setting->key
        )->groupBy('group');

        $company = TenantContext::company();

        return view('core::settings.index', compact('settings', 'company'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        $companyData = [];

        foreach ($validated['settings'] as $key => $value) {
            if (isset(self::COMPANY_FIELDS[$key])) {
                $companyData[self::COMPANY_FIELDS[$key]] = filled($value) ? $value : null;

                continue;
            }

            SystemSetting::putValue($key, $value);
        }

        $this->syncCompany($companyData);

        $request->validate([
            'logo' => 'nullable|image|mimes:jpeg,png,webp,gif,svg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('landing', 'public');
            SystemSetting::set('landing_logo', $path, 'file', 'landing');
        }

        return back()->with('success', 'Configuracoes salvas com sucesso.');
    }

    public function create()
    {
        return view('core::settings.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:100',
            'value' => 'nullable|string',
            'type' => 'required|in:text,textarea,number,boolean,password,file',
            'group' => 'required|string|max:50',
        ]);

        $validated['key'] = trim($validated['key']);

        if (SystemSetting::query()
            ->where('company_id', SystemSetting::targetCompanyId())
            ->where('key', $validated['key'])
            ->exists()) {
            return back()->with('error', 'Ja existe uma configuracao com esta chave nesta compania.');
        }

        SystemSetting::create($validated);

        return redirect()->route('core.settings.index')
            ->with('success', 'Configuracao criada com sucesso.');
    }

    protected function syncCompany(array $data): void
    {
        if ($data === []) {
            return;
        }

        $company = TenantContext::company();

        if (! $company) {
            return;
        }

        $company->update($data);
    }
}
