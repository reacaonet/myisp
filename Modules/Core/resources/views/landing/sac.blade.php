@include('core::landing.partials.head', ['title' => $sac_title . ' - ' . $name])
<style>
    .sac-hero { position: relative; overflow: hidden; background: linear-gradient(160deg, var(--c-hero-start) 0%, var(--c-hero-mid) 55%, var(--c-hero-end) 130%); color: #fff; }
    .sac-hero::before { content: ''; position: absolute; width: 460px; height: 460px; border-radius: 50%; background: radial-gradient(circle, rgba(96,165,250,0.28), transparent 65%); top: -120px; right: -80px; }
    .sac-hero .container { padding: 74px 24px 90px; max-width: 820px; text-align: center; position: relative; z-index: 2; }
    .sac-crumb { display: inline-flex; align-items: center; gap: 8px; font-size: 0.8rem; color: #8194b0; margin-bottom: 18px; }
    .sac-crumb a { color: #bfdbfe; }
    .sac-hero h1 { font-size: clamp(2rem, 4.5vw, 3.2rem); font-weight: 900; letter-spacing: -0.025em; line-height: 1.12; margin-bottom: 14px; }
    .sac-hero h1 .grada { background: linear-gradient(120deg, #93c5fd, #c7d2fe); -webkit-background-clip: text; background-clip: text; color: transparent; }
    .sac-hero p { color: #b6c2d9; font-size: 1.05rem; max-width: 640px; margin: 0 auto 26px; }
    .sac-actions { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
    .sac-quick { padding: 66px 0 26px; }
    .sac-quick-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px; margin-top: 30px; }
    .sac-quick-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 28px 26px; text-align: center; transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s; }
    .sac-quick-card:hover { transform: translateY(-4px); box-shadow: 0 18px 40px rgba(15,23,42,0.10); border-color: #dbeafe; }
    .sac-quick-card .ic { width: 62px; height: 62px; margin: 0 auto 14px; display: flex; align-items: center; justify-content: center; font-size: 1.7rem; border-radius: 18px; background: linear-gradient(135deg, rgba(99,102,241,0.12), rgba(37,99,235,0.12)); }
    .sac-quick-card h3 { font-size: 1.02rem; font-weight: 700; color: #0f172a; margin-bottom: 6px; }
    .sac-quick-card p { font-size: 0.85rem; color: #64748b; margin-bottom: 14px; }
    .sac-quick-card .btn { width: 100%; }
    section.sac-channels { padding: 40px 0 70px; background: #f8fafc; }
    .sac-form-card { margin-top: 34px; background: #fff; border: 1px solid #e2e8f0; border-radius: 22px; padding: 36px; box-shadow: 0 10px 30px rgba(15,23,42,0.06); }
    .sac-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .sac-form-field { display: flex; flex-direction: column; gap: 6px; }
    .sac-form-field.full { grid-column: 1 / -1; }
    .sac-form-field label { font-size: 0.82rem; font-weight: 600; color: #334155; }
    .sac-form-field input, .sac-form-field select, .sac-form-field textarea { padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 0.92rem; font-family: inherit; outline: none; transition: border-color 0.2s, box-shadow 0.2s; }
    .sac-form-field input:focus, .sac-form-field select:focus, .sac-form-field textarea:focus { border-color: var(--c-primary); box-shadow: 0 0 0 3px rgba(37,99,235,0.12); }
    .sac-form-note { font-size: 0.78rem; color: #94a3b8; margin-top: 14px; }
    @media (max-width: 720px) { .sac-form-grid { grid-template-columns: 1fr; } }
</style>

@include('core::landing.partials.header')

<section class="sac-hero">
    <div class="container">
        <span class="sac-crumb"><a href="{{ route('landing.index') }}">Home</a> <span>/</span> <strong>Sac</strong></span>
        <h1>🎧 {{ $sac_title }}</h1>
        <p>{{ $sac_subtitle }}</p>
        <div class="sac-actions">
            @if($whatsapp)
                <a class="btn btn-whatsapp" href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}">WhatsApp</a>
            @endif
            @if($phone)
                <a class="btn btn-outline" href="tel:{{ preg_replace('/\D/', '', $phone) }}">{{ $phone }}</a>
            @endif
            <a class="btn btn-outline" href="{{ route('crm.portal.login') }}">Portal do Cliente</a>
        </div>
    </div>
</section>

<section class="sac-quick">
    <div class="container">
        <div class="section-title">
            <h2>Como podemos ajudar?</h2>
            <p>Escolha o canal mais pratico para voce e resolva sua questao rapidinho.</p>
        </div>
        <div class="sac-quick-grid">
            @if($whatsapp)
            <div class="sac-quick-card">
                <div class="ic">💬</div>
                <h3>WhatsApp</h3>
                <p>Atendimento rapido com resposta em minutos. 2 via de boleto, ouvidoria e mais.</p>
                <a class="btn btn-whatsapp" href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}?text=Ol%C3%A1%2C%20preciso%20de%20ajuda%20do%20SAC">Chamar agora</a>
            </div>
            @endif
            <div class="sac-quick-card">
                <div class="ic">👤</div>
                <h3>Portal do Cliente</h3>
                <p>2 via de boleto, consulta de consumo, dados do contrato e abrir chamados.</p>
                <a class="btn btn-primary" href="{{ route('crm.portal.login') }}">Acessar portal</a>
            </div>
            @if($phone || $cellphone)
            @php $sacPhones = trim(($phone ?? '') . ($phone && $cellphone ? ' / ' : '') . ($cellphone ?? '')); @endphp
            <div class="sac-quick-card">
                <div class="ic">📞</div>
                <h3>Ligacao</h3>
                <p>{{ $sacPhones }}</p>
                <a class="btn btn-dark" href="tel:{{ preg_replace('/\D/', '', $phone ?: $cellphone) }}">Ligar agora</a>
            </div>
            @endif
        </div>
    </div>
</section>

<section class="sac-channels">
    <div class="container" id="formulario">
        <div class="section-title">
            <h2>Envie sua solicitacao</h2>
            <p>Preencha o formulario abaixo e receba o atendimento direto no WhatsApp da {{ $name }}.</p>
        </div>

        <div class="sac-form-card">
            <form onsubmit="enviarSac(event)">
                <div class="sac-form-grid">
                    <div class="sac-form-field">
                        <label for="sac-nome">Nome</label>
                        <input type="text" id="sac-nome" placeholder="Seu nome completo" required>
                    </div>
                    <div class="sac-form-field">
                        <label for="sac-assunto">Assunto</label>
                        <select id="sac-assunto" required>
                            <option value="">Selecione o assunto</option>
                            <option>Sinal de internet</option>
                            <option>2 via de boleto / pagamento</option>
                            <option>Mudanca de endereco</option>
                            <option>Upgrade de plano</option>
                            <option>Cancelamento</option>
                            <option>Reclamacao / Ouvidoria</option>
                            <option>Outros</option>
                        </select>
                    </div>
                    <div class="sac-form-field full">
                        <label for="sac-mensagem">Mensagem</label>
                        <textarea id="sac-mensagem" rows="4" placeholder="Descreva sua solicitacao..." required></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-whatsapp" style="margin-top:20px;">Enviar pelo WhatsApp</button>
                @if($email)
                <a class="btn btn-light" href="mailto:{{ $email }}" style="margin-top:20px; margin-left:10px;">Enviar por email</a>
                @endif
                <p class="sac-form-note">Seu mensagem abre uma conversa no WhatsApp com o numero do SAC. Nenhum dado e armazenado por este formulario.</p>
            </form>
        </div>
    </div>
</section>

@if($faq->count())
<section class="faq" id="duvidas">
    <div class="container">
        <div class="section-title">
            <h2>{{ $titles['faq_title'] }}</h2>
            <p>{{ $titles['faq_subtitle'] }}</p>
        </div>
        <div class="faq-list">
            @foreach($faq as $item)
            <details class="faq-item">
                <summary>{{ $item['question'] }}
                    <svg class="arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </summary>
                <div class="faq-answer">{{ $item['answer'] }}</div>
            </details>
            @endforeach
        </div>
    </div>
</section>
@endif

@if($map_embed || $address || $city)
<section class="contact" id="contato">
    <div class="container">
        <div class="section-title">
            <h2 style="color:#fff;">{{ $titles['contact_title'] }}</h2>
            <p style="color:#8194b0;">{{ $titles['contact_subtitle'] }}</p>
        </div>
        <div class="contact-grid">
            @if($whatsapp)
            <div class="contact-box"><div class="ic">💬</div><h4>WhatsApp</h4><a href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}">Chamar no WhatsApp</a></div>
            @endif
            @if($phone)
            <div class="contact-box"><div class="ic">📞</div><h4>Telefone</h4><a href="tel:{{ preg_replace('/\D/', '', $phone) }}">{{ $phone }}</a></div>
            @endif
            @if($email)
            <div class="contact-box"><div class="ic">✉️</div><h4>Email</h4><a href="mailto:{{ $email }}">{{ $email }}</a></div>
            @endif
            @if($instagram)
            <div class="contact-box"><div class="ic">📸</div><h4>Instagram</h4><a href="{{ $instagram }}" target="_blank">@itamidiatelecom</a></div>
            @endif
            @if($hours)
            <div class="contact-box"><div class="ic">🕐</div><h4>Horario de Atendimento</h4><p>{{ $hours }}</p></div>
            @endif
            @if($address || $city)
            <div class="contact-box"><div class="ic">📍</div><h4>Endereco</h4><p>{{ $address }}@if($city) - {{ $city }}/{{ $state }}@endif</p></div>
            @endif
        </div>
        @if($map_embed)
        <div class="map-wrap">
            {!! $map_embed !!}
        </div>
        @endif
    </div>
</section>
@endif

@include('core::landing.partials.footer')

<script>
    function enviarSac(e) {
        e.preventDefault();
        var nome = document.getElementById('sac-nome').value.trim();
        var assunto = document.getElementById('sac-assunto').value;
        var mensagem = document.getElementById('sac-mensagem').value.trim();
        @if($whatsapp)
        var texto = 'Olá! Meu nome é ' + nome + '. Assunto: ' + assunto + '. ' + mensagem;
        var url = 'https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}?text=' + encodeURIComponent(texto);
        window.open(url, '_blank');
        @elseif($email)
        var mailto = 'mailto:{{ $email }}?subject=' + encodeURIComponent('[SAC] ' + assunto) + '&body=' + encodeURIComponent(mensagem + '\n\nNome: ' + nome);
        window.open(mailto, '_blank');
        @endif
    }
</script>

</body>
</html>