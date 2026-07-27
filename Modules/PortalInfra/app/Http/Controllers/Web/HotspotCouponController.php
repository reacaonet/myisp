<?php

namespace Modules\PortalInfra\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\HotspotCoupon;
use Modules\Core\Models\Server;

class HotspotCouponController extends Controller
{
    public function index(Request $request)
    {
        $query = HotspotCoupon::with('server', 'client');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $coupons = $query->orderByDesc('created_at')->paginate(20);

        $stats = [
            'total' => HotspotCoupon::count(),
            'active' => HotspotCoupon::where('status', 'active')->count(),
            'used' => HotspotCoupon::where('status', 'used')->count(),
            'expired' => HotspotCoupon::where('status', 'expired')->count(),
        ];

        return view('infra::hotspot-coupons.index', compact('coupons', 'stats'));
    }

    public function create()
    {
        $servers = Server::orderBy('name')->get();
        return view('infra::hotspot-coupons.create', compact('servers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:hotspot_coupons,code',
            'profile' => 'nullable|string|max:100',
            'duration_hours' => 'required|integer|min:1|max:720',
            'price' => 'required|numeric|min:0',
            'server_id' => 'nullable|exists:servers,id',
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
        $coupon = HotspotCoupon::with('server', 'client')->findOrFail($id);
        return view('infra::hotspot-coupons.show', compact('coupon'));
    }

    public function edit($id)
    {
        $coupon = HotspotCoupon::findOrFail($id);
        $servers = Server::orderBy('name')->get();
        return view('infra::hotspot-coupons.edit', compact('coupon', 'servers'));
    }

    public function update(Request $request, $id)
    {
        $coupon = HotspotCoupon::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:hotspot_coupons,code,' . $id,
            'profile' => 'nullable|string|max:100',
            'duration_hours' => 'required|integer|min:1|max:720',
            'price' => 'required|numeric|min:0',
            'server_id' => 'nullable|exists:servers,id',
        ]);

        $coupon->update($validated);

        return redirect()->route('infra.hotspot-coupons.index')
            ->with('success', 'Cupom atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $coupon = HotspotCoupon::findOrFail($id);
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
            'server_id' => 'nullable|exists:servers,id',
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
