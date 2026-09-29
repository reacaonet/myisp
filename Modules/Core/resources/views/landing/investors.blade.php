@include('core::landing.partials.head', ['title' => $investor['title'] . ' - ' . $name])
<style>
    .inv-hero { position: relative; overflow: hidden; background: linear-gradient(160deg, var(--c-hero-start) 0%, var(--c-hero-mid) 55%, var(--c-hero-end) 130%); color: #fff; }
    .inv-hero::before { content: ''; position: absolute; width: 520px; height: 520px; border-radius: 50%; background: radial-gradient(circle, rgba(52,211,153,0.22), transparent 65%); top: -160px; right: -120px; }
    .inv-hero .container { padding: 84px 24px 96px; max-width: 860px; text-align: center; position: relative; z-index: 2; }
    .inv-crumb { display: inline-flex; align-items: center; gap: 8px; font-size: 0.8rem; color: #8194b0; margin-bottom: 18px; }
    .inv-crumb a { color: #a7f3d0; }
    .inv-hero h1 { font-size: clamp(2rem, 4.6vw, 3.3rem); font-weight: 900; letter-spacing: -0.025em; line-height: 1.1; margin-bottom: 16px; }
    .inv-hero h1 .grada { background: linear-gradient(120deg, #6ee7b7, #93c5fd); -webkit-background-clip: text; background-clip: text; color: transparent; }
    .inv-hero p { color: #b6c2d9; font-size: 1.06rem; max-width: 680px; margin: 0 auto 28px; }
    .inv-actions { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }

    .inv-numbers { padding: 0 0 10px; }
    .inv-numbers-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 18px; margin-top: -46px; position: relative; z-index: 5; }
    .inv-number { background: #fff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 24px 20px; text-align: center; box-shadow: 0 12px 30px rgba(15,23,42,0.07); }
    .inv-number .val { font-size: 1.5rem; font-weight: 900; color: #0f172a; }
    .inv-number .lbl { font-size: 0.82rem; color: #64748b; margin-top: 4px; }

    .inv-section { padding: 74px 0; }
    .inv-section.alt { background: #f8fafc; }
    .inv-section .section-title h2 { color: #0f172a; }
    .inv-section .section-title p { color: #64748b; }
    .inv-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px; margin-top: 34px; }
    .inv-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 28px 26px; }
    .inv-card .ic { width: 54px; height: 54px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; border-radius: 16px; background: linear-gradient(135deg, rgba(16,185,129,0.12), rgba(37,99,235,0.12)); margin-bottom: 14px; }
    .inv-card h3 { font-size: 1.02rem; font-weight: 700; color: #0f172a; margin-bottom: 6px; }
    .inv-card p { font-size: 0.88rem; color: #64748b; }

    .inv-steps { counter-reset: step; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; margin-top: 34px; }
    .inv-step { position: relative; background: #fff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 30px 24px 26px; counter-increment: step; }
    .inv-step::before { content: counter(step); display: flex; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: 12px; background: var(--c-primary); color: #fff; font-weight: 800; margin-bottom: 12px; }
    .inv-step p { font-size: 0.9rem; color: #475569; }
    .inv-step-title { font-size: 0.95rem; font-weight: 700; color: #0f172a; margin-bottom: 4px; }

    .inv-cta { padding: 74px 0; background: linear-gradient(160deg, var(--c-hero-start) 0%, var(--c-hero-mid) 60%, var(--c-hero-end) 130%); color: #fff; text-align: center; }
    .inv-cta h2 { font-size: clamp(1.6rem, 3.4vw, 2.4rem); font-weight: 900; margin-bottom: 12px; }
    .inv-cta p { color: #b6c2d9; max-width: 620px; margin: 0 auto 26px; }
</style>

@include('core::landing.partials.header')

<section class="inv-hero">
    <div class="container">
        <span class="inv-crumb"><a href="{{ route('landing.index') }}">Home</a> <span>/</span> <strong>Investidores</strong></span>
        <h1>{{ $investor['title'] }}</h1>
        @if($investor['subtitle'])
            <p>{{ $investor['subtitle'] }}</p>
        @endif
        <div class="inv-actions">
            @if($whatsapp)
                <a class="btn btn-primary" href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}?text=Tenho%20interesse%20em%20ser%20investidor%20da%20{{ urlencode($name) }}">Quero ser investidor</a>
            @endif
            <a class="btn btn-outline" href="{{ route('crm.portal.login') }}">Portal do Cliente</a>
        </div>
    </div>
</section>

@if($investor['numbers']->count())
<div class="inv-numbers">
    <div class="container">
        <div class="inv-numbers-grid">
            @foreach($investor['numbers'] as $number)
                <div class="inv-number">
                    <div class="val">{{ $number['value'] }}</div>
                    <div class="lbl">{{ $number['label'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif

@if($investor['benefits']->count())
<section class="inv-section">
    <div class="container">
        <div class="section-title">
            <h2>Por que ser investidor {{ $name }}?</h2>
            <p>Modelo de negocio pronto, com suporte centralizado e marca establishida.</p>
        </div>
        <div class="inv-grid">
            @foreach($investor['benefits'] as $index => $benefit)
                @php $icons = ['💰', '🚀', '🛡️', '🤝', '📈', '🏷️']; @endphp
                <div class="inv-card">
                    <div class="ic">{{ $icons[$index % count($icons)] }}</div>
                    <p>{{ $benefit }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if($investor['steps']->count())
<section class="inv-section alt">
    <div class="container">
        <div class="section-title">
            <h2>Como virar investidor</h2>
            <p>Do primeiro contato ao primeiro cliente em 4 passos.</p>
        </div>
        <div class="inv-steps">
            @foreach($investor['steps'] as $step)
                @php
                    $clean = trim(preg_replace('/^\d+[.)-]?\s*/', '', $step));
                    [$stepTitle, $stepText] = str_contains($clean, ' - ')
                        ? array_map('trim', explode(' - ', $clean, 2))
                        : ['', $clean];
                @endphp
                <div class="inv-step">
                    @if($stepTitle)
                        <div class="inv-step-title">{{ $stepTitle }}</div>
                    @endif
                    <p>{{ $stepText }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="inv-cta">
    <div class="container">
        <h2>{{ $investor['cta_title'] }}</h2>
        @if($investor['cta_text'])
            <p>{{ $investor['cta_text'] }}</p>
        @endif
        <div class="inv-actions">
            @if($whatsapp)
                <a class="btn btn-primary" href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}?text=Quero%20investir%20na%20{{ urlencode($name) }}">Falar com o time comercial</a>
            @endif
            <a class="btn btn-outline" href="{{ route('landing.sac') }}">Falar com o SAC</a>
        </div>
    </div>
</section>

@include('core::landing.partials.footer')

</body>
</html>
