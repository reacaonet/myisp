<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📡</text></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
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
            --radius: 18px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Outfit', 'Segoe UI', system-ui, -apple-system, Arial, sans-serif; color: #1e293b; line-height: 1.6; background: #fff; -webkit-font-smoothing: antialiased; }
        ::selection { background: var(--c-primary); color: #fff; }
        img { max-width: 100%; }
        .container { max-width: 1180px; margin: 0 auto; padding: 0 24px; }
        a { text-decoration: none; }

        /* ------------------------------------------------------------------ */
        /* Buttons                                                             */
        /* ------------------------------------------------------------------ */
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 13px 26px; border-radius: 999px; font-weight: 700; font-size: 0.92rem; text-decoration: none; border: none; cursor: pointer; transition: transform 0.18s, box-shadow 0.2s, background 0.2s, filter 0.2s; }
        .btn:hover { transform: translateY(-2px); }
        .btn-primary { background: linear-gradient(135deg, var(--c-secondary), var(--c-primary)); color: #fff; box-shadow: 0 10px 26px rgba(37,99,235,0.35); }
        .btn-primary:hover { box-shadow: 0 14px 32px rgba(37,99,235,0.45); filter: brightness(1.06); }
        .btn-outline { background: rgba(255,255,255,0.08); color: #fff; border: 2px solid rgba(255,255,255,0.35); backdrop-filter: blur(4px); }
        .btn-outline:hover { background: rgba(255,255,255,0.16); }
        .btn-dark { background: var(--c-dark); color: #fff; }
        .btn-light { background: #fff; color: var(--c-primary); box-shadow: 0 8px 22px rgba(0,0,0,0.18); }
        .btn-light:hover { background: #f1f5f9; }
        .btn-whatsapp { background: linear-gradient(135deg, #25d366, #1da851); color: #fff; box-shadow: 0 10px 24px rgba(37,211,102,0.35); }
        .btn-whatsapp:hover { filter: brightness(1.06); }
        .btn-block { display: flex; width: 100%; }

        /* ------------------------------------------------------------------ */
        /* Header                                                              */
        /* ------------------------------------------------------------------ */
        header.site { position: sticky; top: 0; z-index: 60; background: rgba(255,255,255,0.86); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); border-bottom: 1px solid rgba(226,232,240,0.8); }
        header.site .container { display: flex; align-items: center; justify-content: space-between; height: 76px; }
        .brand { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .brand img { height: 62px; width: auto; }
        .brand span { font-size: 1.25rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; }
        nav.site { display: flex; align-items: center; gap: 26px; }
        nav.site a.nav-link { position: relative; color: #475569; text-decoration: none; font-size: 0.92rem; font-weight: 600; transition: color 0.2s; padding: 4px 0; }
        nav.site a.nav-link::after { content: ''; position: absolute; left: 0; bottom: -2px; width: 100%; height: 2px; border-radius: 2px; background: linear-gradient(90deg, var(--c-secondary), var(--c-primary)); transform: scaleX(0); transform-origin: left; transition: transform 0.22s; }
        nav.site a.nav-link:hover { color: var(--c-primary); }
        nav.site a.nav-link:hover::after, nav.site a.nav-link.active::after { transform: scaleX(1); }
        nav.site a.nav-link.active { color: var(--c-primary); }
        nav.site .btn { padding: 11px 20px; font-size: 0.88rem; }

        /* ------------------------------------------------------------------ */
        /* Hero / Slider                                                       */
        /* ------------------------------------------------------------------ */
        .hero, section.hero, .hero-carousel { position: relative; overflow: hidden; background: linear-gradient(160deg, var(--c-hero-start) 0%, var(--c-hero-mid) 55%, var(--c-hero-end) 130%); }
        .hero-carousel::before, section.hero::before { content: ''; position: absolute; width: 520px; height: 520px; border-radius: 50%; background: radial-gradient(circle, rgba(96,165,250,0.28), transparent 65%); top: -160px; left: -120px; pointer-events: none; }
        .hero-carousel::after, section.hero::after { content: ''; position: absolute; width: 480px; height: 480px; border-radius: 50%; background: radial-gradient(circle, rgba(99,102,241,0.26), transparent 65%); bottom: -180px; right: -120px; pointer-events: none; }
        .hero-slides { display: flex; transition: transform 0.55s ease; min-height: 560px; }
        .hero-slide { flex: 0 0 100%; width: 100%; position: relative; overflow: hidden; display: flex; align-items: center; }
        .hero-slide.has-bg::before { content: ''; position: absolute; inset: 0; background-image: var(--bg, none); background-size: cover; background-position: center; opacity: 0.92; z-index: 0; }
        .hero-slide.has-bg::after { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(11,18,34,0.10) 0%, rgba(11,18,34,0.50) 100%); z-index: 1; }
        section.hero .container, .hero-slide .container { padding: 84px 24px 104px; text-align: center; position: relative; z-index: 2; width: 100%; }
        section.hero h1 { font-size: clamp(2.1rem, 4.8vw, 3.6rem); font-weight: 900; line-height: 1.12; margin-bottom: 18px; letter-spacing: -0.025em; color: #fff; text-shadow: 0 2px 20px rgba(11,18,34,0.5); }
        section.hero h1 .grada { background: linear-gradient(120deg, #93c5fd, #c7d2fe 60%, #a5b4fc); -webkit-background-clip: text; background-clip: text; color: transparent; }
        section.hero p.sub { font-size: clamp(1rem, 2vw, 1.2rem); color: #e2e8f0; max-width: 740px; margin: 0 auto 32px; text-shadow: 0 1px 12px rgba(11,18,34,0.6); }
        .hero-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(147,197,253,0.14); color: #dbeafe; border: 1px solid rgba(147,197,253,0.35); padding: 9px 20px; border-radius: 999px; font-size: 0.82rem; font-weight: 700; margin-bottom: 24px; letter-spacing: 0.04em; backdrop-filter: blur(4px); }
        .hero-slide.has-bg .hero-badge { background: rgba(11,18,34,0.35); border-color: rgba(255,255,255,0.45); color: #fff; text-shadow: 0 1px 6px rgba(11,18,34,0.5); }
        .city-select-wrap { max-width: 460px; margin: 0 auto 32px; text-align: left; position: relative; }
        .city-select-wrap label { display: block; font-size: 0.75rem; color: #94a3b8; margin-bottom: 8px; letter-spacing: 0.08em; text-transform: uppercase; }
        .city-select { width: 100%; padding: 15px 44px 15px 20px; border-radius: 14px; border: 1px solid rgba(147,197,253,0.4); background: rgba(255,255,255,0.98); color: #0f172a; font-size: 0.98rem; font-weight: 600; outline: none; cursor: pointer; box-shadow: 0 14px 34px rgba(0,0,0,0.3); appearance: none; font-family: inherit; }
        .city-select-wrap::after { content: '▾'; position: absolute; right: 20px; top: 45px; color: var(--c-primary); pointer-events: none; font-size: 1.1rem; }
        .hero-actions { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
        .slider-nav { position: absolute; top: 50%; transform: translateY(-50%); z-index: 5; width: 48px; height: 48px; border-radius: 50%; border: 1px solid rgba(255,255,255,0.28); background: rgba(255,255,255,0.1); color: #fff; font-size: 1.5rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s; backdrop-filter: blur(6px); }
        .slider-nav:hover { background: rgba(255,255,255,0.24); }
        .slider-nav.prev { left: 18px; }
        .slider-nav.next { right: 18px; }
        .slider-dots { position: absolute; bottom: 26px; left: 50%; transform: translateX(-50%); z-index: 5; display: flex; gap: 9px; }
        .slider-dots button { width: 10px; height: 10px; border-radius: 999px; border: none; background: rgba(255,255,255,0.35); cursor: pointer; transition: width 0.2s, background 0.2s; padding: 0; }
        .slider-dots button.active { width: 28px; background: #fff; }
        @media (max-width: 640px) { .slider-nav { display: none; } }

        /* ------------------------------------------------------------------ */
        /* Stats band                                                          */
        /* ------------------------------------------------------------------ */
        section.stats { background: #fff; padding: 34px 0; box-shadow: 0 1px 0 #eef2f7; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 18px; text-align: center; }
        .stat-item { padding: 8px; }
        .stat-item .ic { font-size: 1.4rem; display: block; margin-bottom: 4px; }
        .stat-item .num { font-size: 1.8rem; font-weight: 900; color: var(--c-primary); letter-spacing: -0.02em; }
        .stat-item .lbl { font-size: 0.78rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.06em; margin-top: 2px; }

        /* ------------------------------------------------------------------ */
        /* Coverage                                                           */
        /* ------------------------------------------------------------------ */
        section.coverage { padding: 60px 0 40px; }
        .chip-cloud { display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; max-width: 820px; margin: 0 auto; }
        .chip { background: #eff6ff; color: var(--c-primary); border: 1px solid #dbeafe; font-weight: 600; font-size: 0.85rem; padding: 9px 18px; border-radius: 999px; transition: transform 0.15s, box-shadow 0.2s; }
        .chip:hover { transform: translateY(-2px); box-shadow: 0 8px 18px rgba(37,99,235,0.12); }

        /* ------------------------------------------------------------------ */
        /* Section titles                                                      */
        /* ------------------------------------------------------------------ */
        .section-title { text-align: center; margin-bottom: 44px; }
        .section-title h2 { font-size: clamp(1.6rem, 3vw, 2.3rem); font-weight: 800; color: #0f172a; letter-spacing: -0.02em; }
        .section-title p { color: #64748b; margin-top: 8px; max-width: 640px; margin-left: auto; margin-right: auto; }

        /* ------------------------------------------------------------------ */
        /* Features                                                            */
        /* ------------------------------------------------------------------ */
        section.features { padding: 70px 0; }
        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 22px; margin-top: 8px; }
        .feature-card { background: #f8fafc; border: 1px solid #edf2f7; border-radius: 20px; padding: 32px 26px; text-align: center; transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s; }
        .feature-card:hover { transform: translateY(-5px); box-shadow: 0 18px 40px rgba(15,23,42,0.10); border-color: #dbeafe; }
        .feature-card .ic { width: 64px; height: 64px; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; border-radius: 18px; background: linear-gradient(135deg, rgba(99,102,241,0.12), rgba(37,99,235,0.12)); }
        .feature-card h3 { font-size: 1.05rem; margin-bottom: 7px; color: #0f172a; }
        .feature-card p { font-size: 0.88rem; color: #64748b; }

        /* ------------------------------------------------------------------ */
        /* VOD Stream                                                          */
        /* ------------------------------------------------------------------ */
        section.vod { padding: 90px 0 110px; background: linear-gradient(180deg, #fff 0%, #f1f5f9 100%); }
        .vod-panel { background: linear-gradient(155deg, var(--c-hero-start) 0%, var(--c-hero-mid) 45%, var(--c-hero-end) 120%); border-radius: 30px; padding: 56px 52px; color: #fff; position: relative; overflow: hidden; box-shadow: 0 30px 70px rgba(11,18,34,0.35); }
        .vod-panel::before { content: ''; position: absolute; width: 420px; height: 420px; border-radius: 50%; background: radial-gradient(circle, rgba(96,165,250,0.3), transparent 65%); top: -140px; right: -100px; }
        .vod-panel .section-title { margin-bottom: 40px; }
        .vod-panel .section-title h2 { color: #fff; }
        .vod-panel .section-title p { color: #a8b8d0; }
        .vod-show { display: grid; grid-template-columns: 1.25fr 0.85fr; gap: 30px; align-items: stretch; }
        .vod-mock { border-radius: 20px; overflow: hidden; border: 1px solid rgba(255,255,255,0.14); background: #0b1222; box-shadow: 0 24px 50px rgba(0,0,0,0.35); display: flex; flex-direction: column; }
        .vod-mock-bar { display: flex; align-items: center; gap: 7px; padding: 12px 16px; background: rgba(255,255,255,0.05); border-bottom: 1px solid rgba(255,255,255,0.1); }
        .vod-mock-bar > span { width: 11px; height: 11px; border-radius: 50%; }
        .vod-mock-bar > span:nth-child(1) { background: #f87171; }
        .vod-mock-bar > span:nth-child(2) { background: #fbbf24; }
        .vod-mock-bar > span:nth-child(3) { background: #34d399; }
        .vod-mock-live { margin-left: auto; display: inline-flex; align-items: center; gap: 6px; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.1em; color: #fbbf24; background: rgba(251,191,36,0.12); padding: 4px 12px; border-radius: 999px; }
        .vod-mock-live .dot { width: 7px; height: 7px; border-radius: 50%; background: #ef4444; animation: vodPulse 1.4s infinite; }
        @keyframes vodPulse { 0%,100% { opacity: 1; } 50% { opacity: 0.35; } }
        .vod-mock-screen { position: relative; flex: 1; min-height: 280px; display: flex; align-items: flex-end; padding: 26px; background: radial-gradient(600px 260px at 80% 10%, rgba(99,102,241,0.5), transparent 60%), linear-gradient(160deg, #1e293b, #0f172a); }
        .vod-mock-screen::before { content: ''; position: absolute; inset: 0; opacity: 0.25; background: radial-gradient(circle at 20% 20%, rgba(255,255,255,0.25), transparent 45%); }
        .vod-playing { display: flex; align-items: center; gap: 18px; position: relative; z-index: 2; }
        .vod-play-btn { width: 62px; height: 62px; flex-shrink: 0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; color: #0f172a; background: #fff; box-shadow: 0 12px 30px rgba(0,0,0,0.4); }
        .vod-playing-info .vod-badge { display: inline-block; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: #c7d2fe; background: rgba(147,197,253,0.16); border: 1px solid rgba(147,197,253,0.3); padding: 4px 12px; border-radius: 999px; margin-bottom: 8px; }
        .vod-playing-info h3 { font-size: 1.35rem; font-weight: 800; }
        .vod-playing-info p { font-size: 0.85rem; color: #a8b8d0; margin-top: 4px; }
        .vod-cats { display: flex; flex-direction: column; gap: 14px; }
        .vod-cat { display: flex; align-items: center; gap: 16px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); border-radius: 18px; padding: 18px 20px; transition: transform 0.18s, background 0.2s; }
        .vod-cat:hover { transform: translateY(-3px); background: rgba(255,255,255,0.1); }
        .vod-cat-ic { width: 52px; height: 52px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; border-radius: 14px; background: linear-gradient(135deg, rgba(147,197,253,0.2), rgba(99,102,241,0.2)); }
        .vod-cat div { flex: 1; }
        .vod-cat h3 { font-size: 1rem; font-weight: 700; }
        .vod-cat p { font-size: 0.8rem; color: #a8b8d0; }
        .vod-cat-tag { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #6ee7b7; background: rgba(52,211,153,0.14); border: 1px solid rgba(52,211,153,0.3); padding: 4px 12px; border-radius: 999px; white-space: nowrap; }
        .vod-devices { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 6px; }
        .vod-device { text-align: center; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; padding: 12px 6px; font-size: 1.25rem; }
        .vod-device small { display: block; font-size: 0.62rem; font-weight: 600; letter-spacing: 0.03em; color: #a8b8d0; margin-top: 4px; text-transform: uppercase; }
        .vod-cta { display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap; margin-top: 34px; padding-top: 30px; border-top: 1px solid rgba(255,255,255,0.1); }
        .vod-cta p { color: #c7d2fe; font-size: 0.92rem; }
        .vod-cta p strong { color: #fff; }
        @media (max-width: 860px) {
            .vod-show { grid-template-columns: 1fr; }
            .vod-panel { padding: 40px 26px; }
            .vod-cta { flex-direction: column; align-items: flex-start; }
        }

        /* ------------------------------------------------------------------ */
        /* Plans                                                               */
        /* ------------------------------------------------------------------ */
        section.plans { padding: 70px 0; background: linear-gradient(180deg, #fff 0%, #f1f5f9 100%); }
        .plans-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 22px; }
        .plan-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 22px; padding: 32px 28px; display: flex; flex-direction: column; position: relative; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: transform 0.2s, box-shadow 0.2s; }
        .plan-card:hover { transform: translateY(-5px); box-shadow: 0 22px 46px rgba(15,23,42,0.12); }
        .plan-card.featured { border: 2px solid var(--c-primary); box-shadow: 0 18px 46px rgba(37,99,235,0.16); }
        .plan-card .tag { position: absolute; top: -13px; left: 50%; transform: translateX(-50%); background: linear-gradient(135deg, var(--c-secondary), var(--c-primary)); color: #fff; font-size: 0.7rem; font-weight: 700; padding: 6px 18px; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.07em; box-shadow: 0 8px 18px rgba(37,99,235,0.35); }
        .plan-card .plan-icon { font-size: 1.6rem; margin-bottom: 8px; }
        .plan-card h3 { font-size: 1.2rem; font-weight: 800; color: #0f172a; }
        .plan-card .desc { font-size: 0.85rem; color: #64748b; margin: 4px 0 16px; min-height: 38px; }
        .plan-card .price { font-size: 2.1rem; font-weight: 900; color: var(--c-primary); margin-bottom: 4px; line-height: 1.1; letter-spacing: -0.02em; }
        .plan-card .price small { font-size: 0.9rem; font-weight: 500; color: #64748b; }
        .plan-card .speeds { display: flex; gap: 18px; margin: 16px 0 22px; border-top: 1px solid #eef2f7; padding-top: 16px; }
        .plan-card .speeds div { flex: 1; }
        .plan-card .speeds .lbl { font-size: 0.72rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; }
        .plan-card .speeds .val { font-size: 1.05rem; font-weight: 700; color: #0f172a; }
        .plan-card .btn { margin-top: auto; }

        /* ------------------------------------------------------------------ */
        /* About                                                               */
        /* ------------------------------------------------------------------ */
        section.about { padding: 80px 0; background: #fff; }
        .about-wrap { display: grid; grid-template-columns: 1fr 1fr; gap: 54px; align-items: center; }
        .about-wrap h2 { font-size: clamp(1.5rem, 3vw, 2.1rem); font-weight: 800; color: #0f172a; margin-bottom: 18px; letter-spacing: -0.02em; }
        .about-wrap p { color: #475569; margin-bottom: 14px; }
        .about-wrap ul { list-style: none; margin: 16px 0; }
        .about-wrap ul li { padding: 6px 0; color: #475569; }
        .about-wrap ul li::before { content: '✓'; color: #16a34a; font-weight: 700; margin-right: 10px; }
        .about-badge { display: inline-flex; align-items: center; gap: 8px; background: #eff6ff; color: var(--c-primary); padding: 8px 16px; border-radius: 10px; font-weight: 700; font-size: 0.9rem; margin-bottom: 20px; }
        .about-visual { background: linear-gradient(160deg, var(--c-hero-end), var(--c-hero-mid)); border-radius: 24px; padding: 44px 38px; color: #fff; position: relative; overflow: hidden; }
        .about-visual::after { content: ''; position: absolute; right: -60px; bottom: -60px; width: 240px; height: 240px; background: radial-gradient(circle, rgba(59,130,246,0.4), transparent 70%); }
        .about-visual h3 { font-size: 1.4rem; font-weight: 800; margin-bottom: 14px; }
        .about-visual p { color: #c7d2fe; }
        .about-visual .mega { font-size: 2.6rem; font-weight: 900; margin: 18px 0 4px; }

        /* ------------------------------------------------------------------ */
        /* FAQ                                                                 */
        /* ------------------------------------------------------------------ */
        section.faq { padding: 70px 0; background: #f8fafc; }
        .faq-list { max-width: 820px; margin: 0 auto; }
        .faq-item { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; margin-bottom: 12px; overflow: hidden; transition: box-shadow 0.2s, border-color 0.2s; }
        .faq-item[open] { border-color: #dbeafe; box-shadow: 0 10px 26px rgba(37,99,235,0.08); }
        .faq-item summary { display: flex; align-items: center; justify-content: space-between; padding: 18px 22px; cursor: pointer; font-weight: 600; color: #0f172a; font-size: 0.98rem; list-style: none; }
        .faq-item summary::-webkit-details-marker { display: none; }
        .faq-item summary .arrow { color: var(--c-primary); transition: transform 0.2s; flex-shrink: 0; margin-left: 14px; }
        .faq-item[open] summary .arrow { transform: rotate(180deg); }
        .faq-item .faq-answer { padding: 0 22px 18px; color: #64748b; font-size: 0.92rem; }

        /* ------------------------------------------------------------------ */
        /* Contact                                                             */
        /* ------------------------------------------------------------------ */
        section.contact { padding: 80px 0 92px; background: var(--c-dark); color: #e2e8f0; }
        .contact-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 18px; margin-top: 10px; }
        .contact-box { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.09); border-radius: 18px; padding: 26px 24px; transition: transform 0.18s, background 0.2s; }
        .contact-box:hover { transform: translateY(-3px); background: rgba(255,255,255,0.07); }
        .contact-box .ic { font-size: 1.6rem; margin-bottom: 12px; }
        .contact-box h4 { color: #fff; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 8px; }
        .contact-box p, .contact-box a { color: #cbd5e1; text-decoration: none; word-break: break-word; }
        .contact-box a:hover { color: #fff; text-decoration: underline; }
        .map-wrap { margin-top: 26px; border-radius: 18px; overflow: hidden; border: 1px solid rgba(255,255,255,0.1); }
        .map-wrap iframe { display: block; width: 100%; height: 320px; border: 0; }

        /* ------------------------------------------------------------------ */
        /* Footer                                                              */
        /* ------------------------------------------------------------------ */
        footer.site { background: var(--c-footer); color: #64748b; padding: 48px 0 26px; }
        .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 30px; margin-bottom: 34px; }
        .footer-grid h4 { color: #e2e8f0; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 16px; }
        .footer-grid a { display: block; color: #8194b0; text-decoration: none; font-size: 0.88rem; padding: 4px 0; transition: color 0.15s, padding-left 0.15s; }
        .footer-grid a:hover { color: #fff; padding-left: 4px; }
        .footer-brand img { height: 56px; width: auto; margin-bottom: 12px; }
        .footer-brand p { font-size: 0.88rem; max-width: 260px; }
        footer.site .socials { display: flex; gap: 10px; margin-top: 14px; }
        footer.site .socials a { display: flex; width: 40px; height: 40px; border-radius: 12px; background: rgba(255,255,255,0.08); color: #cbd5e1; align-items: center; justify-content: center; text-decoration: none; font-size: 0.95rem; transition: background 0.2s, transform 0.15s; }
        footer.site .socials a:hover { background: var(--c-primary); color: #fff; transform: translateY(-2px); }
        .footer-bottom { border-top: 1px solid rgba(255,255,255,0.07); padding-top: 22px; text-align: center; font-size: 0.82rem; }

        .fab { position: fixed; bottom: 24px; right: 24px; z-index: 70; }
        .fab a { display: flex; width: 58px; height: 58px; border-radius: 50%; background: #22c55e; color: #fff; text-decoration: none; align-items: center; justify-content: center; box-shadow: 0 12px 28px rgba(34,197,94,0.45); transition: transform 0.15s, box-shadow 0.2s; }
        .fab a:hover { transform: scale(1.1); box-shadow: 0 14px 34px rgba(34,197,94,0.55); }

        @media (max-width: 900px) {
            nav.site a.nav-link { display: none; }
            .about-wrap { grid-template-columns: 1fr; }
            .footer-grid { grid-template-columns: 1fr 1fr; }
            .container { padding: 0 18px; }
            .vod-panel { border-radius: 22px; }
        }
        @media (max-width: 520px) {
            .footer-grid { grid-template-columns: 1fr; }
            .brand span { display: none; }
        }
        .empty-note { text-align: center; color: #64748b; font-size: 0.95rem; }
    </style>
</head>