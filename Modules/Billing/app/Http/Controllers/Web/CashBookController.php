<?php

namespace Modules\Billing\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Models\CashBookEntry;
use Modules\Billing\Models\Invoice;

class CashBookController extends Controller
{
    public function index(Request $request)
    {
        $query = CashBookEntry::with('invoice');

        if ($request->get('start_date')) {
            $query->where('entry_date', '>=', $request->start_date);
        }
        if ($request->get('end_date')) {
            $query->where('entry_date', '<=', $request->end_date);
        }
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }
        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        $entries = $query->orderByDesc('entry_date')->orderByDesc('id')->paginate(20);

        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $summary = CashBookEntry::query()
            ->whereBetween('entry_date', [$startDate, $endDate]);

        $totalEntradas = (clone $summary)->where('type', 'entrada')->sum('amount');
        $totalSaidas = (clone $summary)->where('type', 'saida')->sum('amount');
        $saldo = $totalEntradas - $totalSaidas;

        $previousBalance = CashBookEntry::where('entry_date', '<', $startDate)
            ->selectRaw("SUM(CASE WHEN type = 'entrada' THEN amount ELSE 0 END) - SUM(CASE WHEN type = 'saida' THEN amount ELSE 0 END) as balance")
            ->value('balance') ?? 0;

        $saldoAcumulado = $previousBalance + $saldo;

        $categories = CashBookEntry::whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        return view('billing::cash-book.index', compact(
            'entries', 'totalEntradas', 'totalSaidas', 'saldo',
            'saldoAcumulado', 'categories', 'startDate', 'endDate',
            'previousBalance'
        ));
    }

    public function create()
    {
        return view('billing::cash-book.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:entrada,saida',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'entry_date' => 'required|date',
            'reference' => 'nullable|string|max:100',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        CashBookEntry::create($validated);

        return redirect()->route('billing.cash-book.index')
            ->with('success', 'Lancamento registrado com sucesso.');
    }

    public function show($id)
    {
        $entry = CashBookEntry::with('invoice')->findOrFail($id);
        return view('billing::cash-book.show', compact('entry'));
    }

    public function edit($id)
    {
        $entry = CashBookEntry::findOrFail($id);
        return view('billing::cash-book.edit', compact('entry'));
    }

    public function update(Request $request, $id)
    {
        $entry = CashBookEntry::findOrFail($id);

        $validated = $request->validate([
            'type' => 'required|in:entrada,saida',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'entry_date' => 'required|date',
            'reference' => 'nullable|string|max:100',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $entry->update($validated);

        return redirect()->route('billing.cash-book.index')
            ->with('success', 'Lancamento atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $entry = CashBookEntry::findOrFail($id);
        $entry->delete();

        return redirect()->route('billing.cash-book.index')
            ->with('success', 'Lancamento removido com sucesso.');
    }
}
