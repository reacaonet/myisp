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
            <a class="nav-link {{ request()->routeIs('landing.sac') ? 'active' : '' }}" href="{{ route('landing.sac') }}">SAC</a>
            <a class="nav-link" href="{{ route('landing.index') }}#planos">Planos</a>
            <a class="nav-link" href="{{ route('landing.index') }}#vod">VOD Stream</a>
            <a class="nav-link" href="{{ route('landing.index') }}#cobertura">Cobertura</a>
            <a class="nav-link" href="{{ route('landing.index') }}#sobre">Sobre</a>
            <a class="btn btn-primary" href="{{ route('crm.portal.login') }}">Area do Cliente</a>
        </nav>
    </div>
</header>