<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $lestype->naam }} - Sportschool De Linde</title>
    @vite(['resources/css/app.css'])
</head>
<body>
    @include('partials.nav')

    @php
        $dagNamen = ['Ma', 'Di', 'Wo', 'Do', 'Vr', 'Za', 'Zo'];
        $maanden  = ['jan', 'feb', 'mrt', 'apr', 'mei', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'dec'];
    @endphp

    <main class="pagina">

        @if (session('succes'))
            <div class="melding-succes">{{ session('succes') }}</div>
        @endif

        @if (session('fout'))
            <div class="login-error">{{ session('fout') }}</div>
        @endif

        {{-- Wat is ...? (foto links) --}}
        <section class="info-rij">
            <div class="info-foto" role="img" aria-label="Foto {{ $lestype->naam }}">
                @if ($lestype->foto_pad)
                    <img src="{{ asset('storage/' . $lestype->foto_pad) }}" alt="">
                @else
                    <span>foto</span>
                @endif
            </div>

            <div class="info-tekst">
                <h1 class="info-titel">Wat is {{ mb_strtolower($lestype->naam) }}?</h1>
                <p>{{ $lestype->beschrijving }}</p>
            </div>
        </section>

        {{-- Voor wie? (foto rechts) --}}
        <section class="info-rij info-rij-omgekeerd">
            <div class="info-tekst">
                <h2 class="info-titel">Voor wie?</h2>

                <ul class="info-feiten">
                    <li><strong>Doelgroep</strong> {{ $lestype->doelgroep }}</li>
                    <li><strong>Niveau</strong> {{ $lestype->niveau }}</li>
                    <li><strong>Wat neem je mee?</strong> {{ $lestype->benodigdheden }}</li>
                    <li><strong>Duur</strong> {{ $lestype->duur_minuten }} minuten</li>
                </ul>
            </div>

            <div class="info-foto" role="img" aria-label="Foto {{ $lestype->naam }}">
                @if ($lestype->extra_foto_pad)
                    <img src="{{ asset('storage/' . $lestype->extra_foto_pad) }}" alt="">
                @else
                    <span>foto</span>
                @endif
            </div>
        </section>

        {{-- Trainer (foto links) --}}
        <section class="info-rij">
            <div class="info-foto" role="img" aria-label="Foto trainer">
                @if ($trainer && $trainer->profielfoto_pad)
                    <img src="{{ asset('storage/' . $trainer->profielfoto_pad) }}" alt="">
                @else
                    <span>foto</span>
                @endif
            </div>

            <div class="info-tekst">
                <h2 class="info-titel">Trainer</h2>

                @if ($trainer)
                    <p><strong>{{ $trainer->voornaam }} {{ $trainer->achternaam }}</strong></p>
                    @if ($trainer->trainer_bio)
                        <p>{{ $trainer->trainer_bio }}</p>
                    @endif
                @else
                    <p>Er is nog geen trainer bekend voor deze les.</p>
                @endif
            </div>
        </section>

        {{-- Eerstvolgende lessen --}}
        <section class="paneel">
            <h2 class="paneel-titel">Eerstvolgende lessen</h2>

            @if ($lessen->isEmpty())
                <p class="lijst-leeg">Er zijn op dit moment geen lessen gepland.</p>
            @else
                <ul class="deelnemers-lijst">
                    @foreach ($lessen as $les)
                        @php
                            $vol = $les->bevestigd_aantal >= $les->maximaal_aantal_deelnemers;
                            $isAangemeld = in_array($les->id, $aangemeld, true);
                        @endphp

                        <li>
                            <div class="les-info">
                                <a href="{{ route('les.tonen', $les) }}" class="les-link">
                                    {{ $dagNamen[$les->starttijd->dayOfWeekIso - 1] }}
                                    {{ $les->starttijd->day }} {{ $maanden[$les->starttijd->month - 1] }}
                                </a>
                                <span class="les-tijd">
                                    {{ $les->starttijd->format('H:i') }} &ndash; {{ $les->eindtijd->format('H:i') }}
                                    &middot; {{ $les->locatie->naam }}
                                </span>
                            </div>

                            <div class="les-acties">
                                @if ($isAangemeld)
                                    <span class="les-aangemeld">Aangemeld</span>
                                @elseif ($vol)
                                    <button type="button" class="btn btn-uit" disabled>Vol</button>
                                @elseif ($gebruiker && in_array($gebruiker->rol, ['trainer', 'beheerder'], true))
                                    <a href="{{ route('les.tonen', $les) }}" class="btn btn-rand">Bekijk les</a>
                                @elseif ($gebruiker)
                                    <form method="POST" action="{{ route('les.aanmelden', $les) }}">
                                        @csrf
                                        <button type="submit" class="btn">Aanmelden voor les</button>
                                    </form>
                                @else
                                    <a href="{{ route('login') }}" class="btn">Aanmelden voor les</a>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

    </main>

    @include('partials.footer')
</body>
</html>