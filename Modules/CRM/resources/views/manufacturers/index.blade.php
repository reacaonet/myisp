@extends('core::layouts.master')

@section('title', 'Fabricantes')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-800">Fabricantes</h2>
        <a href="{{ route('crm.manufacturers.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Novo Fabricante</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 bg-gray-50 border-b border-gray-200">
                    <th class="px-6 py-4 font-medium">Nome</th>
                    <th class="px-6 py-4 font-medium">Website</th>
                    <th class="px-6 py-4 font-medium">Telefone</th>
                    <th class="px-6 py-4 font-medium">Email</th>
                    <th class="px-6 py-4 font-medium">Equipamentos</th>
                    <th class="px-6 py-4 font-medium text-right">Acoes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($manufacturers as $m)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $m->name }}</td>
                    <td class="px-6 py-4 text-gray-600">
                        @if($m->website)
                            <a href="{{ $m->website }}" target="_blank" class="text-blue-600 hover:underline">{{ $m->website }}</a>
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ $m->phone ?? '-' }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $m->email ?? '-' }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $m->equipment_count }}</td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('crm.manufacturers.edit', $m) }}" class="text-blue-600 hover:text-blue-800 font-medium text-sm">Editar</a>
                        <span class="text-gray-300 mx-2">|</span>
                        <form method="POST" action="{{ route('crm.manufacturers.destroy', $m) }}" onsubmit="return confirm('Remover fabricante {{ $m->name }}?')" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 font-medium text-sm">Excluir</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">Nenhum fabricante cadastrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($manufacturers->hasPages())
    <div class="p-6 border-t border-gray-200">{{ $manufacturers->links() }}</div>
    @endif
</div>
@endsection
