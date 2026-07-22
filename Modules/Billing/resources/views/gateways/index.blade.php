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

            <div class="flex items-center gap-3 text-sm pt-4 border-t border-gray-100">
                <a href="{{ route('billing.gateways.edit', $gateway) }}" class="text-blue-600 hover:underline">Editar</a>
                <button onclick="testGateway({{ $gateway->id }})" class="text-gray-500 hover:underline" id="test-btn-{{ $gateway->id }}">Testar</button>
                <span id="test-result-{{ $gateway->id }}"></span>
                <form method="POST" action="{{ route('billing.gateways.destroy', $gateway) }}" class="ml-auto" onsubmit="return confirm('Excluir este gateway?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-red-600 hover:underline" {{ $gateway->invoices()->count() > 0 ? 'disabled title="Possui faturas vinculadas"' : '' }}>Excluir</button>
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
    fetch('/billing/gateways/' + id + '/test')
        .then(r => r.json())
        .then(data => {
            if (data.config_valid) {
                result.innerHTML = '<span class="text-green-600">Config OK</span>';
            } else {
                result.innerHTML = '<span class="text-red-600">Config incompleta</span>';
            }
            btn.textContent = 'Testar';
        })
        .catch(() => {
            result.innerHTML = '<span class="text-red-600">Erro</span>';
            btn.textContent = 'Testar';
        });
}
</script>
@endsection
