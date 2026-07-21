<?php

namespace Modules\CRM\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\CRM\Models\Plan;

class PlanController extends Controller
{
    public function index()
    {
        return Plan::paginate();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'download_speed' => 'required|integer',
            'upload_speed' => 'required|integer',
            'price' => 'required|numeric',
            'setup_fee' => 'nullable|numeric',
            'billing_cycle' => 'required|in:monthly,quarterly,semiannual,annual',
            'description' => 'nullable|string',
            'max_simultaneous' => 'nullable|integer',
            'has_pppoe' => 'boolean',
            'has_hotspot' => 'boolean',
            'pool' => 'nullable|string',
            'address_list' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $plan = Plan::create($validated);

        return response()->json($plan, 201);
    }

    public function show($id)
    {
        return Plan::with('contracts')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $plan = Plan::findOrFail($id);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'download_speed' => 'integer',
            'upload_speed' => 'integer',
            'price' => 'numeric',
            'setup_fee' => 'nullable|numeric',
            'billing_cycle' => 'in:monthly,quarterly,semiannual,annual',
            'description' => 'nullable|string',
            'max_simultaneous' => 'nullable|integer',
            'has_pppoe' => 'boolean',
            'has_hotspot' => 'boolean',
            'pool' => 'nullable|string',
            'address_list' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $plan->update($validated);

        return response()->json($plan);
    }

    public function destroy($id)
    {
        $plan = Plan::findOrFail($id);
        $plan->delete();

        return response()->noContent();
    }
}
