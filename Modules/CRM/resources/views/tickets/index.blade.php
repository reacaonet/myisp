@extends('core::layouts.master')

@section('title', 'Chamados')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200">
        <form method="GET" class="flex items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar chamado..."
                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm">
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Todos Status</option>
                <option value="open" @selected(request('status') == 'open')>Aberto</option>
                <option value="in_progress" @selected(request('status') == 'in_progress')>Em Andamento</option>
                <option value="resolved" @selected(request('status') == 'resolved')>Resolvido</option>
                <option value="closed" @selected(request('status') == 'closed')>Fechado</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Buscar</button>
        </form>
    </div>

    @if($tickets->isEmpty())
    <div class="p-12 text-center text-gray-400">Nenhum chamado encontrado.</div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b border-gray-200">
                    <th class="px-6 py-3 font-medium">Codigo</th>
                    <th class="px-6 py-3 font-medium">Cliente</th>
                    <th class="px-6 py-3 font-medium">Assunto</th>
                    <th class="px-6 py-3 font-medium">Categoria</th>
                    <th class="px-6 py-3 font-medium">Prioridade</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium">Atualizado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tickets as $ticket)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="px-6 py-3">
                        <a href="{{ route('crm.tickets.show', $ticket) }}" class="font-mono text-blue-600 hover:underline">{{ $ticket->codigo }}</a>
                    </td>
                    <td class="px-6 py-3 text-gray-900">{{ $ticket->client->name }}</td>
                    <td class="px-6 py-3 text-gray-900">{{ $ticket->subject }}</td>
                    <td class="px-6 py-3 text-gray-600">{{ $ticket->category ? ucfirst($ticket->category) : '-' }}</td>
                    <td class="px-6 py-3">
                        <span class="px-2 py-0.5 rounded text-xs font-medium
                            @if($ticket->priority == 'urgent') bg-red-100 text-red-700
                            @elseif($ticket->priority == 'high') bg-orange-100 text-orange-700
                            @elseif($ticket->priority == 'medium') bg-gray-100 text-gray-600
                            @else bg-gray-50 text-gray-500 @endif">
                            {{ $ticket->priority == 'urgent' ? 'Urgente' : ($ticket->priority == 'high' ? 'Alta' : ($ticket->priority == 'medium' ? 'Media' : 'Baixa')) }}
                        </span>
                    </td>
                    <td class="px-6 py-3">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($ticket->status == 'open') bg-blue-100 text-blue-700
                            @elseif($ticket->status == 'in_progress') bg-yellow-100 text-yellow-700
                            @elseif($ticket->status == 'resolved') bg-green-100 text-green-700
                            @else bg-gray-100 text-gray-600 @endif">
                            {{ $ticket->status == 'open' ? 'Aberto' : ($ticket->status == 'in_progress' ? 'Em Andamento' : ($ticket->status == 'resolved' ? 'Resolvido' : 'Fechado')) }}
                        </span>
                    </td>
                    <td class="px-6 py-3 text-gray-500">{{ $ticket->updated_at->format('d/m/Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-gray-200">
        {{ $tickets->links() }}
    </div>
    @endif
</div>
@endsection
