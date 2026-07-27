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
                    <th class="px-6 py-4 font-medium">Email</th>
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
                    <td class="px-6 py-4 text-gray-600 font-mono text-sm">{{ $t->email ?? '-' }}</td>
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
                        <div class="flex items-center justify-end gap-0.5">
                            <a href="{{ route('crm.technicians.edit', $t) }}" title="Editar" class="p-1.5 rounded hover:bg-blue-50 text-blue-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                            <form method="POST" action="{{ route('crm.technicians.destroy', $t) }}" onsubmit="return confirm('Remover tecnico {{ $t->name }}?')" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" title="Excluir" class="p-1.5 rounded hover:bg-red-50 text-red-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">Nenhum tecnico cadastrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($technicians->hasPages())
    <div class="p-6 border-t border-gray-200">{{ $technicians->links() }}</div>
    @endif
</div>
@endsection
