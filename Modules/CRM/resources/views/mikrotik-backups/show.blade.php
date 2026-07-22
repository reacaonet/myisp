@extends('core::layouts.master')

@section('title', 'Detalhes do Backup')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Backup: {{ $backup->filename }}</h2>
        <div class="flex gap-3">
            <a href="{{ route('crm.mikrotik-backups.download', $backup) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">Baixar</a>
            <a href="{{ route('crm.mikrotik-backups.index') }}" class="text-sm text-blue-600 hover:underline">Voltar</a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div><span class="text-gray-500">Servidor:</span><p class="font-medium">{{ $backup->server->name ?? 'N/A' }}</p></div>
            <div><span class="text-gray-500">Tipo:</span><p class="font-medium">{{ $backup->type }}</p></div>
            <div><span class="text-gray-500">Tamanho:</span><p class="font-medium">{{ number_format($backup->file_size / 1024, 1) }} KB</p></div>
            <div><span class="text-gray-500">Criado em:</span><p class="font-medium">{{ $backup->created_at->format('d/m/Y H:i') }}</p></div>
        </div>
    </div>

    <div class="bg-gray-900 rounded-xl shadow-sm border border-gray-700 p-4">
        <h3 class="text-sm font-medium text-gray-300 mb-3">Conteudo do Backup</h3>
        <pre class="text-green-400 text-xs font-mono overflow-auto max-h-96 whitespace-pre-wrap">{{ $backup->content }}</pre>
    </div>
</div>
@endsection
