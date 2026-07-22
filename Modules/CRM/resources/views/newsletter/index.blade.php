@extends('core::layouts.master')

@section('title', 'Newsletter / Mala Direta')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Newsletter / Mala Direta</h2>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-4 mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-4">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('crm.newsletter.send') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        @csrf
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assunto *</label>
                <input type="text" name="subject" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" value="{{ old('subject') }}" placeholder="Assunto do email">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mensagem *</label>
                <textarea name="message" rows="8" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Escreva sua mensagem aqui...">{{ old('message') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Enviar para:</label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                    <label class="flex items-center gap-2 p-2 border border-gray-200 rounded-lg hover:bg-gray-50">
                        <input type="radio" name="recipient_type" value="all" checked class="text-blue-600"> Todos os clientes
                    </label>
                    <label class="flex items-center gap-2 p-2 border border-gray-200 rounded-lg hover:bg-gray-50">
                        <input type="radio" name="recipient_type" value="active" class="text-blue-600"> Ativos
                    </label>
                    <label class="flex items-center gap-2 p-2 border border-gray-200 rounded-lg hover:bg-gray-50">
                        <input type="radio" name="recipient_type" value="overdue" class="text-blue-600"> Com fatura atrasada
                    </label>
                </div>
            </div>
        </div>
        <div class="mt-6 flex justify-end">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700" onclick="return confirm('Enviar email para os clientes selecionados?')">
                Enviar Newsletter
            </button>
        </div>
    </form>
</div>
@endsection
