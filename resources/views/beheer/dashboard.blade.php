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
                <div class="melding-succes">{{ session('succes') }}</div>
            </div>
        @endif

        @if (session('fout'))
            <div class="kolom-vol">
                <div class="login-error">{{ session('fout') }}</div>
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
                            @if ($collega->profielfoto_pad)
                                <img src="{{ asset('storage/' . $collega->profielfoto_pad) }}" alt=""
                                    style="width:48px;height:48px;border-radius:50%;object-fit:cover;flex:none;">
                            @endif
                            <div class="les-info">
                                <strong>{{ $collega->voornaam }} {{ $collega->achternaam }}</strong>

                                <a href="mailto:{{ $collega->email }}" class="collega-email">{{ $collega->email }}</a>

                                <span>
                                    {{ $collega->rol }} &middot;
                                    @if ($collega->geactiveerd_op)
                                        <span class="status-pill">Actief</span>
                                    @else
                                        <span class="status-pill status-pill-wacht">Geen wachtwoord</span>
                                    @endif
                                </span>

                                <form method="POST" action="{{ route('beheer.collegas.foto', $collega) }}"
                                    enctype="multipart/form-data" style="margin-top:.4rem;">
                                    @csrf
                                    <input type="file" name="profielfoto" accept="image/jpeg,image/png,image/webp"
                                        aria-label="Foto van {{ $collega->voornaam }} {{ $collega->achternaam }}">
                                    <button type="submit" class="btn btn-rand">Foto opslaan</button>
                                    @if ($collega->profielfoto_pad)
                                        <label style="font-size:.85rem;">
                                            <input type="checkbox" name="verwijder_foto" value="1"> Foto weghalen
                                        </label>
                                    @endif
                                </form>
                            </div>

                            {{-- Je eigen account kun je niet verwijderen --}}
                            @if ($collega->id !== auth('beheer')->id())
                                <div class="les-acties">
                                    <form method="POST" action="{{ route('beheer.collegas.verwijderen', $collega) }}"
                                        onsubmit="return confirm({{ Js::from('Weet je zeker dat je het account van ' . $collega->voornaam . ' ' . $collega->achternaam . ' wilt verwijderen? Deze persoon kan daarna niet meer inloggen.') }});">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="icoon-knop icoon-gevaar" title="Verwijderen"
                                            aria-label="Account van {{ $collega->voornaam }} {{ $collega->achternaam }} verwijderen">
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <polyline points="3 6 5 6 21 6" />
                                                <path
                                                    d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                                <line x1="10" y1="11" x2="10" y2="17" />
                                                <line x1="14" y1="11" x2="14" y2="17" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            @endif
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

            <form method="POST" action="{{ route('beheer.collegas.opslaan') }}" enctype="multipart/form-data">
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

                <div class="form-rij form-rij-2">
                    <div class="veld">
                        <label for="password">Wachtwoord (minimaal 8 tekens)</label>
                        <input type="password" id="password" name="password" class="veld-input"
                            autocomplete="new-password" minlength="8" required>
                        @error('password') <span class="veld-fout">{{ $message }}</span> @enderror
                    </div>

                    <div class="veld">
                        <label for="password_confirmation">Herhaal wachtwoord</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            class="veld-input" autocomplete="new-password" minlength="8" required>
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

                <div class="form-rij">
                    <div class="veld">
                        <label for="profielfoto">Foto (optioneel, jpg/png/webp, max 2 MB)</label>
                        <input type="file" id="profielfoto" name="profielfoto" accept="image/jpeg,image/png,image/webp"
                            class="veld-input">
                        @error('profielfoto') <span class="veld-fout">{{ $message }}</span> @enderror
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