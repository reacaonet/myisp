@extends('core::layouts.master')

@section('title', 'Servidores MikroTik')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-800">Servidores MikroTik</h2>
        <a href="{{ route('crm.mikrotik-servers.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Novo Servidor</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 bg-gray-50 border-b border-gray-200">
                    <th class="px-6 py-4 font-medium">Nome</th>
                    <th class="px-6 py-4 font-medium">IP</th>
                    <th class="px-6 py-4 font-medium">Porta</th>
                    <th class="px-6 py-4 font-medium">Tipo</th>
                    <th class="px-6 py-4 font-medium">Ativo</th>
                    <th class="px-6 py-4 font-medium text-right">Acoes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($servers as $s)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $s->name }}</td>
                    <td class="px-6 py-4 text-gray-600 font-mono">{{ $s->ip }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $s->port }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ ucfirst($s->type) }}</td>
                    <td class="px-6 py-4">
                        @if($s->is_active)
                            <span class="text-green-600 font-medium">Sim</span>
                        @else
                            <span class="text-red-600 font-medium">Nao</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <form method="POST" action="{{ route('crm.mikrotik-servers.test', $s) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-green-600 hover:text-green-800 font-medium text-sm">Testar</button>
                        </form>
                        <span class="text-gray-300 mx-1">|</span>
                        <a href="{{ route('crm.mikrotik-servers.edit', $s) }}" class="text-blue-600 hover:text-blue-800 font-medium text-sm">Editar</a>
                        <span class="text-gray-300 mx-1">|</span>
                        <form method="POST" action="{{ route('crm.mikrotik-servers.destroy', $s) }}" onsubmit="return confirm('Remover servidor {{ $s->name }}?')" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 font-medium text-sm">Excluir</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">Nenhum servidor MikroTik cadastrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($servers->hasPages())
    <div class="p-6 border-t border-gray-200">{{ $servers->links() }}</div>
    @endif
</div>
@endsection
