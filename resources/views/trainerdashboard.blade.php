<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Beheer - Sportschool De Linde</title>
    @vite(['resources/css/app.css'])
</head>
<body>
    @include('partials.nav')

    <main class="beheer-main">
        <div class="kaart">
            <h1>Beheergedeelte</h1>
            <p>Welkom, {{ auth('beheer')->user()->voornaam }}. Je bent ingelogd als beheerder. Hier komen de overzichten.</p>
        </div>
    </main>

    @include('partials.footer')
</body>
</html>