@include('core::landing.partials.head', ['title' => $name])

@include('core::landing.partials.header')

@if($banners->count())
<section class="hero-carousel">
    <div class="hero-slides" id="hero-slides">
        @foreach($banners as $banner)
        <div class="hero-slide {{ $banner->image ? 'has-bg' : '' }}" style="{{ $banner->image ? '--bg: url('.asset('storage/'.$banner->image).')' : '' }}">
            <div class="container">
                @php
                    $bannerTitleStyle = trim(($banner->title_font_size ? "font-size:{$banner->title_font_size}px;" : '') . ($banner->title_color ? "color:{$banner->title_color};" : ''));
                    $bannerSubStyle = trim(($banner->subtitle_font_size ? "font-size:{$banner->subtitle_font_size}px;" : '') . ($banner->subtitle_color ? "color:{$banner->subtitle_color};" : ''));
                @endphp
                @if($banner->badge)
                    <span class="hero-badge">✨ {{ $banner->badge }}</span>
                @endif
                @if($banner->highlight)
                    <h1 style="{{ $bannerTitleStyle }}">{!! $banner->title !!} <span class="grada">{{ $banner->highlight }}</span></h1>
                @else
                    <h1 style="{{ $bannerTitleStyle }}">{!! $banner->title !!}</h1>
                @endif
                @if($banner->subtitle)
                    <p class="sub" style="{{ $bannerSubStyle }}">{{ $banner->subtitle }}</p>
                @endif
                <div class="hero-actions">
                    @if($banner->link_url)
                        <a class="btn btn-outline" href="{{ $banner->link_url }}" target="_blank" rel="noopener">{{ $banner->link_label ?: 'Saiba mais' }}</a>
                    @endif
                    @if($whatsapp)
                        <a class="btn btn-primary" href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}">Falar no WhatsApp</a>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @if($banners->count() > 1)
    <button class="slider-nav prev" type="button" aria-label="Anterior" onclick="moveSlide(-1)">‹</button>
    <button class="slider-nav next" type="button" aria-label="Proximo" onclick="moveSlide(1)">›</button>
    <div class="slider-dots" id="slider-dots"></div>
    @endif
</section>
@else
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
@endif

<section class="stats">
    <div class="container">
        <div class="stats-grid">
            @if($cities->count())
            <div class="stat-item"><span class="ic">🏙️</span><div class="num"><span data-count="{{ $cities->count() }}">0</span></div><div class="lbl">Cidades Atendidas</div></div>
            @endif
            <div class="stat-item"><span class="ic">🌐</span><div class="num">100%</div><div class="lbl">Fibra Optica</div></div>
            <div class="stat-item"><span class="ic">🛟</span><div class="num">24h</div><div class="lbl">Suporte ao Cliente</div></div>
            <div class="stat-item"><span class="ic">🏠</span><div class="num">FTTH</div><div class="lbl">Na sua casa</div></div>
        </div>
    </div>
</section>

@if($cities->count())
<section class="coverage" id="cobertura">
    <div class="container">
        <div class="section-title">
            <h2>📍 {{ $titles['coverage_title'] }}</h2>
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
            <div class="feature-card"><div class="ic">📡</div><h3>Fibra Optica FTTH</h3><p>Tecnologia de ultima geracao chegando direto na sua casa.</p></div>
            <div class="feature-card"><div class="ic">🎬</div><h3>VOD Stream</h3><p>Filmes, series e canais abertos incluidos de graca no seu plano.</p></div>
            <div class="feature-card"><div class="ic">🛡️</div><h3>Suporte Dedicado</h3><p>SAC que resolve seu problema de verdade, pelos canais que voce preferir.</p></div>
        </div>
    </div>
</section>

<section class="vod" id="vod">
    <div class="container">
        <div class="vod-panel">
            <div class="section-title">
                <h2>🎬 {{ $titles['vod_title'] }}</h2>
                <p>{{ $titles['vod_subtitle'] }}</p>
            </div>

            <div class="vod-show">
                <div class="vod-mock">
                    <div class="vod-mock-bar">
                        <span></span><span></span><span></span>
                        <div class="vod-mock-live"><span class="dot"></span> AO VIVO</div>
                    </div>
                    <div class="vod-mock-screen">
                        <div class="vod-playing">
                            <div class="vod-play-btn">▶</div>
                            <div class="vod-playing-info">
                                <span class="vod-badge">Em destaque</span>
                                <h3>Cinema na sua sala</h3>
                                <p>Filmes, series e canais abertos com a qualidade da fibra optica.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="vod-cats">
                    <div class="vod-cat">
                        <div class="vod-cat-ic">🎬</div>
                        <div><h3>Filmes</h3><p>Lancamentos e classicos sob demanda.</p></div>
                        <span class="vod-cat-tag">Incluso</span>
                    </div>
                    <div class="vod-cat">
                        <div class="vod-cat-ic">📺</div>
                        <div><h3>Series</h3><p>Maratonas completas em alta definicao.</p></div>
                        <span class="vod-cat-tag">Incluso</span>
                    </div>
                    <div class="vod-cat">
                        <div class="vod-cat-ic">📡</div>
                        <div><h3>Canais Abertos</h3><p>TV ao vivo sem antena nem assinatura.</p></div>
                        <span class="vod-cat-tag">Incluso</span>
                    </div>
                    <div class="vod-devices">
                        <div class="vod-device">📺<small>TV</small></div>
                        <div class="vod-device">💻<small>Notebook</small></div>
                        <div class="vod-device">📱<small>Celular</small></div>
                        <div class="vod-device">📲<small>Tablet</small></div>
                    </div>
                </div>
            </div>

            <div class="vod-cta">
                <p><strong>Incluso em todos os planos</strong> — sem mensalidade extra. Assista onde e quando quiser.</p>
                <a class="btn btn-light" href="#planos">Quero VOD Stream</a>
            </div>
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

@include('core::landing.partials.footer')

<script>
    (function () {
        var slidesWrap = document.getElementById('hero-slides');
        if (slidesWrap) {
            var slides = slidesWrap.querySelectorAll('.hero-slide');
            var current = 0;

            var dotsWrap = document.getElementById('slider-dots');
            if (dotsWrap) {
                slides.forEach(function (_, i) {
                    var b = document.createElement('button');
                    b.type = 'button';
                    b.setAttribute('aria-label', 'Ir para o banner ' + (i + 1));
                    if (i === 0) b.classList.add('active');
                    b.addEventListener('click', function () { goTo(i); resetTimer(); });
                    dotsWrap.appendChild(b);
                });
            }
            var dots = dotsWrap ? dotsWrap.querySelectorAll('button') : [];

            function goTo(index) {
                current = (index + slides.length) % slides.length;
                slidesWrap.style.transform = 'translateX(-' + (current * 100) + '%)';
                if (dots.length) {
                    dots.forEach(function (d, i) {
                        d.classList.toggle('active', i === current);
                    });
                }
            }

            window.moveSlide = function (dir) {
                goTo(current + dir);
                resetTimer();
            };

            var timer = null;
            function resetTimer() {
                if (slides.length < 2) return;
                if (timer) clearInterval(timer);
                timer = setInterval(function () { goTo(current + 1); }, 6000);
            }
            resetTimer();
        }

        var counters = document.querySelectorAll('[data-count]');
        if (counters.length && 'IntersectionObserver' in window) {
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) return;
                    io.unobserve(entry.target);
                    var el = entry.target;
                    var target = parseInt(el.getAttribute('data-count'), 10) || 0;
                    var start = null;
                    var dur = 1400;
                    function step(ts) {
                        if (!start) start = ts;
                        var p = Math.min((ts - start) / dur, 1);
                        el.textContent = Math.floor(p * target);
                        if (p < 1) requestAnimationFrame(step);
                    }
                    requestAnimationFrame(step);
                });
            }, { threshold: 0.4 });
            counters.forEach(function (c) { io.observe(c); });
        }

        var sel = document.getElementById('sel-cidade');
        if (sel) {
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
        }
    })();
</script>

</body>
</html>