<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account activeren - Sportschool De Linde</title>
    @vite(['resources/css/app.css'])
</head>
<body>
    @include('partials.nav')

    <div class="login-container">
        <div class="login-card">

            <h1 class="login-title">Account activeren</h1>

            @if ($geldig)
                <p style="margin-bottom: 1rem;">
                    Hallo {{ $gebruiker->voornaam }}, kies een wachtwoord om je account te activeren.
                </p>

                @if ($errors->any())
                    <div class="login-error">
                        @foreach ($errors->all() as $fout)
                            <div>{{ $fout }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('account.activeren.opslaan', $token) }}">
                    @csrf

                    <div class="login-group">
                        <label for="password" class="login-label">Wachtwoord (minimaal 8 tekens)</label>
                        <input type="password" id="password" name="password" class="login-input"
                               autocomplete="new-password" minlength="8" required autofocus>
                    </div>

                    <div class="login-group">
                        <label for="password_confirmation" class="login-label">Herhaal wachtwoord</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="login-input" autocomplete="new-password" minlength="8" required>
                    </div>

                    <button type="submit" class="login-button">Account activeren</button>
                </form>
            @else
                <div class="login-error">
                    Deze link is ongeldig of verlopen. Vraag de beheerder om een nieuwe uitnodiging.
                </div>
            @endif

        </div>
    </div>

    @include('partials.footer')
</body>
</html>