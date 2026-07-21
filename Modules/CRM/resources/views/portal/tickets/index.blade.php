@extends('crm::portal.layouts.master')

@section('title', 'Meus Chamados')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-800">Meus Chamados</h3>
        <a href="{{ route('crm.portal.tickets.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Abrir Chamado</a>
    </div>

    @if($tickets->isEmpty())
    <div class="p-12 text-center text-gray-400">
        <p class="text-lg mb-2">Nenhum chamado encontrado.</p>
        <a href="{{ route('crm.portal.tickets.create') }}" class="text-blue-600 hover:underline">Abrir primeiro chamado</a>
    </div>
    @else
    <div class="divide-y divide-gray-100">
        @foreach($tickets as $ticket)
        <a href="{{ route('crm.portal.tickets.show', $ticket) }}" class="block p-6 hover:bg-gray-50 transition">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="font-mono text-xs text-gray-500">{{ $ticket->codigo }}</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium
                        @if($ticket->status == 'open') bg-blue-100 text-blue-700
                        @elseif($ticket->status == 'in_progress') bg-yellow-100 text-yellow-700
                        @elseif($ticket->status == 'resolved') bg-green-100 text-green-700
                        @else bg-gray-100 text-gray-600 @endif">
                        {{ $ticket->status == 'open' ? 'Aberto' : ($ticket->status == 'in_progress' ? 'Em Andamento' : ($ticket->status == 'resolved' ? 'Resolvido' : 'Fechado')) }}
                    </span>
                </div>
                <div class="text-right">
                    <span class="px-2 py-0.5 rounded text-xs font-medium
                        @if($ticket->priority == 'urgent') bg-red-100 text-red-700
                        @elseif($ticket->priority == 'high') bg-orange-100 text-orange-700
                        @elseif($ticket->priority == 'medium') bg-gray-100 text-gray-600
                        @else bg-gray-50 text-gray-500 @endif">
                        {{ $ticket->priority == 'urgent' ? 'Urgente' : ($ticket->priority == 'high' ? 'Alta' : ($ticket->priority == 'medium' ? 'Media' : 'Baixa')) }}
                    </span>
                </div>
            </div>
            <p class="font-medium text-gray-900 mt-2">{{ $ticket->subject }}</p>
            @if($ticket->latestMessage)
            <p class="text-sm text-gray-500 mt-1 truncate">{{ $ticket->latestMessage->message }}</p>
            @endif
            <p class="text-xs text-gray-400 mt-2">{{ $ticket->created_at->format('d/m/Y H:i') }} @if($ticket->category) &middot; {{ ucfirst($ticket->category) }} @endif</p>
        </a>
        @endforeach
    </div>

    <div class="p-4 border-t border-gray-200">
        {{ $tickets->links() }}
    </div>
    @endif
</div>
@endsection
