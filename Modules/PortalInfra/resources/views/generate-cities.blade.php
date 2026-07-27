@extends('infra::layouts.master')

@section('title', 'Gerar Rede FTTH')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Gerar Rede FTTH</h2>
            <p class="text-sm text-gray-500 mt-1">Selecione o estado e a cidade para gerar CTOs e Caixas de Emenda automaticamente.</p>
        </div>

        <div class="p-6">
            <form method="POST" action="{{ route('infra.ftth.generate.city.run') }}" id="genForm">
                @csrf
                <input type="hidden" name="city_name" id="cityInput" value="{{ old('city_name') }}">

                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select name="state" id="stateSelect" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecione o estado</option>
                            <option value="AC" {{ old('state') == 'AC' ? 'selected' : '' }}>AC - Acre</option>
                            <option value="AL" {{ old('state') == 'AL' ? 'selected' : '' }}>AL - Alagoas</option>
                            <option value="AP" {{ old('state') == 'AP' ? 'selected' : '' }}>AP - Amapa</option>
                            <option value="AM" {{ old('state') == 'AM' ? 'selected' : '' }}>AM - Amazonas</option>
                            <option value="BA" {{ old('state') == 'BA' ? 'selected' : '' }}>BA - Bahia</option>
                            <option value="CE" {{ old('state') == 'CE' ? 'selected' : '' }}>CE - Ceara</option>
                            <option value="DF" {{ old('state') == 'DF' ? 'selected' : '' }}>DF - Distrito Federal</option>
                            <option value="ES" {{ old('state') == 'ES' ? 'selected' : '' }}>ES - Espirito Santo</option>
                            <option value="GO" {{ old('state') == 'GO' ? 'selected' : '' }}>GO - Goias</option>
                            <option value="MA" {{ old('state', 'MA') == 'MA' ? 'selected' : '' }}>MA - Maranhao</option>
                            <option value="MT" {{ old('state') == 'MT' ? 'selected' : '' }}>MT - Mato Grosso</option>
                            <option value="MS" {{ old('state') == 'MS' ? 'selected' : '' }}>MS - Mato Grosso do Sul</option>
                            <option value="MG" {{ old('state') == 'MG' ? 'selected' : '' }}>MG - Minas Gerais</option>
                            <option value="PA" {{ old('state') == 'PA' ? 'selected' : '' }}>PA - Para</option>
                            <option value="PB" {{ old('state') == 'PB' ? 'selected' : '' }}>PB - Paraiba</option>
                            <option value="PR" {{ old('state') == 'PR' ? 'selected' : '' }}>PR - Parana</option>
                            <option value="PE" {{ old('state') == 'PE' ? 'selected' : '' }}>PE - Pernambuco</option>
                            <option value="PI" {{ old('state') == 'PI' ? 'selected' : '' }}>PI - Piaui</option>
                            <option value="RJ" {{ old('state') == 'RJ' ? 'selected' : '' }}>RJ - Rio de Janeiro</option>
                            <option value="RN" {{ old('state') == 'RN' ? 'selected' : '' }}>RN - Rio Grande do Norte</option>
                            <option value="RS" {{ old('state') == 'RS' ? 'selected' : '' }}>RS - Rio Grande do Sul</option>
                            <option value="RO" {{ old('state') == 'RO' ? 'selected' : '' }}>RO - Rondonia</option>
                            <option value="RR" {{ old('state') == 'RR' ? 'selected' : '' }}>RR - Roraima</option>
                            <option value="SC" {{ old('state') == 'SC' ? 'selected' : '' }}>SC - Santa Catarina</option>
                            <option value="SP" {{ old('state') == 'SP' ? 'selected' : '' }}>SP - Sao Paulo</option>
                            <option value="SE" {{ old('state') == 'SE' ? 'selected' : '' }}>SE - Sergipe</option>
                            <option value="TO" {{ old('state') == 'TO' ? 'selected' : '' }}>TO - Tocantins</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
                        <select name="city_name_display" id="citySelect" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" disabled>
                            <option value="">Selecione o estado primeiro</option>
                        </select>
                    </div>

                    <div class="relative">
                        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
                        <div class="relative flex justify-center text-sm"><span class="px-2 bg-white text-gray-400">ou busque pelo CEP</span></div>
                    </div>

                    <div>
                        <div class="flex gap-2">
                            <input type="text" name="cep" id="cepInput" placeholder="00000-000" maxlength="9"
                                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <button type="button" id="cepBtn" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">Buscar</button>
                        </div>
                        <p class="text-xs text-gray-400 mt-1" id="cepResult"></p>
                    </div>
                </div>

                @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                    <ul class="list-disc list-inside text-sm text-red-600">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-yellow-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        <div>
                            <p class="text-sm font-medium text-yellow-800">Atencao</p>
                            <p class="text-xs text-yellow-700 mt-1">A busca usa o OpenStreetMap. A cidade deve ter ruas mapeadas. CTOs criadas a cada 250m, Caixas de Emenda a cada 4 CTOs.</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('infra.dashboard') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-500">Cancelar</a>
                    <button type="submit" id="submitBtn" class="px-6 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Gerar Rede
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
const stateSelect = document.getElementById('stateSelect');
const citySelect = document.getElementById('citySelect');
const cityInput = document.getElementById('cityInput');
const cepInput = document.getElementById('cepInput');
const cepBtn = document.getElementById('cepBtn');
const cepResult = document.getElementById('cepResult');

let citiesCache = {};

stateSelect.addEventListener('change', function() {
    const state = this.value;
    if (!state) {
        citySelect.innerHTML = '<option value="">Selecione o estado primeiro</option>';
        citySelect.disabled = true;
        return;
    }

    if (citiesCache[state]) {
        populateCities(citiesCache[state]);
        return;
    }

    citySelect.innerHTML = '<option value="">Carregando cidades...</option>';
    citySelect.disabled = true;

    fetch('https://servicodados.ibge.gov.br/api/v1/localidades/estados/' + state + '/municipios')
        .then(r => r.json())
        .then(data => {
            const cities = data.map(c => c.nome).sort((a, b) => a.localeCompare(b, 'pt-BR'));
            citiesCache[state] = cities;
            populateCities(cities);
        })
        .catch(() => {
            citySelect.innerHTML = '<option value="">Erro ao carregar cidades</option>';
        });
});

function populateCities(cities) {
    citySelect.innerHTML = '<option value="">Selecione a cidade</option>';
    cities.forEach(name => {
        const opt = document.createElement('option');
        opt.value = name;
        opt.textContent = name;
        citySelect.appendChild(opt);
    });
    citySelect.disabled = false;

    const saved = cityInput.value;
    if (saved && cities.includes(saved)) {
        citySelect.value = saved;
    }
}

citySelect.addEventListener('change', function() {
    cityInput.value = this.value;
});

cepBtn.addEventListener('click', function() {
    const cep = cepInput.value.replace(/\D/g, '');
    if (cep.length !== 8) {
        cepResult.textContent = 'CEP invalido.';
        return;
    }
    cepResult.textContent = 'Buscando...';
    cepBtn.disabled = true;

    fetch('https://viacep.com.br/ws/' + cep + '/json/')
        .then(r => r.json())
        .then(data => {
            if (data.erro) {
                cepResult.textContent = 'CEP nao encontrado.';
                return;
            }
            cepResult.textContent = data.logradouro + ', ' + data.bairro + ' - ' + data.localidade + '/' + data.uf;

            if (stateSelect.value !== data.uf) {
                stateSelect.value = data.uf;
                stateSelect.dispatchEvent(new Event('change'));

                const waitForCity = setInterval(() => {
                    if (!citySelect.disabled && citySelect.querySelector('option[value="' + data.localidade + '"]')) {
                        clearInterval(waitForCity);
                        citySelect.value = data.localidade;
                        cityInput.value = data.localidade;
                    }
                }, 100);

                setTimeout(() => clearInterval(waitForCity), 10000);
            } else {
                citySelect.value = data.localidade;
                cityInput.value = data.localidade;
            }
        })
        .catch(() => {
            cepResult.textContent = 'Erro ao buscar CEP.';
        })
        .finally(() => {
            cepBtn.disabled = false;
        });
});

cepInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        cepBtn.click();
    }
});

document.getElementById('genForm').addEventListener('submit', function(e) {
    var state = stateSelect.value;
    var city = citySelect.value || cityInput.value;
    if (!state) {
        e.preventDefault();
        alert('Selecione o estado.');
        return;
    }
    if (!city) {
        e.preventDefault();
        alert('Selecione a cidade.');
        return;
    }
    cityInput.value = city;
    var btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Buscando ruas na Overpass API...';
});

if (stateSelect.value) {
    stateSelect.dispatchEvent(new Event('change'));
}
</script>
@endpush
@endsection
