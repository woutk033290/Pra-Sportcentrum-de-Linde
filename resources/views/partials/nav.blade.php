<header class="site-nav">
    <div class="site-nav-links">
        <a href="{{ url('/') }}" class="site-nav-logo">logo</a>

        {{-- Vervang de # door de routes van de lespagina's zodra die bestaan --}}
        <a href="#">spinning</a>
        <a href="#">Yoga</a>
        <a href="#">bootcamp</a>
        <a href="#">aquagym</a>
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
    @else
        <a href="{{ route('login') }}">inlog</a>
    @endif
</div>
</header>