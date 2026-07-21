@extends('core::layouts.master')

@section('title', "Chamado {$ticket->codigo}")

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-bold text-gray-900">{{ $ticket->subject }}</h2>
                    <span class="font-mono text-xs text-gray-500">{{ $ticket->codigo }}</span>
                </div>
                <p class="text-sm text-gray-500 mt-1">
                    Cliente: {{ $ticket->client->name }} &middot; Aberto em {{ $ticket->created_at->format('d/m/Y H:i') }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium
                    @if($ticket->status == 'open') bg-blue-100 text-blue-700
                    @elseif($ticket->status == 'in_progress') bg-yellow-100 text-yellow-700
                    @elseif($ticket->status == 'resolved') bg-green-100 text-green-700
                    @else bg-gray-100 text-gray-600 @endif">
                    {{ $ticket->status == 'open' ? 'Aberto' : ($ticket->status == 'in_progress' ? 'Em Andamento' : ($ticket->status == 'resolved' ? 'Resolvido' : 'Fechado')) }}
                </span>
                <span class="px-2.5 py-0.5 rounded text-xs font-medium
                    @if($ticket->priority == 'urgent') bg-red-100 text-red-700
                    @elseif($ticket->priority == 'high') bg-orange-100 text-orange-700
                    @elseif($ticket->priority == 'medium') bg-gray-100 text-gray-600
                    @else bg-gray-50 text-gray-500 @endif">
                    {{ $ticket->priority == 'urgent' ? 'Urgente' : ($ticket->priority == 'high' ? 'Alta' : ($ticket->priority == 'medium' ? 'Media' : 'Baixa')) }}
                </span>
            </div>
        </div>

        <div class="p-6">
            <div class="flex items-center gap-4">
                <form method="POST" action="{{ route('crm.tickets.status', $ticket) }}" class="flex items-center gap-4">
                    @csrf
                    <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="open" @selected($ticket->status == 'open')>Aberto</option>
                        <option value="in_progress" @selected($ticket->status == 'in_progress')>Em Andamento</option>
                        <option value="resolved" @selected($ticket->status == 'resolved')>Resolvido</option>
                        <option value="closed" @selected($ticket->status == 'closed')>Fechado</option>
                    </select>
                    <select name="priority" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="low" @selected($ticket->priority == 'low')>Baixa</option>
                        <option value="medium" @selected($ticket->priority == 'medium')>Media</option>
                        <option value="high" @selected($ticket->priority == 'high')>Alta</option>
                        <option value="urgent" @selected($ticket->priority == 'urgent')>Urgente</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Alterar</button>
                </form>
            </div>

            <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mt-4">
                @if($ticket->category)
                <div>
                    <dt class="text-gray-500">Categoria</dt>
                    <dd class="font-medium text-gray-900">{{ ucfirst($ticket->category) }}</dd>
                </div>
                @endif
                @if($ticket->contract)
                <div>
                    <dt class="text-gray-500">Contrato</dt>
                    <dd class="font-medium text-gray-900">{{ $ticket->contract->plan->name }}</dd>
                </div>
                @endif
            </dl>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Conversa</h3>
        </div>

        <div class="p-6 space-y-4 max-h-96 overflow-y-auto">
            @forelse($ticket->messages as $msg)
            <div class="flex {{ $msg->sender_type === 'admin' ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-xs lg:max-w-md px-4 py-3 rounded-lg text-sm
                    {{ $msg->sender_type === 'admin' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-800' }}">
                    <p class="text-xs font-medium mb-1 {{ $msg->sender_type === 'admin' ? 'text-blue-200' : 'text-gray-500' }}">
                        {{ $msg->sender_type === 'admin' ? 'Suporte' : $ticket->client->name }} &middot; {{ $msg->created_at->format('d/m/Y H:i') }}
                    </p>
                    <p class="whitespace-pre-wrap">{{ $msg->message }}</p>
                </div>
            </div>
            @empty
            <div class="text-center text-gray-400 py-4">Nenhuma mensagem.</div>
            @endforelse
        </div>

        @if(in_array($ticket->status, ['open', 'in_progress']))
        <div class="p-6 border-t border-gray-200">
            <form method="POST" action="{{ route('crm.tickets.reply', $ticket) }}" class="space-y-3">
                @csrf
                <div>
                    <textarea name="message" rows="3" required
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                              placeholder="Digite sua resposta..."></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">Enviar Resposta</button>
                </div>
            </form>
        </div>
        @endif
    </div>

    <div class="text-center">
        <a href="{{ route('crm.tickets.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Voltar para chamados</a>
    </div>
</div>
@endsection
