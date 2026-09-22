@extends('infra::layouts.master')

@section('title', 'OLTs')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-800">OLTs</h2>
        <a href="{{ route('infra.olts.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Nova OLT</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 bg-gray-50 border-b border-gray-200">
                    <th class="px-6 py-4 font-medium">Nome</th>
                    <th class="px-6 py-4 font-medium">Marca/Modelo</th>
                    <th class="px-6 py-4 font-medium">IP</th>
                    <th class="px-6 py-4 font-medium">Tipo</th>
                    <th class="px-6 py-4 font-medium">Portas PON</th>
                    <th class="px-6 py-4 font-medium">Ativo</th>
                    <th class="px-6 py-4 font-medium text-right">Acoes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($olts as $olt)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $olt->name }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $olt->brand }} {{ $olt->model }}</td>
                    <td class="px-6 py-4 text-gray-600 font-mono">{{ $olt->ip }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ strtoupper($olt->type) }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $olt->used_pon_ports }}/{{ $olt->pon_ports }}</td>
                    <td class="px-6 py-4">
                        @if($olt->is_active)
                            <span class="text-green-600 font-medium">Ativa</span>
                        @else
                            <span class="text-red-500 font-medium">Inativa</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right whitespace-nowrap">
                        <a href="{{ route('infra.olts.edit', $olt->id) }}" class="text-blue-600 hover:underline text-sm font-medium">Editar</a>
                        <form action="{{ route('infra.olts.destroy', $olt->id) }}" method="POST" class="inline" onsubmit="return confirm('Excluir esta OLT?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline text-sm font-medium ml-3">Excluir</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-10 text-center text-gray-500">Nenhuma OLT cadastrada.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($olts->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">{{ $olts->links() }}</div>
    @endif
</div>
@endsection