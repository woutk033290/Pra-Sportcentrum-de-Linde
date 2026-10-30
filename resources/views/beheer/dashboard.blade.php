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

    <main class="beheer-lessen">

        <div class="kaart kolom-vol">
            <h1>Beheergedeelte</h1>
            <p>Welkom, {{ auth('beheer')->user()->voornaam }}. Je bent ingelogd als beheerder. Hier komen de
                overzichten.</p>
            <a href="{{ route('beheer.lessen') }}" class="btn" style="margin-top: 1rem;">Lessen beheren</a>
        </div>

        @if (session('succes'))
            <div class="kolom-vol">
                <div class="melding-succes">
                    {{ session('succes') }}

                    @if (session('uitnodigingslink'))
                        <input type="text" readonly value="{{ session('uitnodigingslink') }}" onclick="this.select()"
                            class="veld-input uitnodiging-link" aria-label="Activatielink">
                    @endif
                </div>
            </div>
        @endif

        {{-- Links: collega's --}}
        <aside class="paneel">
            <h2 class="paneel-titel">Collega's</h2>

            @if ($collegas->isEmpty())
                <p class="lijst-leeg">Nog geen collega's.</p>
            @else
                <ul class="les-lijst">
                    @foreach ($collegas as $collega)
                        <li>
                            <div class="les-info">
                                <strong>{{ $collega->voornaam }} {{ $collega->achternaam }}</strong>

                                <a href="mailto:{{ $collega->email }}" class="collega-email">{{ $collega->email }}</a>

                                <span>
                                    {{ $collega->rol }} &middot;
                                    @if ($collega->geactiveerd_op)
                                        <span class="status-pill">Actief</span>
                                    @else
                                        <span class="status-pill status-pill-wacht">Wacht op activatie</span>
                                    @endif
                                </span>
                            </div>

                            @unless ($collega->geactiveerd_op)
                                <form method="POST" action="{{ route('beheer.collegas.uitnodiging', $collega) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-rand btn-klein">Nieuwe link</button>
                                </form>
                            @endunless
                        </li>
                    @endforeach
                </ul>
            @endif
        </aside>

        {{-- Rechts: nieuwe collega --}}
        <section class="paneel">
            <h2 class="paneel-titel">Nieuwe collega toevoegen</h2>

            @if ($errors->any())
                <div class="login-error">
                    Controleer het formulier: er ging iets mis bij het opslaan.
                </div>
            @endif

            <form method="POST" action="{{ route('beheer.collegas.opslaan') }}">
                @csrf

                <div class="form-rij form-rij-2">
                    <div class="veld">
                        <label for="voornaam">Voornaam</label>
                        <input type="text" id="voornaam" name="voornaam" value="{{ old('voornaam') }}"
                            class="veld-input" required>
                        @error('voornaam') <span class="veld-fout">{{ $message }}</span> @enderror
                    </div>

                    <div class="veld">
                        <label for="achternaam">Achternaam</label>
                        <input type="text" id="achternaam" name="achternaam" value="{{ old('achternaam') }}"
                            class="veld-input" required>
                        @error('achternaam') <span class="veld-fout">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-rij form-rij-2">
                    <div class="veld">
                        <label for="email">E-mailadres</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" class="veld-input"
                            required>
                        @error('email') <span class="veld-fout">{{ $message }}</span> @enderror
                    </div>

                    <div class="veld">
                        <label for="telefoonnummer">Telefoonnummer</label>
                        <input type="tel" id="telefoonnummer" name="telefoonnummer" value="{{ old('telefoonnummer') }}"
                            class="veld-input" required>
                        @error('telefoonnummer') <span class="veld-fout">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-rij">
                    <div class="veld">
                        <label for="rol">Rol</label>
                        <select id="rol" name="rol" class="veld-input" required>
                            <option value="">Kies</option>
                            <option value="trainer" @selected(old('rol') === 'trainer')>Trainer</option>
                            <option value="beheerder" @selected(old('rol') === 'beheerder')>Beheerder</option>
                        </select>
                        @error('rol') <span class="veld-fout">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-rij">
                    <div class="veld">
                        <label for="trainer_bio">Korte intro (alleen voor trainers, optioneel)</label>
                        <textarea id="trainer_bio" name="trainer_bio" rows="3"
                            class="veld-input">{{ old('trainer_bio') }}</textarea>
                        @error('trainer_bio') <span class="veld-fout">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-knoppen">
                    <button type="submit" class="btn">Collega toevoegen</button>
                </div>
            </form>
        </section>
    </main>

    @include('partials.footer')
</body>

</html>