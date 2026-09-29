<?php

namespace Modules\PortalInfra\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;
use Modules\Core\Services\TenantContext;
use Modules\CRM\Models\HotspotCoupon;
use Modules\CRM\Models\MikrotikServer;

class HotspotCouponController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->scopedCoupons()->with('server', 'client');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $coupons = $query->orderByDesc('created_at')->paginate(20);

        $stats = [
            'total' => $this->scopedCoupons()->count(),
            'active' => $this->scopedCoupons()->where('status', 'active')->count(),
            'used' => $this->scopedCoupons()->where('status', 'used')->count(),
            'expired' => $this->scopedCoupons()->where('status', 'expired')->count(),
        ];

        return view('infra::hotspot-coupons.index', compact('coupons', 'stats'));
    }

    private function scopedCoupons(): Builder
    {
        if (TenantContext::isCrossTenant()) {
            return HotspotCoupon::query();
        }

        return HotspotCoupon::forCompany(TenantContext::companyId());
    }

    private function findCoupon($id): HotspotCoupon
    {
        return $this->scopedCoupons()->findOrFail($id);
    }

    private function serverRule(): Exists
    {
        return Rule::exists('mikrotik_servers', 'id')
            ->where(fn ($query) => $query
                ->when(! TenantContext::isCrossTenant(), fn ($q) => $q->where('company_id', TenantContext::companyId())));
    }

    private function codeRule(?int $ignoreId = null): Unique
    {
        return Rule::unique('hotspot_coupons', 'code')
            ->where(fn ($query) => $query
                ->when(! TenantContext::isCrossTenant(), fn ($q) => $q->where('company_id', TenantContext::companyId())))
            ->ignore($ignoreId);
    }

    public function create()
    {
        $servers = MikrotikServer::scoped()->orderBy('name')->get();

        return view('infra::hotspot-coupons.create', compact('servers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', $this->codeRule()],
            'profile' => 'nullable|string|max:100',
            'duration_hours' => 'required|integer|min:1|max:720',
            'price' => 'required|numeric|min:0',
            'server_id' => ['nullable', $this->serverRule()],
            'quantity' => 'nullable|integer|min:1|max:100',
        ]);

        $quantity = $validated['quantity'] ?? 1;
        unset($validated['quantity']);

        for ($i = 0; $i < $quantity; $i++) {
            $code = $i === 0 ? $validated['code'] : strtoupper(substr(md5(uniqid()), 0, 8));
            HotspotCoupon::create(array_merge($validated, [
                'code' => $code,
                'expires_at' => now()->addHours($validated['duration_hours']),
            ]));
        }

        return redirect()->route('infra.hotspot-coupons.index')
            ->with('success', "{$quantity} cupom(ns) criado(s) com sucesso.");
    }

    public function show($id)
    {
        $coupon = $this->findCoupon($id);

        return view('infra::hotspot-coupons.show', compact('coupon'));
    }

    public function edit($id)
    {
        $coupon = $this->findCoupon($id);
        $servers = MikrotikServer::scoped()->orderBy('name')->get();

        return view('infra::hotspot-coupons.edit', compact('coupon', 'servers'));
    }

    public function update(Request $request, $id)
    {
        $coupon = $this->findCoupon($id);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', $this->codeRule((int) $coupon->id)],
            'profile' => 'nullable|string|max:100',
            'duration_hours' => 'required|integer|min:1|max:720',
            'price' => 'required|numeric|min:0',
            'server_id' => ['nullable', $this->serverRule()],
        ]);

        $coupon->update($validated);

        return redirect()->route('infra.hotspot-coupons.index')
            ->with('success', 'Cupom atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $coupon = $this->findCoupon($id);
        $coupon->delete();

        return redirect()->route('infra.hotspot-coupons.index')
            ->with('success', 'Cupom removido com sucesso.');
    }

    public function generateBatch(Request $request)
    {
        $validated = $request->validate([
            'profile' => 'nullable|string|max:100',
            'duration_hours' => 'required|integer|min:1|max:720',
            'price' => 'required|numeric|min:0',
            'server_id' => ['nullable', $this->serverRule()],
            'quantity' => 'required|integer|min:1|max:500',
        ]);

        $quantity = $validated['quantity'];
        unset($validated['quantity']);

        $coupons = collect();
        for ($i = 0; $i < $quantity; $i++) {
            $coupons->push(HotspotCoupon::create(array_merge($validated, [
                'code' => strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 12)),
                'expires_at' => now()->addHours($validated['duration_hours']),
            ])));
        }

        return view('infra::hotspot-coupons.batch', compact('coupons'));
    }
}
