<?php

namespace Modules\Core\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Services\TenantContext;
use Modules\Billing\Models\Invoice;
use Modules\CRM\Models\Client;
use Modules\CRM\Models\Contract;
use Modules\CRM\Models\Ticket;

/**
 * Painel do franqueado.
 *
 * A pagina nao aplica filtro proprio de empresa: ela reaproveita o TenantContext,
 * que ja resolve a unica empresa do usuario a partir de company_user. Se o
 * usuario nao tem empresa vinculada, o contexto fica nulo e a listagem volta
 * vazia em vez de vazar a rede inteira.
 */
class FranchiseeController extends Controller
{
    /** Campos livres varridos pela busca da lista de clientes. */
    private const SEARCHABLE = ['name', 'document', 'email', 'cellphone', 'login'];

    private const PER_PAGE = 15;

    private const OPEN_TICKET_STATUSES = ['open', 'in_progress'];

    private const OPEN_INVOICE_STATUSES = ['pending', 'overdue'];

    public function index()
    {
        $clientQuery = $this->scopedClients();

        $contractQuery = Contract::query()->whereHas(
            'client',
            fn ($q) => $q->when(
                ! TenantContext::isCrossTenant(),
                fn ($inner) => $inner->forCompany(TenantContext::companyId())
            )
        );

        $ticketQuery = Ticket::query()->whereHas(
            'client',
            fn ($q) => $q->when(
                ! TenantContext::isCrossTenant(),
                fn ($inner) => $inner->forCompany(TenantContext::companyId())
            )
        );

        $invoiceQuery = $this->scopedInvoices();

        return view('core::franchisee.index', [
            'company' => TenantContext::company(),
            'stats' => [
                'clients_total' => (clone $clientQuery)->count(),
                'clients_active' => (clone $clientQuery)->where('status', 'active')->count(),
                'clients_without_contract' => (clone $clientQuery)->doesntHave('contracts')->count(),
                'contracts_active' => (clone $contractQuery)->where('status', 'active')->count(),
                'tickets_open' => (clone $ticketQuery)->whereIn('status', self::OPEN_TICKET_STATUSES)->count(),
                'invoices_open' => (clone $invoiceQuery)->whereIn('status', self::OPEN_INVOICE_STATUSES)->count(),
                'invoices_open_total' => (float) (clone $invoiceQuery)
                    ->whereIn('status', self::OPEN_INVOICE_STATUSES)
                    ->sum('total'),
            ],
            'recentClients' => (clone $clientQuery)->latest()->limit(8)->get(),
            'openTickets' => (clone $ticketQuery)
                ->whereIn('status', self::OPEN_TICKET_STATUSES)
                ->with('client')
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }

    public function clients(Request $request)
    {
        $query = $this->scopedClients();

        $search = trim((string) $request->get('search', ''));

        if ($search !== '') {
            $term = '%'.$search.'%';

            $query->where(function ($q) use ($term) {
                foreach (self::SEARCHABLE as $column) {
                    $q->orWhere($column, 'like', $term);
                }
            });
        }

        $status = $request->get('status');

        if (in_array($status, ['active', 'inactive', 'suspended', 'canceled'], true)) {
            $query->where('status', $status);
        }

        return view('core::franchisee.clients', [
            'company' => TenantContext::company(),
            'clients' => $query->orderBy('name')->paginate(self::PER_PAGE)->withQueryString(),
            'search' => $search,
            'status' => $status,
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Client>
     */
    private function scopedClients()
    {
        $query = Client::query()->with('branch');

        if (! TenantContext::isCrossTenant()) {
            $query->forCompany(TenantContext::companyId());
        }

        return $query;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Invoice>
     */
    private function scopedInvoices()
    {
        $query = Invoice::query();

        if (! TenantContext::isCrossTenant()) {
            $query->forCompany(TenantContext::companyId());
        }

        return $query;
    }
}