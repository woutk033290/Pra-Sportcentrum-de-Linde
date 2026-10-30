<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $groepsles->naam }} - Sportschool De Linde</title>
    @vite(['resources/css/app.css'])
</head>
<body>
    @include('partials.nav')

    @php
        $dagNamen = ['Ma', 'Di', 'Wo', 'Do', 'Vr', 'Za', 'Zo'];
        $maanden  = ['jan', 'feb', 'mrt', 'apr', 'mei', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'dec'];

        $start = $groepsles->starttijd;
        $datumTekst = $dagNamen[$start->dayOfWeekIso - 1] . ' ' . $start->day . ' ' . $maanden[$start->month - 1];
        $tijdTekst  = $start->format('H:i') . ' – ' . $groepsles->eindtijd->format('H:i');

        $geannuleerd = $groepsles->status === 'geannuleerd';
        $voorbij     = $start->isPast();
        $trainerNaam = $groepsles->trainer->voornaam . ' ' . $groepsles->trainer->achternaam;
        $terugNaarRooster = route('rooster', ['week' => $start->copy()->startOfWeek()->toDateString()]);
    @endphp

    <main class="pagina">

        @if (session('succes'))
            <div class="melding-succes">{{ session('succes') }}</div>
        @endif

        @if (session('fout'))
            <div class="login-error">{{ session('fout') }}</div>
        @endif

        {{-- Bovenste deel: foto + info --}}
        <section class="les-detail">
            <div class="les-detail-foto">
                @if ($groepsles->foto_pad)
                    <img src="{{ asset('storage/' . $groepsles->foto_pad) }}" alt="{{ $groepsles->naam }}">
                @else
                    <span>foto</span>
                @endif
            </div>

            <div class="les-detail-info">
                <h1 class="les-detail-titel">{{ $groepsles->naam }}</h1>

                @if ($kanBeheren)
                    <p class="les-meta">
                        {{ $datumTekst }} &nbsp; {{ $tijdTekst }} &nbsp; {{ $groepsles->locatie->naam }}
                        &nbsp; {{ $bevestigd }} / {{ $groepsles->maximaal_aantal_deelnemers }} aangemeld
                    </p>
                @else
                    <p class="les-meta">
                        {{ $datumTekst }} &nbsp; {{ $tijdTekst }} &nbsp; {{ $groepsles->locatie->naam }}
                        &nbsp; Trainer: {{ $groepsles->trainer->voornaam }}
                    </p>
                @endif

                <p>{{ $groepsles->beschrijving }}</p>

                <p class="les-extra">
                    Niveau: {{ $groepsles->lestype->niveau }}
                    &middot; Doelgroep: {{ $groepsles->lestype->doelgroep }}
                    &middot; Meenemen: {{ $groepsles->lestype->benodigdheden }}
                </p>

                @if ($geannuleerd)
                    <div class="les-status">Deze les is geannuleerd.</div>
                @elseif (! $kanBeheren)
                    <p class="les-plekken">
                        @if ($vrij > 0)
                            Nog {{ $vrij }} van {{ $groepsles->maximaal_aantal_deelnemers }} plekken vrij
                        @else
                            Deze les is vol
                        @endif
                    </p>
                @endif

                <div class="form-knoppen">
                    @if ($kanBeheren)
                        {{-- Weergave trainer / beheerder --}}
                        @if ($isBeheerder)
                            <a href="{{ route('beheer.lessen.bewerken', $groepsles) }}" class="btn">Les bewerken</a>
                        @endif

                        @unless ($geannuleerd)
                            <form method="POST" action="{{ route('les.annuleren', $groepsles) }}"
                                  onsubmit="return confirm({{ Js::from('Weet je zeker dat je de les ' . $groepsles->naam . ' wilt annuleren?') }});">
                                @csrf
                                <button type="submit" class="btn btn-rand">Les annuleren</button>
                            </form>
                        @endunless
                    @elseif (! $geannuleerd && ! $voorbij)
                        {{-- Weergave gebruiker --}}
                        @if ($eigenAanmelding)
                            <span class="les-aangemeld">Je bent aangemeld voor deze les</span>
                        @elseif ($vrij === 0)
                            <button type="button" class="btn btn-uit" disabled>Les is vol</button>
                        @elseif (! $gebruiker)
                            <a href="{{ route('login') }}" class="btn">Aanmelden voor les</a>
                        @elseif ($gebruiker->rol === 'lid')
                            <form method="POST" action="{{ route('les.aanmelden', $groepsles) }}">
                                @csrf
                                <button type="submit" class="btn">Aanmelden voor les</button>
                            </form>
                        @endif
                    @elseif ($voorbij && ! $geannuleerd)
                        <span class="les-aangemeld">Deze les is al geweest</span>
                    @endif

                    <a href="{{ $terugNaarRooster }}" class="btn btn-rand">Terug naar rooster</a>
                </div>
            </div>
        </section>

        @if ($kanBeheren)

            {{-- Deelnemers (trainer / beheerder) --}}
            <section class="paneel les-deelnemers">
                <h2 class="paneel-titel">Deelnemers</h2>

                @if ($deelnemers->isEmpty())
                    <p class="lijst-leeg">Nog geen aanmeldingen.</p>
                @else
                    <ul class="deelnemers-lijst">
                        @foreach ($deelnemers as $reservering)
                            @php
                                $naam = $reservering->gebruiker->voornaam . ' ' . $reservering->gebruiker->achternaam;
                            @endphp
                            <li>
                                <span>{{ $naam }}</span>

                                <form method="POST" action="{{ route('les.afmelden', [$groepsles, $reservering]) }}"
                                      onsubmit="return confirm({{ Js::from('Weet je zeker dat je ' . $naam . ' wilt afmelden voor deze les?') }});">
                                    @csrf
                                    <button type="submit" class="link-knop">Verwijderen</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

        @else

            {{-- Over de trainer + andere momenten (gebruiker) --}}
            <div class="les-kaarten">
                <section class="paneel">
                    <h2 class="paneel-titel">Over de trainer</h2>

                    <div class="trainer-kaart">
                        <div class="trainer-foto">
                            @if ($groepsles->trainer->profielfoto_pad)
                                <img src="{{ asset('storage/' . $groepsles->trainer->profielfoto_pad) }}" alt="{{ $trainerNaam }}">
                            @else
                                <span>foto</span>
                            @endif
                        </div>

                        <div>
                            <strong>{{ $trainerNaam }}</strong>
                            @if ($groepsles->trainer->trainer_bio)
                                <p class="trainer-bio">{{ $groepsles->trainer->trainer_bio }}</p>
                            @endif
                        </div>
                    </div>
                </section>

                <section class="paneel">
                    <h2 class="paneel-titel">Andere momenten</h2>

                    @if ($andere->isEmpty())
                        <p class="lijst-leeg">Er zijn geen andere momenten gepland.</p>
                    @else
                        <ul class="deelnemers-lijst">
                            @foreach ($andere as $les)
                                <li>
                                    <span>
                                        {{ $dagNamen[$les->starttijd->dayOfWeekIso - 1] }}
                                        {{ $les->starttijd->day }} {{ $maanden[$les->starttijd->month - 1] }}
                                        &middot; {{ $les->starttijd->format('H:i') }}
                                    </span>
                                    <a href="{{ route('les.tonen', $les) }}" class="btn btn-rand btn-klein">Bekijk</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            </div>

        @endif
    </main>

    @include('partials.footer')
</body>
</html>