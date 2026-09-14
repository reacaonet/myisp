@extends('crm::technician.layouts.master')

@section('title', 'Rede FTTH')

@section('content')
<div class="mb-6 flex items-center justify-between flex-wrap gap-3">
    <div>
        <h2 class="text-xl font-bold text-gray-900">Rede FTTH</h2>
        <p class="text-sm text-gray-500 mt-1">Dados de projeto das CTOs e Caixas de Emenda. Clique para ver localizacao e plano de fusao.</p>
    </div>
    <form method="GET" action="{{ route('technician.portal.ftth') }}" class="flex items-center gap-2">
        <select name="project" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
            <option value="">Todos os Projetos</option>
            @foreach($projects as $project)
                <option value="{{ $project->id }}" @selected(request('project') == $project->id)>{{ $project->name }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm font-medium text-gray-500">Total CTOs</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total_ctos'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm font-medium text-gray-500">CTOs Ativas</p>
        <p class="text-3xl font-bold text-blue-600 mt-1">{{ $stats['active_ctos'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm font-medium text-gray-500">Caixas de Emenda</p>
        <p class="text-3xl font-bold text-purple-600 mt-1">{{ $stats['total_caixas'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm font-medium text-gray-500">Fusoes no Plano</p>
        <p class="text-3xl font-bold text-amber-600 mt-1">{{ $stats['total_pending_fusions'] }}</p>
    </div>
</div>

@if($ctos->isNotEmpty())
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">CTOs</h3>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-left text-sm text-gray-500 border-b border-gray-200">
                    <th class="pb-3 font-medium">Codigo</th>
                    <th class="pb-3 font-medium">Projeto</th>
                    <th class="pb-3 font-medium">Rua</th>
                    <th class="pb-3 font-medium">Portas</th>
                    <th class="pb-3 font-medium">Porta OLT</th>
                    <th class="pb-3 font-medium">Fusoes</th>
                    <th class="pb-3 font-medium">Status</th>
                    <th class="pb-3 font-medium text-right">Acao</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($ctos as $cto)
                <tr class="hover:bg-gray-50">
                    <td class="py-3 text-sm font-mono font-medium text-gray-900">{{ $cto->code }}</td>
                    <td class="py-3 text-sm text-gray-600">{{ $cto->ftthProject?->name ?: '-' }}</td>
                    <td class="py-3 text-sm text-gray-600">{{ $cto->street ?: ($cto->full_address ?: '-') }}</td>
                    <td class="py-3 text-sm text-gray-600">{{ $cto->used_ports }}/{{ $cto->capacity }}</td>
                    <td class="py-3 text-sm font-mono text-gray-600">{{ $cto->olt_port ?: '-' }}</td>
                    <td class="py-3 text-sm text-gray-600">{{ $cto->fusions_count }}</td>
                    <td class="py-3 text-sm">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $cto->status === 'active' ? 'bg-green-100 text-green-700' : ($cto->status === 'maintenance' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700') }}">
                            {{ $cto->status === 'active' ? 'Ativa' : ($cto->status === 'maintenance' ? 'Manutencao' : ucfirst($cto->status)) }}
                        </span>
                    </td>
                    <td class="py-3 text-sm text-right">
                        <a href="{{ route('technician.portal.ftth.ctos.show', $cto) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-semibold hover:bg-blue-700">
                            Ver / Ativar
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if($caixas->isNotEmpty())
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Caixas de Emenda</h3>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-left text-sm text-gray-500 border-b border-gray-200">
                    <th class="pb-3 font-medium">Codigo</th>
                    <th class="pb-3 font-medium">Projeto</th>
                    <th class="pb-3 font-medium">Rua</th>
                    <th class="pb-3 font-medium">CTOs</th>
                    <th class="pb-3 font-medium">Porta OLT</th>
                    <th class="pb-3 font-medium">Fusoes</th>
                    <th class="pb-3 font-medium">Status</th>
                    <th class="pb-3 font-medium text-right">Acao</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($caixas as $caixa)
                <tr class="hover:bg-gray-50">
                    <td class="py-3 text-sm font-mono font-medium text-gray-900">{{ $caixa->code }}</td>
                    <td class="py-3 text-sm text-gray-600">{{ $caixa->ftthProject?->name ?: '-' }}</td>
                    <td class="py-3 text-sm text-gray-600">{{ $caixa->street ?: '-' }}</td>
                    <td class="py-3 text-sm text-gray-600">{{ $caixa->ctos_count }}</td>
                    <td class="py-3 text-sm font-mono text-gray-600">{{ $caixa->olt_port ?: '-' }}</td>
                    <td class="py-3 text-sm text-gray-600">{{ $caixa->fusions_count }}</td>
                    <td class="py-3 text-sm">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $caixa->status === 'active' ? 'bg-green-100 text-green-700' : ($caixa->status === 'maintenance' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700') }}">
                            {{ $caixa->status === 'active' ? 'Ativa' : ($caixa->status === 'maintenance' ? 'Manutencao' : ucfirst($caixa->status)) }}
                        </span>
                    </td>
                    <td class="py-3 text-sm text-right">
                        <a href="{{ route('technician.portal.ftth.caixas.show', $caixa) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-semibold hover:bg-blue-700">
                            Ver / Ativar
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if($ctos->isEmpty() && $caixas->isEmpty())
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
    <p class="text-gray-500">Nenhuma CTO ou Caixa de Emenda encontrada.</p>
</div>
@endif
@endsection