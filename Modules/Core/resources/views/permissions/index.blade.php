@extends('core::layouts.master')

@section('title', 'Permissoes de Usuarios')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Permissoes de Usuarios</h2>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Usuario</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Email</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-500">Permissoes</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-500">Acoes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $user->email }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700">
                            {{ $user->permissions->count() }} permissoes
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <a href="{{ route('core.permissions.edit', $user) }}" class="text-blue-600 hover:text-blue-800 text-xs">Editar Permissoes</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
