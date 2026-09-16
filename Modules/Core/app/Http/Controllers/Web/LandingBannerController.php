<?php

namespace Modules\Core\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Models\LandingBanner;

class LandingBannerController extends Controller
{
    public function index()
    {
        $banners = LandingBanner::ordered()->get();
        return view('core::banners.index', compact('banners'));
    }

    public function create()
    {
        return view('core::banners.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string',
            'badge' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,webp,gif|max:4096',
            'link_url' => 'nullable|url|max:255',
            'link_label' => 'nullable|string|max:255',
            'highlight' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? LandingBanner::max('sort_order') + 1;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('banners', 'public');
        }

        LandingBanner::create($validated);

        return redirect()->route('core.banners.index')
            ->with('success', 'Banner criado com sucesso.');
    }

    public function edit($id)
    {
        $banner = LandingBanner::findOrFail($id);
        return view('core::banners.edit', compact('banner'));
    }

    public function update(Request $request, $id)
    {
        $banner = LandingBanner::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string',
            'badge' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,webp,gif|max:4096',
            'link_url' => 'nullable|url|max:255',
            'link_label' => 'nullable|string|max:255',
            'highlight' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            if ($banner->image) {
                Storage::disk('public')->delete($banner->image);
            }
            $validated['image'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($validated);

        return redirect()->route('core.banners.index')
            ->with('success', 'Banner atualizado com sucesso.');
    }

    public function move($id, $direction)
    {
        $banner = LandingBanner::findOrFail($id);
        $banners = LandingBanner::ordered()->get();
        $keys = $banners->pluck('id')->flip();

        $currentKey = $keys[$banner->id] ?? null;
        if ($currentKey === null) {
            return back()->with('error', 'Banner nao encontrado.');
        }

        $swapKey = $direction === 'up' ? $currentKey - 1 : $currentKey + 1;
        if ($swapKey < 0 || $swapKey >= $banners->count()) {
            return back();
        }

        $swap = $banners[$swapKey];
        $bannerSort = $banner->sort_order;
        $banner->update(['sort_order' => $swap->sort_order]);
        $swap->update(['sort_order' => $bannerSort]);

        return back()->with('success', 'Ordem atualizada.');
    }

    public function destroy($id)
    {
        $banner = LandingBanner::findOrFail($id);
        if ($banner->image) {
            Storage::disk('public')->delete($banner->image);
        }
        $banner->delete();

        return redirect()->route('core.banners.index')
            ->with('success', 'Banner removido com sucesso.');
    }
}