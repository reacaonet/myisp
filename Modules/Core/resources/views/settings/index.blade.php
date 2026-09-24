@extends('core::layouts.master')

@section('title', 'Configuracoes do Sistema')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Configuracoes do Sistema</h2>
        <a href="{{ route('core.settings.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">+ Nova Configuracao</a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-4 mb-4">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('core.settings.update') }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        @php
            $labels = [
                'company_name' => ['label' => 'Razao Social', 'placeholder' => 'Razao social da empresa'],
                'company_fantasy' => ['label' => 'Nome Fantasia', 'placeholder' => 'Nome fantasia da empresa'],
                'company_document' => ['label' => 'CNPJ', 'placeholder' => '00.000.000/0001-00'],
                'company_state_registration' => ['label' => 'Inscricao Estadual', 'placeholder' => 'IE ou ISENTO'],
                'company_municipal_registration' => ['label' => 'Inscricao Municipal', 'placeholder' => 'IM'],
                'company_phone' => ['label' => 'Telefone', 'placeholder' => '(00) 0000-0000'],
                'company_cellphone' => ['label' => 'Celular', 'placeholder' => '(00) 00000-0000'],
                'company_email' => ['label' => 'Email', 'placeholder' => 'contato@empresa.com.br'],
                'company_website' => ['label' => 'Site', 'placeholder' => 'https://www.empresa.com.br'],
                'company_address' => ['label' => 'Endereco', 'placeholder' => 'Rua, numero - Bairro'],
                'company_city' => ['label' => 'Cidade', 'placeholder' => 'Sao Paulo'],
                'company_state' => ['label' => 'Estado', 'placeholder' => 'SP'],
                'company_zip' => ['label' => 'CEP', 'placeholder' => '00000-000'],
                'bank_name' => ['label' => 'Banco', 'placeholder' => 'Banco do Brasil'],
                'bank_code' => ['label' => 'Codigo do Banco', 'placeholder' => '001'],
                'bank_agency' => ['label' => 'Agencia', 'placeholder' => '0000-0'],
                'bank_account' => ['label' => 'Conta', 'placeholder' => '00000-0'],
                'bank_account_type' => ['label' => 'Tipo de Conta', 'placeholder' => 'Corrente ou Poupanca'],
                'bank_cedente' => ['label' => 'Cedente', 'placeholder' => 'Nome do cedente'],
                'bank_cnpj_cedente' => ['label' => 'CNPJ do Cedente', 'placeholder' => '00.000.000/0001-00'],
                'bank_carteira' => ['label' => 'Carteira', 'placeholder' => '17 ou 109'],
                'bank_convenio' => ['label' => 'Convenio', 'placeholder' => 'Numero do convenio'],
                'boleto_layout' => ['label' => 'Layout do Boleto', 'placeholder' => 'boleto ou carteira'],
                'boleto_instrucoes' => ['label' => 'Instrucoes no Boleto', 'placeholder' => 'Instrucoes para o pagador'],
                'default_gateway' => ['label' => 'Gateway Padrao', 'placeholder' => 'mercado-pago, asaas ou gerencianet'],
                'landing_logo' => ['label' => 'Logo da Marca', 'placeholder' => 'Envie o arquivo da logo'],
                'landing_enabled' => ['label' => 'Landing Page Ativa', 'placeholder' => ''],
                'landing_hero_title' => ['label' => 'Titulo Principal da Landing', 'placeholder' => 'Internet Fibra Optica de Alta Velocidade'],
                'landing_hero_subtitle' => ['label' => 'Subtitulo da Landing', 'placeholder' => 'A melhor conectividade para sua casa ou empresa...'],
                'landing_about' => ['label' => 'Sobre o Provedor', 'placeholder' => 'Texto da secao sobre a empresa'],
                'landing_cities' => ['label' => 'Cidades Atendidas', 'placeholder' => 'Uma cidade por linha'],
                'landing_faq' => ['label' => 'Perguntas Frequentes', 'placeholder' => 'Uma pergunta por bloco. Escreva a pergunta na primera linha e a resposta nas linhas seguintes. Separe cada pergunta por uma linha em branco.'],
                'landing_map_embed' => ['label' => 'Mapa (codigo do embed)', 'placeholder' => 'Cole o codigo iframe do Google Maps'],
                'landing_whatsapp' => ['label' => 'WhatsApp (com DDI)', 'placeholder' => '5599999999999'],
                'landing_hours' => ['label' => 'Horario de Atendimento', 'placeholder' => 'Seg a Sab, das 8h as 18h'],
                'landing_facebook' => ['label' => 'Facebook (URL)', 'placeholder' => 'https://facebook.com/seuprovedor'],
                'landing_instagram' => ['label' => 'Instagram (URL)', 'placeholder' => 'https://instagram.com/seuprovedor'],
                'landing_linkedin' => ['label' => 'LinkedIn (URL)', 'placeholder' => 'https://linkedin.com/company/seuprovedor'],
                'landing_color_primary' => ['label' => 'Cor Primaria', 'placeholder' => '#1d4ed8'],
                'landing_color_primary_dark' => ['label' => 'Cor Primaria (escura)', 'placeholder' => '#1e40af'],
                'landing_color_secondary' => ['label' => 'Cor Secundaria', 'placeholder' => '#4f46e5'],
                'landing_color_hero_start' => ['label' => 'Hero - Cor Inicial', 'placeholder' => '#0b1222'],
                'landing_color_hero_mid' => ['label' => 'Hero - Cor do Meio', 'placeholder' => '#0f172a'],
                'landing_color_hero_end' => ['label' => 'Hero - Cor Final', 'placeholder' => '#1e3a8a'],
                'landing_color_dark' => ['label' => 'Cor Escura (Contato)', 'placeholder' => '#0b1222'],
                'landing_color_footer' => ['label' => 'Cor do Rodape', 'placeholder' => '#070b16'],
                'landing_section_plans_title' => ['label' => 'Titulo da Secao Planos', 'placeholder' => 'Nossos Planos'],
                'landing_section_plans_subtitle' => ['label' => 'Subtitulo da Secao Planos', 'placeholder' => 'Escolha o plano ideal para voce'],
                'landing_section_vod_title' => ['label' => 'Titulo da Secao VOD Stream', 'placeholder' => 'VOD Stream'],
                'landing_section_vod_subtitle' => ['label' => 'Subtitulo da Secao VOD Stream', 'placeholder' => 'Assista onde e quando quiser, incluido no seu plano'],
                'landing_section_coverage_title' => ['label' => 'Titulo da Secao Cobertura', 'placeholder' => 'Cobertura'],
                'landing_section_coverage_subtitle' => ['label' => 'Subtitulo da Secao Cobertura', 'placeholder' => 'Atendemos com fibra optica nas seguintes cidades'],
                'landing_section_features_title' => ['label' => 'Titulo da Secao Vantagens', 'placeholder' => 'Por que escolher'],
                'landing_section_features_subtitle' => ['label' => 'Subtitulo da Secao Vantagens', 'placeholder' => 'A tecnologia que voce merece, com atendimento de verdade'],
                'landing_section_faq_title' => ['label' => 'Titulo da Secao Duvidas', 'placeholder' => 'Perguntas Frequentes'],
                'landing_section_faq_subtitle' => ['label' => 'Subtitulo da Secao Duvidas', 'placeholder' => 'Tire suas duvidas antes de contratar'],
                'landing_section_contact_title' => ['label' => 'Titulo da Secao Contato', 'placeholder' => 'Fale Conosco'],
                'landing_section_contact_subtitle' => ['label' => 'Subtitulo da Secao Contato', 'placeholder' => 'Estamos prontos para atender voce por qualquer canal'],
                'landing_sac_title' => ['label' => 'Pagina SAC - Titulo', 'placeholder' => 'Central de Atendimento (SAC)'],
                'landing_sac_subtitle' => ['label' => 'Pagina SAC - Subtitulo', 'placeholder' => 'Estamos aqui para ajudar...'],
            ];
        @endphp

        @foreach($settings as $group => $items)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-4 border-b border-gray-200 flex items-center gap-2">
@if($group === 'company')
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                @elseif($group === 'billing')
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                @elseif($group === 'landing')
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                @else
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                @endif
                <h3 class="font-semibold text-gray-900 uppercase text-sm">
                    {{ $group === 'company' ? 'Dados da Empresa' : ($group === 'billing' ? 'Financeiro / Banco' : ($group === 'landing' ? 'Landing Page' : ucfirst($group))) }}
                </h3>
                @if($group === 'landing')
                    <a href="{{ route('landing.index') }}" target="_blank" class="ml-auto text-xs text-blue-600 hover:underline">Ver site &#8599;</a>
                @endif
            </div>
            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($items as $setting)
                <div class="{{ in_array($setting->key, ['company_address', 'boleto_instrucoes', 'landing_hero_subtitle', 'landing_about', 'landing_cities', 'landing_faq', 'landing_map_embed']) ? 'md:col-span-2' : '' }}">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ $labels[$setting->key]['label'] ?? ucfirst(str_replace('_', ' ', $setting->key)) }}
                    </label>
                    @if($setting->key === 'landing_about')
                        <div id="about-editor-wrap" style="position:relative; overflow:hidden; border-radius:8px;">
                            <textarea id="about-textarea" rows="6" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="{{ $labels[$setting->key]['placeholder'] ?? '' }}">{{ $setting->value }}</textarea>
                            <div id="about-editor" style="height:220px; display:none;"></div>
                        </div>
                        <input type="hidden" name="settings[landing_about]" id="about-value" value="{{ $setting->value }}">
                        <p class="mt-1 text-xs text-gray-400">Use o editor para formatar o texto. Tags de titulo (h3) e listas sao suportadas. Se o editor nao carregar, use a caixa de texto simples.</p>
                    @elseif(str_starts_with($setting->key, 'landing_color_'))
                        <div class="flex items-center gap-3">
                            <input type="color" value="{{ $setting->value ?: '#000000' }}" data-color-target="input[name='settings[{{ $setting->key }}]']" class="w-12 h-10 rounded-lg border border-gray-300 cursor-pointer p-1 bg-white">
                            <input type="text" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono" placeholder="{{ $labels[$setting->key]['placeholder'] ?? '#000000' }}">
                        </div>
                        <p class="mt-1 text-xs text-gray-400">Cor no formato hex (ex: #1d4ed8). Click no quadrado para escolher.</p>
                    @elseif($setting->type === 'file')
                        <div class="flex items-center gap-4">
                            <input type="file" name="logo" accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm file:mr-3 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 file:text-sm file:font-medium">
                            @if($setting->value)
                                <a href="{{ asset('storage/'.$setting->value) }}" target="_blank">
                                    <img src="{{ asset('storage/'.$setting->value) }}" alt="Logo" class="h-12 w-auto rounded border border-gray-200">
                                </a>
                            @endif
                        </div>
                        <p class="mt-1 text-xs text-gray-400">PNG, JPG, SVG ou WebP ate 2MB.</p>
                    @elseif($setting->type === 'textarea')
                        <textarea name="settings[{{ $setting->key }}]" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="{{ $labels[$setting->key]['placeholder'] ?? '' }}">{{ $setting->value }}</textarea>
                    @elseif($setting->type === 'boolean')
                        <select name="settings[{{ $setting->key }}]" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="1" {{ $setting->value == '1' ? 'selected' : '' }}>Sim</option>
                            <option value="0" {{ $setting->value == '0' ? 'selected' : '' }}>Nao</option>
                        </select>
                    @elseif($setting->type === 'password')
                        <input type="password" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="{{ $labels[$setting->key]['placeholder'] ?? '' }}">
                    @elseif($setting->type === 'number')
                        <input type="number" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="{{ $labels[$setting->key]['placeholder'] ?? '' }}">
                    @else
                        <input type="text" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="{{ $labels[$setting->key]['placeholder'] ?? '' }}">
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Salvar Configuracoes</button>
        </div>
    </form>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.min.js"></script>
<script>
(function () {
    var hidden = document.getElementById('about-value');
    if (!hidden) return;
    if (typeof Quill === 'undefined') return;
    var textarea = document.getElementById('about-textarea');
    var editorEl = document.getElementById('about-editor');
    editorEl.style.display = 'block';
    textarea.style.display = 'none';
    var quill = new Quill(editorEl, {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                [{ 'align': [] }],
                ['link']
            ]
        },
        placeholder: 'Texto da secao sobre a empresa...'
    });
    if (hidden.value) {
        quill.clipboard.dangerouslyPasteHTML(hidden.value);
    }
    var form = hidden.closest('form');
    form.addEventListener('submit', function () {
        hidden.value = quill.root.innerHTML;
    });
})();

document.querySelectorAll('input[type="color"][data-color-target]').forEach(function (picker) {
    var text = document.querySelector(picker.dataset.colorTarget);
    if (!text) return;
    picker.addEventListener('input', function () {
        text.value = picker.value;
    });
    text.addEventListener('input', function () {
        var v = text.value.trim();
        if (/^#[0-9a-fA-F]{6}$/.test(v)) picker.value = v;
    });
});
</script>
@endpush
