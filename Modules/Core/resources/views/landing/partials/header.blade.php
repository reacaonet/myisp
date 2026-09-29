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
        <nav class="site">
            
            @if($about ?? false)
                <a class="nav-link {{ request()->routeIs('landing.index') ? 'active' : '' }}" href="{{ route('landing.index') }}#sobre">Sobre</a>
            @endif
            @if($investors_enabled ?? true)
                <a class="nav-link {{ request()->routeIs('landing.investors') ? 'active' : '' }}" href="{{ route('landing.investors') }}">Investidores</a>
            @endif
            
            <a class="nav-link {{ request()->routeIs('landing.index') ? 'active' : '' }}" href="{{ route('landing.index') }}#para-voce">Para Voce</a>
            <a class="nav-link {{ request()->routeIs('landing.index') ? 'active' : '' }}" href="{{ route('landing.index') }}#empresas">Para Sua Empresa</a>
            <a class="nav-link {{ request()->routeIs('landing.index') ? 'active' : '' }}" href="{{ route('landing.index') }}#planos">Planos</a>
            <a class="nav-link {{ request()->routeIs('landing.sac') ? 'active' : '' }}" href="{{ route('landing.sac') }}">SAC</a>
            <a class="btn btn-primary" href="{{ route('crm.portal.login') }}">Area do Cliente</a>
        </nav>
    </div>
</header>