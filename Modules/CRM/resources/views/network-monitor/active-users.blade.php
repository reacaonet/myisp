@extends('core::layouts.master')

@section('title', 'Usuarios Ativos - ' . $server->name)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('crm.network-monitor.show', $server) }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h2 class="text-lg font-semibold text-gray-800">Usuarios Ativos - {{ $server->name }}</h2>
        </div>
        <button onclick="location.reload()" class="px-4 py-2 bg-gray-600 text-white rounded-lg text-sm font-medium hover:bg-gray-700">Atualizar</button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">PPPoE ({{ is_array($users['pppoe'] ?? null) ? count($users['pppoe']) : 0 }})</h3>
            </div>
            <div class="overflow-x-auto max-h-96">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 bg-gray-50 border-b border-gray-200">
                            <th class="px-4 py-2 font-medium">Login</th>
                            <th class="px-4 py-2 font-medium">Servico</th>
                            <th class="px-4 py-2 font-medium">Caller ID</th>
                            <th class="px-4 py-2 font-medium">Uptime</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users['pppoe'] ?? [] as $user)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="px-4 py-2 font-mono text-xs font-medium text-gray-900">{{ $user['name'] ?? '-' }}</td>
                            <td class="px-4 py-2 text-gray-600">{{ $user['service'] ?? '-' }}</td>
                            <td class="px-4 py-2 text-gray-600 font-mono text-xs">{{ $user['caller-id'] ?? '-' }}</td>
                            <td class="px-4 py-2 text-gray-600 text-xs">{{ $user['uptime'] ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">Nenhum usuario PPPoE ativo</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Hotspot ({{ is_array($users['hotspot'] ?? null) ? count($users['hotspot']) : 0 }})</h3>
            </div>
            <div class="overflow-x-auto max-h-96">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 bg-gray-50 border-b border-gray-200">
                            <th class="px-4 py-2 font-medium">Login</th>
                            <th class="px-4 py-2 font-medium">IP</th>
                            <th class="px-4 py-2 font-medium">MAC</th>
                            <th class="px-4 py-2 font-medium">Uptime</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users['hotspot'] ?? [] as $user)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="px-4 py-2 font-mono text-xs font-medium text-gray-900">{{ $user['user'] ?? '-' }}</td>
                            <td class="px-4 py-2 text-gray-600 font-mono text-xs">{{ $user['address'] ?? '-' }}</td>
                            <td class="px-4 py-2 text-gray-600 font-mono text-xs">{{ $user['mac-address'] ?? '-' }}</td>
                            <td class="px-4 py-2 text-gray-600 text-xs">{{ $user['uptime'] ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">Nenhum usuario Hotspot ativo</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
setTimeout(() => location.reload(), 10000);
</script>
@endpush
@endsection
