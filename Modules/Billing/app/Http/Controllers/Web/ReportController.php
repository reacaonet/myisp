<?php

namespace Modules\Billing\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\CashBookEntry;
use Modules\Billing\Models\Payment;
use Modules\CRM\Models\Client;
use Modules\CRM\Models\Contract;
use Modules\CRM\Models\Plan;
use DB;

class ReportController extends Controller
{
    public function index()
    {
        return view('billing::reports.index');
    }

    public function invoicesByDueDate(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

        $invoices = Invoice::with('client', 'contract.plan')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->orderBy('due_date')
            ->get();

        $stats = [
            'total' => $invoices->sum('total'),
            'pending' => $invoices->where('status', 'pending')->sum('total'),
            'paid' => $invoices->where('status', 'paid')->sum('total'),
            'overdue' => $invoices->where('status', 'overdue')->sum('total'),
            'count' => $invoices->count(),
        ];

        return view('billing::reports.invoices-by-due-date', compact('invoices', 'stats', 'startDate', 'endDate'));
    }

    public function invoicesByStatus(Request $request)
    {
        $status = $request->get('status', 'pending');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = Invoice::with('client', 'contract.plan')->where('status', $status);

        if ($startDate) {
            $query->where('due_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('due_date', '<=', $endDate);
        }

        $invoices = $query->orderBy('due_date')->get();

        $stats = [
            'total' => $invoices->sum('total'),
            'count' => $invoices->count(),
        ];

        return view('billing::reports.invoices-by-status', compact('invoices', 'stats', 'status', 'startDate', 'endDate'));
    }

    public function subscribers(Request $request)
    {
        $planId = $request->get('plan_id');

        $query = Contract::with('client', 'plan', 'server')
            ->where('status', 'active');

        if ($planId) {
            $query->where('plan_id', $planId);
        }

        $contracts = $query->orderBy('activation_date', 'desc')->get();

        $plans = Plan::orderBy('name')->get();

        $stats = [
            'total' => $contracts->count(),
            'by_plan' => $contracts->groupBy('plan_id')->map(fn($c) => [
                'plan' => $c->first()->plan?->name ?? 'N/A',
                'count' => $c->count(),
                'revenue' => $c->sum(fn($c) => $c->plan?->price ?? 0),
            ])->values(),
            'total_revenue' => $contracts->sum(fn($c) => $c->plan?->price ?? 0),
        ];

        return view('billing::reports.subscribers', compact('contracts', 'plans', 'stats', 'planId'));
    }

    public function plansVsClients()
    {
        $plans = Plan::withCount(['contracts as active_contracts' => function ($q) {
            $q->where('status', 'active');
        }])->get();

        $totalActive = Contract::where('status', 'active')->count();
        $totalRevenue = Contract::where('status', 'active')
            ->join('plans', 'contracts.plan_id', '=', 'plans.id')
            ->sum('plans.price');

        return view('billing::reports.plans-vs-clients', compact('plans', 'totalActive', 'totalRevenue'));
    }

    public function cashFlow(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $payments = Payment::with('invoice.client')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->orderBy('payment_date')
            ->get();

        $cashEntries = CashBookEntry::query()
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->orderBy('entry_date')
            ->get();

        $totalRecebido = $payments->sum('amount');
        $totalEntradas = $cashEntries->where('type', 'entrada')->sum('amount');
        $totalSaidas = $cashEntries->where('type', 'saida')->sum('amount');

        $dailyData = collect();
        $current = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);

        while ($current->lte($end)) {
            $date = $current->toDateString();
            $dayPayments = $payments->where('payment_date', $date)->sum('amount');
            $dayEntries = $cashEntries->where('entry_date', $date)->where('type', 'entrada')->sum('amount');
            $dayExits = $cashEntries->where('entry_date', $date)->where('type', 'saida')->sum('amount');

            $dailyData->push([
                'date' => $date,
                'payments' => $dayPayments,
                'entries' => $dayEntries,
                'exits' => $dayExits,
            ]);

            $current->addDay();
        }

        return view('billing::reports.cash-flow', compact(
            'payments', 'cashEntries', 'totalRecebido',
            'totalEntradas', 'totalSaidas', 'dailyData',
            'startDate', 'endDate'
        ));
    }
}
