@extends('infra::layouts.master')

@section('title', 'Script Gerado - MikroTik')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Script Gerado</h2>
        <div class="flex gap-3">
            <a href="{{ route('infra.mikrotik.scripts') }}" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                Gerar Novo Script
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-4 mb-4">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-gray-900">{{ $server->name }} ({{ $server->ip }})</h3>
                <p class="text-xs text-gray-500">Tipo: {{ strtoupper($validated['script_type'] ?? 'complete') }} | Gerado: {{ now()->format('d/m/Y H:i') }}</p>
            </div>
            <button onclick="copyScript()" id="copyBtn" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                Copiar Script
            </button>
        </div>
        <div class="p-4 bg-gray-900 overflow-x-auto">
            <pre id="scriptCode" class="text-green-400 text-xs font-mono leading-relaxed whitespace-pre-wrap">{{ $script }}</pre>
        </div>
    </div>

    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6">
        <h3 class="font-semibold text-yellow-800 mb-3">Como usar o script:</h3>
        <ol class="list-decimal list-inside text-sm text-yellow-700 space-y-2">
            <li>Baixe e instale o <strong>WinBox</strong> em <a href="https://mikrotik.com/download" target="_blank" class="underline">mikrotik.com/download</a></li>
            <li>Conecte-se na RB via WinBox (IP padrao: <code class="bg-yellow-100 px-1 rounded">192.168.88.1</code>)</li>
            <li>Va em <strong>New Terminal</strong> no menu lateral</li>
            <li>Cole o script copiado e pressione <strong>Enter</strong></li>
            <li>Aguarde a execucao de todos os comandos</li>
            <li>Verifique se nao houve mensagens de erro</li>
            <li>No MyISP, acesse <strong>Servidores MikroTik</strong> e teste a conexao</li>
        </ol>
        <div class="mt-4 p-3 bg-yellow-100 rounded-lg text-xs text-yellow-700">
            <strong>Nota:</strong> O script configura: identidade, senha, IP LAN, DNS, rota, IP pool, servico PPPoE/Hotspot, NAT (masquerade), firewall, address-list para bloqueio de clientes inadimplentes, e queue simples para controle de banda.
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyScript() {
    const code = document.getElementById('scriptCode').textContent;
    navigator.clipboard.writeText(code).then(() => {
        const btn = document.getElementById('copyBtn');
        btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Copiado!';
        btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
        btn.classList.add('bg-green-600');
        setTimeout(() => {
            btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg> Copiar Script';
            btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
            btn.classList.remove('bg-green-600');
        }, 2000);
    });
}
</script>
@endpush
@endsection
