<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Les aanpassen - Sportschool De Linde</title>
    @vite(['resources/css/app.css'])
</head>
<body>
    @include('partials.nav')

    <main class="beheer-lessen beheer-lessen-enkel">
        <section class="paneel">
            <h2 class="paneel-titel">Les aanpassen</h2>

            @if ($groepsles->lesreeks_id)
                <p class="reeks-info">Deze les hoort bij een reeks. Alleen deze ene les wordt aangepast.</p>
            @endif

            @if ($errors->any())
                <div class="login-error">
                    Controleer het formulier: er ging iets mis bij het opslaan.
                </div>
            @endif

            @include('beheer._lesformulier', [
                'actie' => route('beheer.lessen.bijwerken', $groepsles),
                'les'   => $groepsles,
            ])
        </section>
    </main>

    @include('partials.footer')
</body>
</html>