<?php

namespace Modules\Core\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Models\SystemSetting;

class SystemSettingController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::orderBy('group')->orderBy('key')->get()->groupBy('group');
        return view('core::settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($validated['settings'] as $key => $value) {
            SystemSetting::where('key', $key)->update(['value' => $value]);
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
            'key' => 'required|string|max:100|unique:system_settings,key',
            'value' => 'nullable|string',
            'type' => 'required|in:text,textarea,number,boolean,password',
            'group' => 'required|string|max:50',
        ]);

        SystemSetting::create($validated);

        return redirect()->route('core.settings.index')
            ->with('success', 'Configuracao criada com sucesso.');
    }
}
