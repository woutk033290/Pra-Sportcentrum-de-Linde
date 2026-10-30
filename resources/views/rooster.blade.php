<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rooster - Sportschool De Linde</title>
    @vite(['resources/css/app.css'])
</head>

<body>
    @include('partials.nav')

    @php
        $query = request()->query();
        $dagNamen = ['Ma', 'Di', 'Wo', 'Do', 'Vr', 'Za', 'Zo'];
        $dagdelen = ['ochtend' => 'Ochtend', 'middag' => 'Middag', 'avond' => 'Avond'];
        $heeftLessen = count($grid) > 0;
    @endphp

    <main class="pagina">
        <h1 class="sectie-titel">Rooster</h1>

        <div class="kaart rooster-kaart">

            {{-- Bovenste balk: week wisselen + filters --}}
            <div class="rooster-balk">
                <div class="rooster-week">
                    <a href="{{ route('rooster', array_merge($query, ['week' => $vorige])) }}" class="rooster-pijl"
                        aria-label="Vorige week">&lsaquo;</a>

                    <strong class="rooster-weeklabel">{{ $weekLabel }}</strong>

                    <a href="{{ route('rooster', array_merge($query, ['week' => $volgende])) }}" class="rooster-pijl"
                        aria-label="Volgende week">&rsaquo;</a>

                    <a href="{{ route('rooster', array_merge($query, ['week' => null])) }}"
                        class="rooster-vandaag">Vandaag</a>
                </div>

                <div class="rooster-pills">
                    <a href="{{ route('rooster', array_merge($query, ['type' => null])) }}"
                        class="pill {{ $gekozenType ? '' : 'pill-actief' }}">Alle</a>

                    @foreach ($lestypen as $type)
                        <a href="{{ route('rooster', array_merge($query, ['type' => $type->id])) }}"
                            class="pill {{ $gekozenType === $type->id ? 'pill-actief' : '' }}">{{ $type->naam }}</a>
                    @endforeach
                </div>
            </div>

            <form method="GET" action="{{ route('rooster') }}" class="rooster-filters">
                @if (request()->filled('week'))
                    <input type="hidden" name="week" value="{{ $start->toDateString() }}">
                @endif
                @if ($gekozenType)
                    <input type="hidden" name="type" value="{{ $gekozenType }}">
                @endif

                <select name="trainer" class="rooster-select" onchange="this.form.submit()" aria-label="Trainer">
                    <option value="">Trainer</option>
                    @foreach ($trainers as $trainer)
                        <option value="{{ $trainer->id }}" @selected($gekozenTrainer === $trainer->id)>
                            {{ $trainer->voornaam }} {{ $trainer->achternaam }}
                        </option>
                    @endforeach
                </select>

                <select name="dag" class="rooster-select" onchange="this.form.submit()" aria-label="Dag">
                    <option value="">Dag</option>
                    @foreach ($dagNamen as $i => $naam)
                        <option value="{{ $i + 1 }}" @selected($gekozenDag === $i + 1)>{{ $naam }}</option>
                    @endforeach
                </select>

                <noscript><button type="submit" class="btn">Filter</button></noscript>
            </form>

            {{-- Weekrooster --}}
            <div class="rooster-scroll">
                <table class="rooster-tabel">
                    <thead>
                        <tr>
                            <th class="rooster-hoek"></th>
                            @foreach ($dagen as $i)
                                @php $datum = $start->copy()->addDays($i); @endphp
                                <th class="{{ $datum->isSameDay($vandaag) ? 'rooster-vandaag-kolom' : '' }}">
                                    {{ $dagNamen[$i] }}
                                    <span class="rooster-datum">{{ $datum->day }}</span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dagdelen as $sleutel => $label)
                            <tr>
                                <th class="rooster-dagdeel">{{ $label }}</th>

                                @foreach ($dagen as $i)
                                    <td>
                                        @foreach ($grid[$sleutel][$i] ?? [] as $les)
                                            @php
                                                $geannuleerd = $les->status === 'geannuleerd';
                                                $vol = !$geannuleerd
                                                    && $les->bevestigd_aantal >= $les->maximaal_aantal_deelnemers;
                                            @endphp

                                            {{-- TODO: link naar de lesinfo/aanmelden-pagina zodra die bestaat --}}
                                            <a href="{{ route('les.tonen', $les) }}"
                                                class="les-blok {{ $geannuleerd ? 'les-geannuleerd' : '' }}"
                                                title="{{ $les->trainer->voornaam }} {{ $les->trainer->achternaam }} · {{ $les->locatie->naam }}">
                                                <span class="les-blok-naam">
                                                    {{ $les->naam ?: $les->lestype->naam }} {{ $les->starttijd->format('H:i') }}
                                                </span>
                                                <span class="les-blok-info">
                                                    {{ $les->trainer->voornaam }} · {{ $les->locatie->naam }}
                                                </span>

                                                @if ($geannuleerd)
                                                    <span class="les-badge">Geannuleerd</span>
                                                @elseif ($vol)
                                                    <span class="les-badge">Vol</span>
                                                @endif
                                            </a>
                                        @endforeach
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @unless ($heeftLessen)
                <p class="rooster-leeg">Geen lessen gevonden voor deze week en selectie.</p>
            @endunless

            <p class="rooster-hint">Klik op een les &rarr; lesinfo / aanmelden</p>
        </div>
    </main>

    @include('partials.footer')
</body>

</html>