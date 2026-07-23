@extends('core::layouts.master')

@section('title', 'Livro Caixa')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Livro Caixa</h2>
        <a href="{{ route('billing.cash-book.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">+ Novo Lancamento</a>
    </div>

    <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6 flex gap-4 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Data Inicio</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Data Fim</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Tipo</label>
            <select name="type" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Todos</option>
                <option value="entrada" {{ request('type') == 'entrada' ? 'selected' : '' }}>Entradas</option>
                <option value="saida" {{ request('type') == 'saida' ? 'selected' : '' }}>Saidas</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Categoria</label>
            <select name="category" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Todas</option>
                @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Filtrar</button>
    </form>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-gray-50 rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase">Saldo Anterior</p>
            <p class="text-lg font-bold text-gray-700">R$ {{ number_format($previousBalance, 2, ',', '.') }}</p>
        </div>
        <div class="bg-green-50 rounded-xl shadow-sm border border-green-200 p-4">
            <p class="text-xs text-green-600 uppercase font-medium">Entradas</p>
            <p class="text-lg font-bold text-green-700">R$ {{ number_format($totalEntradas, 2, ',', '.') }}</p>
        </div>
        <div class="bg-red-50 rounded-xl shadow-sm border border-red-200 p-4">
            <p class="text-xs text-red-600 uppercase font-medium">Saidas</p>
            <p class="text-lg font-bold text-red-700">R$ {{ number_format($totalSaidas, 2, ',', '.') }}</p>
        </div>
        <div class="bg-blue-50 rounded-xl shadow-sm border border-blue-200 p-4">
            <p class="text-xs text-blue-600 uppercase font-medium">Saldo Acumulado</p>
            <p class="text-lg font-bold {{ $saldoAcumulado >= 0 ? 'text-blue-700' : 'text-red-700' }}">
                R$ {{ number_format($saldoAcumulado, 2, ',', '.') }}
            </p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Data</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Tipo</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Descricao</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Categoria</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Pagamento</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-500">Valor</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-500">Acoes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($entries as $entry)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">{{ $entry->entry_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3">
                        @if($entry->type === 'entrada')
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Entrada</span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">Saida</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 font-medium">{{ $entry->description }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $entry->category ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $entry->payment_method ?? '-' }}</td>
                    <td class="px-4 py-3 text-right font-medium {{ $entry->type === 'entrada' ? 'text-green-700' : 'text-red-700' }}">
                        {{ $entry->type === 'entrada' ? '+' : '-' }} R$ {{ number_format($entry->amount, 2, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-0.5">
                            <a href="{{ route('billing.cash-book.edit', $entry) }}" title="Editar" class="p-1.5 rounded hover:bg-blue-50 text-blue-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                            <form method="POST" action="{{ route('billing.cash-book.destroy', $entry) }}" onsubmit="return confirm('Remover lancamento?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Excluir" class="p-1.5 rounded hover:bg-red-50 text-red-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Nenhum lancamento encontrado</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $entries->withQueryString()->links() }}
    </div>
</div>
@endsection
