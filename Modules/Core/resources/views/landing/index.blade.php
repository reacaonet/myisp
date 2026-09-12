<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $name }}</title>
    <style>
        :root {
            --c-primary: {{ $colors['primary'] }};
            --c-primary-dark: {{ $colors['primary_dark'] }};
            --c-secondary: {{ $colors['secondary'] }};
            --c-hero-start: {{ $colors['hero_start'] }};
            --c-hero-mid: {{ $colors['hero_mid'] }};
            --c-hero-end: {{ $colors['hero_end'] }};
            --c-dark: {{ $colors['dark'] }};
            --c-footer: {{ $colors['footer'] }};
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, -apple-system, Arial, sans-serif; color: #1e293b; line-height: 1.6; background: #fff; }
        .container { max-width: 1180px; margin: 0 auto; padding: 0 24px; }
        img { max-width: 100%; }
        .no-margin { margin: 0; }

        /* ------------------------------------------------------------------ */
        /* Header                                                              */
        /* ------------------------------------------------------------------ */
        header.site { position: sticky; top: 0; z-index: 60; background: #fff; border-bottom: 1px solid #eef2f7; box-shadow: 0 1px 4px rgba(15,23,42,0.05); }
        header.site .container { display: flex; align-items: center; justify-content: space-between; height: 74px; }
        .brand { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .brand img { height: 64px; width: auto; }
        .brand span { font-size: 1.3rem; font-weight: 800; color: #0f172a; }
        nav.site { display: flex; align-items: center; gap: 26px; }
        nav.site a.nav-link { color: #475569; text-decoration: none; font-size: 0.92rem; font-weight: 500; transition: color 0.2s; }
        nav.site a.nav-link:hover { color: var(--c-primary); }
        .btn { display: inline-block; padding: 11px 22px; border-radius: 10px; font-weight: 600; font-size: 0.92rem; text-decoration: none; border: none; cursor: pointer; transition: transform 0.15s, background 0.2s; }
        .btn:hover { transform: translateY(-1px); }
        .btn-primary { background: linear-gradient(135deg, var(--c-secondary), var(--c-primary)); color: #fff; box-shadow: 0 6px 18px rgba(37,99,235,0.35); }
        .btn-primary:hover { filter: brightness(1.08); }
        .btn-outline { background: transparent; color: #fff; border: 2px solid rgba(255,255,255,0.4); }
        .btn-outline:hover { background: rgba(255,255,255,0.1); }
        .btn-dark { background: var(--c-dark); color: #fff; }
        .btn-light { background: #fff; color: var(--c-primary); }
        .btn-light:hover { background: #f1f5f9; }
        .btn-whatsapp { background: #22c55e; color: #fff; }
        .btn-whatsapp:hover { background: #16a34a; }
        .btn-block { display: block; text-align: center; }

        /* ------------------------------------------------------------------ */
        /* Hero                                                                */
        /* ------------------------------------------------------------------ */
        section.hero { background: radial-gradient(1200px 500px at 15% -10%, rgba(37,99,235,0.35), transparent 60%), linear-gradient(160deg, var(--c-hero-start) 0%, var(--c-hero-mid) 55%, var(--c-hero-end) 130%); color: #fff; position: relative; overflow: hidden; }
        section.hero::after { content: ''; position: absolute; right: -120px; top: 40px; width: 420px; height: 420px; background: radial-gradient(circle, rgba(59,130,246,0.22), transparent 70%); z-index: 1; }
        section.hero .container { padding-top: 84px; padding-bottom: 96px; text-align: center; position: relative; z-index: 2; }
        section.hero h1 { font-size: clamp(2rem, 4.5vw, 3.3rem); font-weight: 800; line-height: 1.15; margin-bottom: 18px; letter-spacing: -0.02em; }
        section.hero h1 .grada { background: linear-gradient(120deg, #60a5fa, #a5b4fc); -webkit-background-clip: text; background-clip: text; color: transparent; }
        section.hero p.sub { font-size: clamp(1rem, 2vw, 1.18rem); color: #b6c2d9; max-width: 760px; margin: 0 auto 30px; }
        .hero-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(96,165,250,0.15); color: #bfdbfe; border: 1px solid rgba(96,165,250,0.35); padding: 8px 18px; border-radius: 999px; font-size: 0.83rem; font-weight: 600; margin-bottom: 22px; letter-spacing: 0.03em; }
        .city-select-wrap { max-width: 460px; margin: 0 auto 30px; text-align: left; position: relative; }
        .city-select-wrap label { display: block; font-size: 0.78rem; color: #94a3b8; margin-bottom: 8px; letter-spacing: 0.05em; text-transform: uppercase; }
        .city-select { width: 100%; padding: 14px 42px 14px 18px; border-radius: 12px; border: 1px solid rgba(147,197,253,0.4); background: rgba(255,255,255,0.97); color: #0f172a; font-size: 1rem; font-weight: 600; outline: none; cursor: pointer; box-shadow: 0 10px 30px rgba(0,0,0,0.25); appearance: none; }
        .city-select-wrap::after { content: '▾'; position: absolute; right: 18px; top: 42px; color: var(--c-primary); pointer-events: none; font-size: 1.1rem; }
        .hero-actions { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }

        /* ------------------------------------------------------------------ */
        /* Stats band                                                          */
        /* ------------------------------------------------------------------ */
        section.stats { background: #fff; padding: 36px 0; box-shadow: 0 1px 0 #eef2f7; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 18px; text-align: center; }
        .stat-item .num { font-size: 1.7rem; font-weight: 800; color: var(--c-primary); }
        .stat-item .lbl { font-size: 0.82rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 2px; }

        /* ------------------------------------------------------------------ */
        /* Coverage                                                           */
        /* ------------------------------------------------------------------ */
        section.coverage { padding: 54px 0 34px; }
        .chip-cloud { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; max-width: 780px; margin: 0 auto; }
        .chip { background: #eff6ff; color: var(--c-primary); border: 1px solid #dbeafe; font-weight: 600; font-size: 0.85rem; padding: 8px 16px; border-radius: 999px; }

        /* ------------------------------------------------------------------ */
        /* Features                                                            */
        /* ------------------------------------------------------------------ */
        section.features { padding: 60px 0; }
        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 22px; margin-top: 34px; }
        .feature-card { background: #f8fafc; border: 1px solid #edf2f7; border-radius: 16px; padding: 28px 24px; text-align: center; transition: transform 0.2s, box-shadow 0.2s; }
        .feature-card:hover { transform: translateY(-3px); box-shadow: 0 14px 34px rgba(15,23,42,0.08); }
        .feature-card .ic { font-size: 2.2rem; margin-bottom: 12px; }
        .feature-card h3 { font-size: 1.05rem; margin-bottom: 6px; color: #0f172a; }
        .feature-card p { font-size: 0.88rem; color: #64748b; }

        /* ------------------------------------------------------------------ */
        /* Plans                                                               */
        /* ------------------------------------------------------------------ */
        section.plans { padding: 70px 0; background: linear-gradient(180deg, #f1f5f9 0%, #fff 100%); }
        .section-title { text-align: center; margin-bottom: 40px; }
        .section-title h2 { font-size: clamp(1.6rem, 3vw, 2.2rem); font-weight: 800; color: #0f172a; }
        .section-title p { color: #64748b; margin-top: 8px; }
        .plans-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 22px; }
        .plan-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 30px 26px; display: flex; flex-direction: column; position: relative; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: transform 0.2s, box-shadow 0.2s; }
        .plan-card:hover { transform: translateY(-4px); box-shadow: 0 18px 40px rgba(15,23,42,0.10); }
        .plan-card.featured { border: 2px solid var(--c-primary); box-shadow: 0 16px 40px rgba(37,99,235,0.14); }
        .plan-card .tag { position: absolute; top: -13px; left: 50%; transform: translateX(-50%); background: linear-gradient(135deg, var(--c-secondary), var(--c-primary)); color: #fff; font-size: 0.72rem; font-weight: 700; padding: 5px 16px; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.06em; }
        .plan-card .plan-icon { font-size: 1.6rem; margin-bottom: 8px; }
        .plan-card h3 { font-size: 1.2rem; font-weight: 700; color: #0f172a; }
        .plan-card .desc { font-size: 0.85rem; color: #64748b; margin: 4px 0 16px; min-height: 38px; }
        .plan-card .price { font-size: 2.1rem; font-weight: 800; color: var(--c-primary); margin-bottom: 4px; line-height: 1.1; }
        .plan-card .price small { font-size: 0.9rem; font-weight: 500; color: #64748b; }
        .plan-card .speeds { display: flex; gap: 18px; margin: 14px 0 20px; border-top: 1px solid #eef2f7; padding-top: 16px; }
        .plan-card .speeds div { flex: 1; }
        .plan-card .speeds .lbl { font-size: 0.72rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; }
        .plan-card .speeds .val { font-size: 1.05rem; font-weight: 700; color: #0f172a; }
        .plan-card .btn { margin-top: auto; text-align: center; }

        /* ------------------------------------------------------------------ */
        /* About                                                               */
        /* ------------------------------------------------------------------ */
        section.about { padding: 76px 0; background: #fff; }
        .about-wrap { display: grid; grid-template-columns: 1fr 1fr; gap: 54px; align-items: center; }
        .about-wrap h2 { font-size: clamp(1.5rem, 3vw, 2.1rem); font-weight: 800; color: #0f172a; margin-bottom: 18px; }
        .about-wrap p { color: #475569; margin-bottom: 14px; }
        .about-wrap ul { list-style: none; margin: 16px 0; }
        .about-wrap ul li { padding: 6px 0; color: #475569; }
        .about-wrap ul li::before { content: '✓'; color: #16a34a; font-weight: 700; margin-right: 10px; }
        .about-badge { display: inline-flex; align-items: center; gap: 8px; background: #eff6ff; color: var(--c-primary); padding: 8px 16px; border-radius: 10px; font-weight: 600; font-size: 0.9rem; margin-bottom: 20px; }
        .about-visual { background: linear-gradient(160deg, var(--c-hero-end), var(--c-hero-mid)); border-radius: 20px; padding: 40px 34px; color: #fff; position: relative; overflow: hidden; }
        .about-visual::after { content: ''; position: absolute; right: -60px; bottom: -60px; width: 220px; height: 220px; background: radial-gradient(circle, rgba(59,130,246,0.35), transparent 70%); }
        .about-visual h3 { font-size: 1.4rem; font-weight: 700; margin-bottom: 14px; }
        .about-visual p { color: #c7d2fe; }
        .about-visual .mega { font-size: 2.6rem; font-weight: 800; margin: 18px 0 4px; }

        /* ------------------------------------------------------------------ */
        /* FAQ                                                                 */
        /* ------------------------------------------------------------------ */
        section.faq { padding: 70px 0; background: #f8fafc; }
        .faq-list { max-width: 820px; margin: 0 auto; }
        .faq-item { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; margin-bottom: 12px; overflow: hidden; }
        .faq-item summary { display: flex; align-items: center; justify-content: space-between; padding: 18px 22px; cursor: pointer; font-weight: 600; color: #0f172a; font-size: 0.98rem; list-style: none; }
        .faq-item summary::-webkit-details-marker { display: none; }
        .faq-item summary .arrow { color: var(--c-primary); transition: transform 0.2s; flex-shrink: 0; margin-left: 14px; }
        .faq-item[open] summary .arrow { transform: rotate(180deg); }
        .faq-item .faq-answer { padding: 0 22px 18px; color: #64748b; font-size: 0.92rem; }

        /* ------------------------------------------------------------------ */
        /* Contact                                                             */
        /* ------------------------------------------------------------------ */
        section.contact { padding: 70px 0 84px; background: var(--c-dark); color: #e2e8f0; }
        .contact-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 22px; margin-top: 34px; }
        .contact-box { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.09); border-radius: 16px; padding: 26px 24px; }
        .contact-box .ic { font-size: 1.6rem; margin-bottom: 12px; }
        .contact-box h4 { color: #fff; font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; }
        .contact-box p, .contact-box a { color: #cbd5e1; text-decoration: none; word-break: break-word; }
        .contact-box a:hover { color: #fff; text-decoration: underline; }
        .map-wrap { margin-top: 26px; border-radius: 16px; overflow: hidden; border: 1px solid rgba(255,255,255,0.1); }
        .map-wrap iframe { display: block; width: 100%; height: 320px; border: 0; }

        /* ------------------------------------------------------------------ */
        /* Footer                                                              */
        /* ------------------------------------------------------------------ */
        footer.site { background: var(--c-footer); color: #64748b; padding: 44px 0 24px; }
        .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 30px; margin-bottom: 34px; }
        .footer-grid h4 { color: #e2e8f0; font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 14px; }
        .footer-grid a { display: block; color: #8194b0; text-decoration: none; font-size: 0.88rem; padding: 4px 0; }
        .footer-grid a:hover { color: #fff; }
        .footer-brand img { height: 54px; width: auto; margin-bottom: 10px; }
        footer.site .socials { display: flex; gap: 10px; margin-top: 12px; }
        footer.site .socials a { display: flex; width: 38px; height: 38px; border-radius: 10px; background: rgba(255,255,255,0.08); color: #cbd5e1; align-items: center; justify-content: center; text-decoration: none; font-size: 0.95rem; }
        footer.site .socials a:hover { background: var(--c-primary); color: #fff; }
        .footer-bottom { border-top: 1px solid rgba(255,255,255,0.07); padding-top: 20px; text-align: center; font-size: 0.82rem; }

        .fab { position: fixed; bottom: 24px; right: 24px; z-index: 70; }
        .fab a { display: flex; width: 58px; height: 58px; border-radius: 50%; background: #22c55e; color: #fff; text-decoration: none; align-items: center; justify-content: center; box-shadow: 0 10px 24px rgba(34,197,94,0.45); transition: transform 0.15s; }
        .fab a:hover { transform: scale(1.08); }

        @media (max-width: 900px) {
            nav.site a.nav-link { display: none; }
            .about-wrap { grid-template-columns: 1fr; }
            .footer-grid { grid-template-columns: 1fr 1fr; }
            .container { padding: 0 18px; }
        }
        @media (max-width: 520px) {
            .footer-grid { grid-template-columns: 1fr; }
            .brand span { display: none; }
        }
        .empty-note { text-align: center; color: #64748b; font-size: 0.95rem; }
    </style>
</head>
<body>

<header class="site">
    <div class="container">
        <a class="brand" href="/">
            @if($logo)
                <img src="{{ asset('storage/'.$logo) }}" alt="{{ $name }}">
            @else
                <span>{{ $name }}</span>
            @endif
        </a>
        <nav class="site">
            <a class="nav-link" href="#planos">Planos</a>
            <a class="nav-link" href="#cobertura">Cobertura</a>
            <a class="nav-link" href="#sobre">Sobre</a>
            <a class="nav-link" href="#duvidas">Duvidas</a>
            <a class="nav-link" href="#contato">Contato</a>
            <a class="btn btn-primary" href="{{ route('crm.portal.login') }}">Area do Cliente</a>
        </nav>
    </div>
</header>

<section class="hero">
    <div class="container">
        <span class="hero-badge">🚀 Internet Fibra Optica</span>
        <h1>{!! $hero_title !!}</h1>
        @if($hero_subtitle)
            <p class="sub">{{ $hero_subtitle }}</p>
        @endif
        @if($cities->count())
        <div class="city-select-wrap">
            <label for="sel-cidade">Escolha sua cidade</label>
            <select id="sel-cidade" class="city-select">
                <option value="">Selecione a cidade</option>
                @foreach($cities as $cidade)
                <option value="{{ $cidade }}">{{ $cidade }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="hero-actions">
            <a class="btn btn-outline" href="#planos">Ver Planos</a>
            @if($whatsapp)
                <a class="btn btn-primary" href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}">Falar no WhatsApp</a>
            @endif
        </div>
    </div>
</section>

<section class="stats">
    <div class="container">
        <div class="stats-grid">
            @if($cities->count())
            <div class="stat-item"><div class="num">{{ $cities->count() }}</div><div class="lbl">Cidades Atendidas</div></div>
            @endif
            <div class="stat-item"><div class="num">100%</div><div class="lbl">Fibra Optica</div></div>
            <div class="stat-item"><div class="num">24h</div><div class="lbl">Suporte ao Cliente</div></div>
            <div class="stat-item"><div class="num">⛰️→🏠</div><div class="lbl">FTTH na sua casa</div></div>
        </div>
    </div>
</section>

@if($cities->count())
<section class="coverage" id="cobertura">
    <div class="container">
        <div class="section-title">
            <h2>{{ $titles['coverage_title'] }}</h2>
            <p>{{ $titles['coverage_subtitle'] }}</p>
        </div>
        <div class="chip-cloud">
            @foreach($cities as $cidade)
            <span class="chip">📍 {{ $cidade }}</span>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="features">
    <div class="container">
        <div class="section-title">
            <h2>{{ $titles['features_title'] }} {{ $name }}?</h2>
            <p>{{ $titles['features_subtitle'] }}</p>
        </div>
        <div class="features-grid">
            <div class="feature-card"><div class="ic">🚀</div><h3>Alta Velocidade</h3><p>Conexao simetrica e estavel para navegar, jogar e trabalhar sem travar.</p></div>
            <div class="feature-card"><div class="ic">📶</div><h3>Fibra Optica FTTH</h3><p>Tecnologia de ultima geracao chegando direto na sua casa.</p></div>
            <div class="feature-card"><div class="ic">🛡️</div><h3>Suporte Rapido</h3><p>Atendimento agil e dedicado para resolver qualquer problema.</p></div>
            <div class="feature-card"><div class="ic">💸</div><h3>Precos Justos</h3><p>Planos que cabem no seu bolso, sem letras miudas.</p></div>
        </div>
    </div>
</section>

@if($plans->count())
<section class="plans" id="planos">
    <div class="container">
        <div class="section-title">
            <h2>{{ $titles['plans_title'] }}</h2>
            <p>{{ $titles['plans_subtitle'] }}</p>
        </div>
        <div class="plans-grid">
            @foreach($plans as $index => $plan)
                @php
                    $down = number_format($plan->download_speed / 1024, 0);
                    $up = number_format($plan->upload_speed / 1024, 0);
                @endphp
            <div class="plan-card {{ $index === 1 ? 'featured' : '' }}">
                @if($index === 1)
                    <span class="tag">Mais Popular</span>
                @endif
                <div class="plan-icon">📡</div>
                <h3>{{ $plan->name }}</h3>
                @if($plan->description)
                    <p class="desc">{{ $plan->description }}</p>
                @endif
                <div class="price">R$ {{ number_format((float) $plan->price, 2, ',', '.') }} <small>/mes</small></div>
                <div class="speeds">
                    <div><div class="lbl">Download</div><div class="val">{{ $down }} Mbps</div></div>
                    <div><div class="lbl">Upload</div><div class="val">{{ $up }} Mbps</div></div>
                </div>
                <a class="btn btn-primary plan-cta" data-plan="{{ $plan->name }}" target="_blank" href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}?text=Tenho%20interesse%20no%20plano%20{{ $plan->name }}">Quero esse plano</a>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if($about)
<section class="about" id="sobre">
    <div class="container">
        <div class="about-wrap">
            <div>
                <span class="about-badge">👋 Quem somos</span>
                <h2>Sobre a {{ $name }}</h2>
                <div>{!! $about !!}</div>
            </div>
            <div class="about-visual">
                <h3>Conectividade que transforma</h3>
                <p>Estamos comprometidos em levar internet de qualidade a todos, com atendimento humano e infraestrutura de ponta.</p>
                @if($whatsapp)
                <a class="btn btn-light btn-block" style="margin-top:18px;" href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}">Fale conosco agora</a>
                @endif
            </div>
        </div>
    </div>
</section>
@endif

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
            @if($cellphone)
            <div class="contact-box"><div class="ic">📱</div><h4>Celular</h4><a href="tel:{{ preg_replace('/\D/', '', $cellphone) }}">{{ $cellphone }}</a></div>
            @endif
            @if($email)
            <div class="contact-box"><div class="ic">✉️</div><h4>Email</h4><a href="mailto:{{ $email }}">{{ $email }}</a></div>
            @endif
            @if($hours)
            <div class="contact-box"><div class="ic">🕐</div><h4>Horario</h4><p>{{ $hours }}</p></div>
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

<footer class="site">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                @if($logo)
                    <img src="{{ asset('storage/'.$logo) }}" alt="{{ $name }}">
                @else
                    <h3 style="color:#fff;">{{ $name }}</h3>
                @endif
                <p style="font-size:0.88rem;">Internet fibra optica de alta velocidade para sua casa e empresa.</p>
                <div class="socials">
                    @if($facebook)<a href="{{ $facebook }}" target="_blank" aria-label="Facebook">f</a>@endif
                    @if($instagram)<a href="{{ $instagram }}" target="_blank" aria-label="Instagram">◉</a>@endif
                    @if($linkedin)<a href="{{ $linkedin }}" target="_blank" aria-label="LinkedIn">in</a>@endif
                    @if($whatsapp)<a href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}" target="_blank" aria-label="WhatsApp">w</a>@endif
                </div>
            </div>
            <div>
                <h4>Navegacao</h4>
                <a href="#planos">Planos</a>
                <a href="#cobertura">Cobertura</a>
                <a href="#sobre">Sobre</a>
                <a href="#duvidas">Duvidas</a>
                <a href="#contato">Contato</a>
            </div>
            <div>
                <h4>Cliente</h4>
                <a href="{{ route('crm.portal.login') }}">Area do Cliente</a>
                <a href="{{ route('crm.portal.login') }}">2 via de boleto</a>
                <a href="{{ route('crm.portal.login') }}">Suporte tecnico</a>
            </div>
            <div>
                <h4>Fale com a gente</h4>
                @if($whatsapp)<a href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}">WhatsApp</a>@endif
                @if($phone)<a href="tel:{{ preg_replace('/\D/', '', $phone) }}">{{ $phone }}</a>@endif
                @if($email)<a href="mailto:{{ $email }}">{{ $email }}</a>@endif
                @if($address)<a style="cursor:default;">{{ $address }}</a>@endif
            </div>
        </div>
        <div class="footer-bottom">
            © {{ date('Y') }} {{ $name }}. Todos os direitos reservados. CNPJ {{ \Modules\Core\Models\SystemSetting::get('company_document', '') }}
        </div>
    </div>
</footer>

@if($whatsapp)
<div class="fab">
    <a href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}" target="_blank" title="WhatsApp" aria-label="WhatsApp">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
    </a>
</div>
@endif

<script>
    (function () {
        var sel = document.getElementById('sel-cidade');
        if (!sel) return;
        var planButtons = document.querySelectorAll('.plan-cta');
        sel.addEventListener('change', function () {
            var cidade = sel.value;
            planButtons.forEach(function (btn) {
                var texto = 'Tenho interesse no plano ' + btn.getAttribute('data-plan');
                if (cidade) texto += ' para a cidade de ' + cidade;
                var url = new URL(btn.href);
                url.searchParams.set('text', texto);
                btn.href = url.toString();
            });
        });
    })();
</script>

</body>
</html>