@extends('infra::layouts.master')

@section('title', 'Detalhes do Backup')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Backup: {{ $backup->filename }}</h2>
        <div class="flex items-center gap-1">
            <a href="{{ route('infra.mikrotik-backups.download', $backup) }}" title="Baixar" class="p-2 rounded-lg bg-green-600 text-white hover:bg-green-700"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg></a>
            <a href="{{ route('infra.mikrotik-backups.index') }}" title="Voltar" class="p-2 rounded-lg text-gray-600 border border-gray-300 hover:bg-gray-50"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg></a>
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
