@extends('core::layouts.master')

@section('title', 'Gateways de Pagamento')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Gateways de Pagamento</h2>
        <a href="{{ route('billing.gateways.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">+ Novo Gateway</a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-4 mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-4">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($gateways as $gateway)
        <div class="bg-white rounded-xl shadow-sm border {{ $gateway->status === 'active' ? 'border-green-200' : 'border-gray-200' }} p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">{{ $gateway->name }}</h3>
                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $gateway->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ $gateway->status === 'active' ? 'Ativo' : 'Inativo' }}
                </span>
            </div>

            <p class="text-sm text-gray-500 mb-4 font-mono">{{ $gateway->slug }}</p>

            <div class="flex flex-wrap gap-2 mb-4">
                @if($gateway->supports_boleto)
                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700">Boleto</span>
                @endif
                @if($gateway->supports_pix)
                    <span class="px-2 py-1 text-xs rounded-full bg-emerald-100 text-emerald-700">PIX</span>
                @endif
                @if($gateway->supports_credit_card)
                    <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-700">Cartao</span>
                @endif
                @if($gateway->supports_recurrence)
                    <span class="px-2 py-1 text-xs rounded-full bg-orange-100 text-orange-700">Recorrencia</span>
                @endif
            </div>

            @if($gateway->notes)
                <p class="text-sm text-gray-500 mb-4">{{ $gateway->notes }}</p>
            @endif

            <div class="flex items-center gap-0.5 pt-4 border-t border-gray-100">
                <a href="{{ route('billing.gateways.edit', $gateway) }}" title="Editar" class="p-1.5 rounded hover:bg-blue-50 text-blue-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                <button onclick="testGateway({{ $gateway->id }})" title="Testar" class="p-1.5 rounded hover:bg-green-50 text-green-600" id="test-btn-{{ $gateway->id }}"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></button>
                <span id="test-result-{{ $gateway->id }}"></span>
                <form method="POST" action="{{ route('billing.gateways.destroy', $gateway) }}" class="ml-auto" onsubmit="return confirm('Excluir este gateway?')">
                    @csrf @method('DELETE')
                    <button type="submit" title="Excluir" class="p-1.5 rounded hover:bg-red-50 text-red-600" {{ $gateway->invoices()->count() > 0 ? 'disabled title="Possui faturas vinculadas"' : '' }}><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-3 bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center text-gray-400">
            Nenhum gateway de pagamento configurado. Adicione um para comecar a gerar cobrancas online.
        </div>
        @endforelse
    </div>
</div>

<script>
function testGateway(id) {
    const btn = document.getElementById('test-btn-' + id);
    const result = document.getElementById('test-result-' + id);
    btn.textContent = 'Testando...';
    result.innerHTML = '';
    fetch('/billing/gateways/' + id + '/test')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                result.innerHTML = '<span class="text-green-600 font-medium">' + data.message + '</span>';
            } else {
                result.innerHTML = '<span class="text-red-600">' + data.message + '</span>';
            }
            btn.textContent = 'Testar';
        })
        .catch(() => {
            result.innerHTML = '<span class="text-red-600">Erro de conexao</span>';
            btn.textContent = 'Testar';
        });
}
</script>
@endsection
