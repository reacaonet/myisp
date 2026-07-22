@extends('core::layouts.master')

@section('title', 'Backups MikroTik')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Backups MikroTik</h2>
        <a href="{{ route('crm.mikrotik-backups.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">+ Novo Backup</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Servidor</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Arquivo</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Tipo</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Tamanho</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Criado em</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-500">Acoes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($backups as $backup)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium">{{ $backup->server->name ?? 'N/A' }}</td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $backup->filename }}</td>
                    <td class="px-4 py-3"><span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-700">{{ $backup->type }}</span></td>
                    <td class="px-4 py-3 text-gray-500">{{ number_format($backup->file_size / 1024, 1) }} KB</td>
                    <td class="px-4 py-3 text-gray-500">{{ $backup->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3 text-center space-x-2">
                        <a href="{{ route('crm.mikrotik-backups.show', $backup) }}" class="text-blue-600 hover:underline text-xs">Ver</a>
                        <a href="{{ route('crm.mikrotik-backups.download', $backup) }}" class="text-green-600 hover:underline text-xs">Baixar</a>
                        <form method="POST" action="{{ route('crm.mikrotik-backups.destroy', $backup) }}" class="inline" onsubmit="return confirm('Excluir este backup?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline text-xs">Excluir</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Nenhum backup encontrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
