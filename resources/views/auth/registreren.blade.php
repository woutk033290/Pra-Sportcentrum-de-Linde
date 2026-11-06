<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account aanmaken - Sportschool De Linde</title>
    @vite(['resources/css/app.css'])
</head>

<body>
    @include('partials.nav')
    <main class="mx-auto grid w-full max-w-6xl flex-1 items-start gap-8 p-4 sm:p-8 lg:grid-cols-[1.3fr_1fr]">
        <section class="paneel">
            @include('partials.account-tabs', ['actief' => 'registreren'])

            <h1 class="login-title">Account aanmaken</h1>
            <p class="mb-6">Maak een account om lessen te kunnen reserveren.</p>
            <form method="POST" action="{{ route('registreren.store') }}" class="grid gap-5" enctype="multipart/form-data" novalidate>
                @csrf
                <div class="grid gap-5 sm:grid-cols-2">
                    <div><x-input name="voornaam" label="Voornaam" class="login-input" autocomplete="given-name"
                            maxlength="255" required /></div>
                    <div><x-input name="achternaam" label="Achternaam" class="login-input" autocomplete="family-name"
                            maxlength="255" required /></div>
                    <div><x-input name="email" label="E-mailadres" type="email" class="login-input" autocomplete="email"
                            maxlength="255" required /></div>
                    <div><x-input name="telefoonnummer" label="Telefoonnummer (optioneel)" type="tel"
                            class="login-input" autocomplete="tel" maxlength="255" /></div>
                    <div><x-input name="password" label="Wachtwoord (minimaal 8 tekens)" type="password"
                            class="login-input" autocomplete="new-password" minlength="8" maxlength="72" required />
                    </div>
                    <div><x-input name="password_confirmation" label="Herhaal wachtwoord" type="password"
                            class="login-input" autocomplete="new-password" required /></div>
                </div>
                <div><x-input name="geboortedatum" label="Geboortedatum (optioneel)" type="date" class="login-input"
                        autocomplete="bday" max="{{ now()->format('Y-m-d') }}" /></div>
                <div>
                    <label class="block text-sm font-semibold" for="profielfoto">Profielfoto (optioneel)
                        <input id="profielfoto" name="profielfoto" type="file" accept="image/jpeg,image/png,image/webp"
                            class="login-input" @if($errors->has('profielfoto')) aria-invalid="true"
                            aria-describedby="profielfoto-error" @endif>
                    </label>
                    @error('profielfoto')
                    <p id="profielfoto-error" class="text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="flex items-start gap-3" for="voorwaarden">
                        <input id="voorwaarden" name="voorwaarden" type="checkbox" value="1" class="mt-1 size-4"
                            @checked(old('voorwaarden')) required @if($errors->has('voorwaarden')) aria-invalid="true"
                            aria-describedby="voorwaarden-error" @endif>
                        <span>Ik ga akkoord met de voorwaarden</span>
                    </label>
                    @error('voorwaarden')
                    <p id="voorwaarden-error" class="text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="login-button justify-self-start">Account maken</button>
                <a class="text-sm text-blue-700 underline" href="{{ route('login') }}">Al een account? Inloggen</a>
            </form>
        </section>
        <aside
            class="flex min-h-72 flex-col justify-center gap-6 overflow-hidden rounded-xl bg-blue-100 p-8 lg:min-h-[36rem]"
            aria-label="Sporten bij De Linde">
            <svg viewBox="0 0 320 240" class="mx-auto w-full max-w-xs text-blue-700" fill="none" aria-hidden="true">
                <circle cx="160" cy="120" r="105" fill="white" opacity=".7" />
                <circle cx="190" cy="60" r="17" fill="currentColor" />
                <path
                    d="M176 92L150 142M176 92L204 114L232 100M176 92L146 104L120 126M150 142L190 160L180 204M150 142L124 176L92 162"
                    stroke="currentColor" stroke-width="15" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <h2 class="text-2xl font-semibold text-blue-900">Samen in beweging.</h2>
            <p>Spinning, yoga, bootcamp of aquagym: jouw volgende les begint bij De Linde.</p>
        </aside>
    </main>
    @include('partials.footer')
</body>

</html>