<?php

namespace Modules\CRM\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\Ticket;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with('client', 'latestMessage');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('codigo', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $tickets = $query->latest()->paginate(20);

        return view('crm::tickets.index', compact('tickets'));
    }

    public function show($id)
    {
        $ticket = Ticket::with(['client', 'contract.plan', 'messages'])->findOrFail($id);

        return view('crm::tickets.show', compact('ticket'));
    }

    public function updateStatus(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
            'priority' => 'nullable|in:low,medium,high,urgent',
        ]);

        $ticket->update(collect($validated)->filter()->toArray());

        return redirect()->route('crm.tickets.show', $ticket)
            ->with('success', 'Chamado atualizado.');
    }

    public function reply(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $ticket->messages()->create([
            'sender_type' => 'admin',
            'sender_id' => auth()->id(),
            'message' => $validated['message'],
        ]);

        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        return redirect()->route('crm.tickets.show', $ticket)
            ->with('success', 'Resposta enviada.');
    }

    public function destroy($id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->delete();

        return redirect()->route('crm.tickets.index')
            ->with('success', 'Chamado removido.');
    }

    /**
     * Aplica no cadastro o endereco que o cliente propos no chamado.
     *
     * A busca passa pelo cliente por causa do escopo: `tickets` nao tem
     * `company_id`, e `findOrFail` puro deixaria qualquer usuario com a
     * permissao `tickets` reescrever o endereco de um cliente de outra
     * franquia, so trocando o id na URL.
     */
    public function applyProposedAddress(Request $request, $id)
    {
        $ticket = $this->findAddressChangeOrFail($id);

        if ($ticket->status === 'resolved') {
            return back()->with('error', 'Este chamado ja teve o endereco aplicado.');
        }

        $address = $ticket->proposedAddress();

        $client = $ticket->client;

        if (! $client) {
            return back()->with('error', 'O cliente deste chamado nao existe mais.');
        }

        $current = $client->addresses()->first();

        if ($current) {
            $current->update($address);
        } else {
            $client->addresses()->create($address);
        }

        // `contracts.install_*` guardam o local da instalacao, que e um dado
        // historico: nao sao sobrescritos aqui de proposito.
        $ticket->update(['status' => 'resolved']);

        $ticket->messages()->create([
            'sender_type' => 'admin',
            'sender_id' => auth()->id(),
            'message' => 'Endereco atualizado com sucesso a partir deste chamado.',
        ]);

        return redirect()->route('crm.tickets.show', $ticket)
            ->with('success', 'Endereco aplicado no cadastro do cliente.');
    }

    public function rejectProposedAddress(Request $request, $id)
    {
        $ticket = $this->findAddressChangeOrFail($id);

        if ($ticket->status === 'resolved') {
            return back()->with('error', 'Este chamado ja teve o endereco aplicado.');
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        $ticket->update(['status' => 'closed']);

        $ticket->messages()->create([
            'sender_type' => 'admin',
            'sender_id' => auth()->id(),
            'message' => $validated['reason'] ?? 'Solicitacao de mudanca de endereco recusada.',
        ]);

        return redirect()->route('crm.tickets.show', $ticket)
            ->with('success', 'Solicitacao recusada.');
    }

    /**
     * Localiza um chamado de mudanca de endereco que o usuario atual pode
     * ver, dentro do proprio contexto de empresa/filial.
     */
    protected function findAddressChangeOrFail($id): Ticket
    {
        $ticket = Ticket::whereHas('client', fn ($c) => $c->scoped())
            ->with('client')
            ->findOrFail($id);

        abort_unless($ticket->isAddressChange(), 404);

        return $ticket;
    }
}
