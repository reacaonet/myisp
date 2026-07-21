<?php

namespace Modules\CRM\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\Ticket;
use Modules\CRM\Models\TicketMessage;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with('client', 'latestMessage');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('codigo', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($c) => $c->where('name', 'like', "%{$search}%"));
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
}
