@extends('core::layouts.master')

@section('title', 'Tecnicos')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-800">Tecnicos</h2>
        <a href="{{ route('crm.technicians.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Novo Tecnico</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 bg-gray-50 border-b border-gray-200">
                    <th class="px-6 py-4 font-medium">Nome</th>
                    <th class="px-6 py-4 font-medium">Login</th>
                    <th class="px-6 py-4 font-medium">Cargo</th>
                    <th class="px-6 py-4 font-medium">Celular</th>
                    <th class="px-6 py-4 font-medium">Ativo</th>
                    <th class="px-6 py-4 font-medium text-right">Acoes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($technicians as $t)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $t->name }}</td>
                    <td class="px-6 py-4 text-gray-600 font-mono text-sm">{{ $t->login ?? '-' }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $t->cargo ?? '-' }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $t->cellphone ?? $t->phone ?? '-' }}</td>
                    <td class="px-6 py-4">
                        @if($t->is_active)
                            <span class="text-green-600 font-medium">Sim</span>
                        @else
                            <span class="text-red-600 font-medium">Nao</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('crm.technicians.edit', $t) }}" class="text-blue-600 hover:text-blue-800 font-medium text-sm">Editar</a>
                        <span class="text-gray-300 mx-2">|</span>
                        <form method="POST" action="{{ route('crm.technicians.destroy', $t) }}" onsubmit="return confirm('Remover tecnico {{ $t->name }}?')" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 font-medium text-sm">Excluir</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-6 py-12 text-center text-gray-400">Nenhum tecnico cadastrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($technicians->hasPages())
    <div class="p-6 border-t border-gray-200">{{ $technicians->links() }}</div>
    @endif
</div>
@endsection
