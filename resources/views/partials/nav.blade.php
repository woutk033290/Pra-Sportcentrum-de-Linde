<header class="site-nav">
    <div class="site-nav-links">
        <a href="{{ url('/') }}" class="site-nav-logo">logo</a>

        <a href="{{ route('lestype.tonen', 'spinning') }}" class="{{ request()->is('lessen/spinning') ? 'nav-actief' : '' }}">spinning</a>
        <a href="{{ route('lestype.tonen', 'yoga') }}" class="{{ request()->is('lessen/yoga') ? 'nav-actief' : '' }}">Yoga</a>
        <a href="{{ route('lestype.tonen', 'bootcamp') }}" class="{{ request()->is('lessen/bootcamp') ? 'nav-actief' : '' }}">bootcamp</a>
        <a href="{{ route('lestype.tonen', 'aquagym') }}" class="{{ request()->is('lessen/aquagym') ? 'nav-actief' : '' }}">aquagym</a>
        <a href="{{ route('rooster') }}" class="{{ request()->routeIs('rooster') ? 'nav-actief' : '' }}">rooster</a>
    </div>

    <div class="site-nav-account">
        @if (auth('beheer')->check())
            <a href="{{ route('beheer.dashboard') }}">admin</a>
            <span aria-hidden="true">&middot;</span>

            <form method="POST" action="{{ route('beheer.uitloggen') }}">
                @csrf
                <button type="submit" class="site-nav-knop">uitloggen</button>
            </form>
        @elseif (auth('web')->check())
            @if (auth('web')->user()->rol === 'lid')
                <a href="{{ route('reserveringen.index') }}">Mijn reserveringen</a>
            @elseif (auth('web')->user()->rol === 'trainer')
                <a href="{{ route('trainer.dashboard') }}">Mijn lessen</a>
            @endif
            <span>{{ auth('web')->user()->voornaam }}</span>
            <form method="POST" action="{{ route('uitloggen') }}">
                @csrf
                <button type="submit" class="site-nav-knop">uitloggen</button>
            </form>
        @else
            <a href="{{ route('login') }}">inlog</a>
        @endif
    </div>
</header>