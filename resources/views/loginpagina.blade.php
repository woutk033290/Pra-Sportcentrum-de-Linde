<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inloggen - Sportschool De Linde</title>
    @vite(['resources/css/app.css'])
</head>
<body>
    @include('partials.nav')

    <div class="login-container">
        <div class="login-card">

            <a href="{{ url('/') }}" class="terug-knop">&larr; Terug</a>

            <h1 class="login-title">Inloggen</h1>

            @if (session('error'))
                <div class="login-error">{{ session('error') }}</div>
            @endif

            @if ($errors->any())
                <div class="login-error">
                    @foreach ($errors->all() as $fout)
                        <div>{{ $fout }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login.verwerken') }}">
                @csrf

                <div class="login-group">
                    <label for="email" class="login-label">E-mailadres</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="login-input"
                        autocomplete="username"
                        required
                        autofocus
                    >
                </div>

                <div class="login-group">
                    <label for="password" class="login-label">Wachtwoord</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="login-input"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button type="submit" class="login-button">Inloggen</button>
            </form>

        </div>
    </div>
    @include('partials.footer')
</body>
</html>