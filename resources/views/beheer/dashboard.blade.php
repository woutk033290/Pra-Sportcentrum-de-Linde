<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Beheer - Sportschool De Linde</title>
    @vite(['resources/css/app.css'])
</head>
<body>
    <header class="beheer-header">
        <strong>Beheergedeelte</strong>

        <form method="POST" action="{{ route('beheer.uitloggen') }}">
            @csrf
            <button type="submit" class="login-button">Uitloggen</button>
        </form>
    </header>

    <main class="beheer-main">
        <h1>Welkom, {{ auth('beheer')->user()->voornaam }}</h1>
        <p>Je bent ingelogd als beheerder. Hier komen de overzichten.</p>
        <a href="{{ route('beheer.lessen') }}" class="btn" style="margin-top: 1rem;">Lessen beheren</a>
    </main>
</body>
</html>