@extends('core::layouts.master')

@section('title', 'Editar Compania')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('core.companies.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Voltar</a>
        <span class="text-sm text-gray-400">Slug: {{ $company->slug }}</span>
    </div>

    <form method="POST" action="{{ route('core.companies.update', $company) }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        @csrf @method('PUT')

        <h2 class="text-xl font-bold text-gray-900 mb-6">Editar Compania</h2>

        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                <input type="text" name="name" value="{{ old('name', $company->name) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $company->slug) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dominio publico</label>
                <input type="text" name="domain" value="{{ old('domain', $company->domain) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="franquia.exemplo.com.br">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Codigo</label>
                <input type="text" name="code" value="{{ old('code', $company->code) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Franquia de (matriz / pai)</label>
                <select name="parent_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" @disabled($company->isRoot())>
                    <option value="">Nenhuma (raiz/franqueadora)</option>
                    @foreach($parents as $p)
                        <option value="{{ $p->id }}" {{ old('parent_id', $company->parent_id) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
                @if($company->isRoot())
                    <p class="mt-1 text-xs text-gray-400">A franqueadora e a raiz e nao possui pai.</p>
                @endif
            </div>
            <div class="flex items-end gap-4 pb-1">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_franchise" value="1" {{ old('is_franchise', $company->is_franchise) ? 'checked' : '' }} class="rounded border-gray-300">
                    E uma franquia
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $company->is_active) ? 'checked' : '' }} class="rounded border-gray-300">
                    Ativa
                </label>
            </div>

            <div class="md:col-span-2 border-t border-gray-200 pt-4 mt-2">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Dados Fiscais (opcional - herda da matriz/franqueadora se vazio)</h3>
                @if($company->parent)
                    <p class="text-xs text-gray-400 mb-2">Valores herdados (nao preenchidos nesta compania): CNPJ {{ $company->parent->fiscal('document') ?: '—' }}, Telefone {{ $company->parent->fiscal('phone') ?: '—' }}, Cidade {{ $company->parent->fiscal('city') ?: '—' }}</p>
                @endif
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Razao Social</label>
                <input type="text" name="legal_name" value="{{ old('legal_name', $company->legal_name) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="{{ $company->parent?->legal_name ?? '00.000.000/0001-00 LTDA' }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome Fantasia</label>
                <input type="text" name="fantasy_name" value="{{ old('fantasy_name', $company->fantasy_name) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="{{ $company->parent?->fantasy_name ?? 'Nome usado na marca' }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">CNPJ</label>
                <input type="text" name="document" value="{{ old('document', $company->document) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Inscricao Estadual</label>
                <input type="text" name="state_registration" value="{{ old('state_registration', $company->state_registration) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Inscricao Municipal</label>
                <input type="text" name="municipal_registration" value="{{ old('municipal_registration', $company->municipal_registration) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                <input type="text" name="phone" value="{{ old('phone', $company->phone) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Celular</label>
                <input type="text" name="cellphone" value="{{ old('cellphone', $company->cellphone) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $company->email) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Site</label>
                <input type="text" name="website" value="{{ old('website', $company->website) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Endereco</label>
                <input type="text" name="address" value="{{ old('address', $company->address) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
                <input type="text" name="city" value="{{ old('city', $company->city) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">UF</label>
                    <input type="text" name="state" value="{{ old('state', $company->state) }}" maxlength="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CEP</label>
                    <input type="text" name="zip" value="{{ old('zip', $company->zip) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>
        </div>

        <fieldset class="mt-6 border border-gray-200 rounded-xl p-4">
            <legend class="px-2 text-sm font-medium text-gray-700">Assinatura</legend>
            @if ($company->isRoot())
                <p class="text-xs text-gray-500">A matriz opera sem assinatura e sem vencimento.</p>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Plano</label>
                        <input type="text" name="plan_slug" value="{{ old('plan_slug', $company->plan_slug) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Situacao</label>
                        <select name="subscription_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            @foreach(['trial' => 'Em teste', 'active' => 'Ativa', 'overdue' => 'Em atraso', 'canceled' => 'Cancelada'] as $value => $label)
                                <option value="{{ $value }}" {{ old('subscription_status', $company->subscription_status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fim do teste</label>
                        <input type="date" name="trial_ends_at" value="{{ old('trial_ends_at', $company->trial_ends_at?->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vencimento da assinatura</label>
                        <input type="date" name="subscription_ends_at" value="{{ old('subscription_ends_at', $company->subscription_ends_at?->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Observacoes</label>
                        <textarea name="subscription_notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ old('subscription_notes', $company->subscription_notes) }}</textarea>
                    </div>
                </div>
            @endif
        </fieldset>

        <div class="flex justify-end mt-6">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Salvar Compania</button>
        </div>
    </form>
</div>
@endsection