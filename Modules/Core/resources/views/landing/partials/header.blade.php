<body>

<header class="site">
    <div class="container">
        <a class="brand" href="{{ route('landing.index') }}">
            @if($logo)
                <img src="{{ asset('storage/'.$logo) }}" alt="{{ $name }}">
            @else
                <span>{{ $name }}</span>
            @endif
        </a>
        <nav class="site" id="landing-nav">
            {{-- Itens com # sao ancoras da propria home. Nao podem decidir o
                 active pela rota: as quatro respondem a landing.index e
                 sublinhavam todas de uma vez. Quem marca a secao em tela e o
                 script no fim deste arquivo. --}}
            @if($about ?? false)
                <a class="nav-link" data-section="sobre" href="{{ route('landing.index') }}#sobre">Sobre</a>
            @endif
            @if($investors_enabled ?? true)
                <a class="nav-link {{ request()->routeIs('landing.investors') ? 'active' : '' }}" href="{{ route('landing.investors') }}">Investidores</a>
            @endif

            <a class="nav-link" data-section="para-voce" href="{{ route('landing.index') }}#para-voce">Para Voce</a>
            <a class="nav-link" data-section="empresas" href="{{ route('landing.index') }}#empresas">Para Sua Empresa</a>
            <a class="nav-link" data-section="planos" href="{{ route('landing.index') }}#planos">Planos</a>
            <a class="nav-link {{ request()->routeIs('landing.sac') ? 'active' : '' }}" href="{{ route('landing.sac') }}">SAC</a>
            <a class="btn btn-primary" href="{{ route('crm.portal.login') }}">Area do Cliente</a>
        </nav>

        {{-- Ate 900px o menu vira painel. Sem este botao os itens de link
             ficavam escondidos pelo CSS e o celular so mostrava a logo. --}}
        <button class="nav-toggle" type="button" aria-label="Abrir menu" aria-expanded="false" aria-controls="landing-nav">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>

<script>
    // Um item ativo por vez. As ancoras da home sao marcadas pela secao que
    // esta em tela, e nao pela rota: quatro delas apontam para landing.index e
    // a rota nao diz qual delas o leitor esta vendo.
    document.addEventListener('DOMContentLoaded', function () {
        var nav = document.querySelector('nav.site');
        if (!nav) return;

        // Painel do celular. Fica aberto so ate a proxima escolha: link
        // clicado fecha, Escape fecha, clique fora fecha, e voltar para o
        // desktop fecha, senao o menu fica preso aberto na tela larga.
        var toggle = document.querySelector('.nav-toggle');

        if (toggle) {
            function abrir(aberto) {
                nav.classList.toggle('is-open', aberto);
                toggle.setAttribute('aria-expanded', aberto ? 'true' : 'false');
                toggle.setAttribute('aria-label', aberto ? 'Fechar menu' : 'Abrir menu');
            }

            toggle.addEventListener('click', function () {
                abrir(toggle.getAttribute('aria-expanded') !== 'true');
            });

            nav.addEventListener('click', function (e) {
                if (e.target.closest('a')) abrir(false);
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') abrir(false);
            });

            document.addEventListener('click', function (e) {
                if (!nav.contains(e.target) && !toggle.contains(e.target)) abrir(false);
            });

            window.addEventListener('resize', function () {
                if (window.innerWidth > 900) abrir(false);
            });
        }

        var links = Array.prototype.slice.call(nav.querySelectorAll('a.nav-link[data-section]'));
        if (!links.length) return;

        var sections = links
            .map(function (link) { return document.getElementById(link.dataset.section); })
            .filter(Boolean);

        function mark(sectionId) {
            links.forEach(function (link) {
                var on = !!sectionId && link.dataset.section === sectionId;
                link.classList.toggle('active', on);
                if (on) {
                    link.setAttribute('aria-current', 'true');
                } else {
                    link.removeAttribute('aria-current');
                }
            });
        }

        // Enquanto o mouse passa por cima, o item apontado assume o
        // sublinhado e a secao em tela sai. Sem isso aparecem dois ao mesmo
        // tempo, que e o mesmo defeito em outra hora.
        nav.addEventListener('mouseenter', function () { nav.classList.add('is-hovering'); });
        nav.addEventListener('mouseleave', function () { nav.classList.remove('is-hovering'); });

        if (!sections.length) return;

        // Quem entra por /#planos ja precisa ver Planos sublinhado, antes do
        // primeiro scroll. Sem hash nao ha secao: a home comeca no hero, que
        // nao e item de menu.
        mark((location.hash || '').replace('#', '') || null);

        links.forEach(function (link) {
            link.addEventListener('click', function () { mark(link.dataset.section); });
        });

        var agendado = null;
        var linhaDoHeader = 100;

        window.addEventListener('scroll', function () {
            if (agendado) return;

            agendado = requestAnimationFrame(function () {
                agendado = null;

                var atual = null;
                sections.forEach(function (section) {
                    // Secao que ja subiu acima da linha do header. A ultima
                    // que passou conta: e o trecho que o leitor esta vendo.
                    if (section.getBoundingClientRect().top - linhaDoHeader <= 0) {
                        atual = section.id;
                    }
                });

                mark(atual);
            });
        }, { passive: true });
    });
</script>
