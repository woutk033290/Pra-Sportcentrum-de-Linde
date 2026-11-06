@php
    $actief = $actief ?? 'login';
@endphp

<nav class="account-tabs" aria-label="Account">
    <a href="{{ route('login') }}"
       class="account-tab {{ $actief === 'login' ? 'account-tab-actief' : '' }}"
       @if ($actief === 'login') aria-current="page" @endif>Inloggen</a>

    <a href="{{ route('registreren') }}"
       class="account-tab {{ $actief === 'registreren' ? 'account-tab-actief' : '' }}"
       @if ($actief === 'registreren') aria-current="page" @endif>Nieuw account maken</a>
</nav>