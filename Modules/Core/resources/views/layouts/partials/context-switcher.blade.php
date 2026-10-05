@php
    use Modules\Core\Services\TenantContext;

    $ctxUser = auth()->user();
    $ctxSuper = $ctxUser && TenantContext::isSuperadmin($ctxUser);

    // Empresas que o usuario pode operar. O dono do negocio (superadmin) ve a
    // rede inteira; o franqueado/investidor ve apenas as empresas vinculadas
    // a ele em company_user.
    $ctxCompanies = $ctxSuper
        ? Modules\Core\Models\Company::orderBy('name')->get()
        : $ctxUser?->companies()->orderBy('name')->get() ?? collect();

    $ctxActiveCompany = TenantContext::companyId();

    // Filiais dentro da empresa ativa: as liberadas para o usuario. Trocar de
    // empresa invalida a filial, e o TenantContext cai na primeira liberada.
    $ctxBranches = collect();

    if ($ctxActiveCompany) {
        $ctxBranches = $ctxSuper
            ? Modules\Core\Models\Branch::where('company_id', $ctxActiveCompany)->orderBy('name')->get()
            : $ctxUser?->branches()
                ->where('branches.company_id', $ctxActiveCompany)
                ->orderBy('name')
                ->get() ?? collect();
    }

    $ctxActiveBranch = TenantContext::branchId();
    $ctxMostraSeletor = $ctxCompanies->count() > 1;
@endphp

@if ($ctxMostraSeletor)
    <form method="POST" action="{{ route('core.context.switch') }}" class="flex items-center gap-2">
        @csrf

        <select name="company_id" onchange="this.form.submit()"
            class="rounded-lg border-gray-300 border px-2 py-1 text-sm focus:ring-blue-500 focus:border-blue-500"
            title="Empresa em operacao">
            @foreach ($ctxCompanies as $empresa)
                <option value="{{ $empresa->id }}" {{ (int) $ctxActiveCompany === $empresa->id ? 'selected' : '' }}>
                    {{ $empresa->name }}
                </option>
            @endforeach
        </select>

        @if ($ctxBranches->count() > 1)
            <select name="branch_id" onchange="this.form.submit()"
                class="rounded-lg border-gray-300 border px-2 py-1 text-sm focus:ring-blue-500 focus:border-blue-500"
                title="Filial em operacao">
                @foreach ($ctxBranches as $filial)
                    <option value="{{ $filial->id }}" {{ (int) $ctxActiveBranch === $filial->id ? 'selected' : '' }}>
                        {{ $filial->name }}
                    </option>
                @endforeach
            </select>
        @elseif ($ctxActiveBranch)
            {{-- Uma filial so: continua sends no POST para nao perder o contexto. --}}
            <input type="hidden" name="branch_id" value="{{ $ctxActiveBranch }}">
        @endif

        <noscript>
            <button type="submit" class="px-2 py-1 text-xs border border-gray-300 rounded-lg">Trocar</button>
        </noscript>
    </form>
@endif
