<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lessen beheren - Sportschool De Linde</title>
    @vite(['resources/css/app.css'])
</head>
<body>
    @include('partials.nav')

    @php
        $dagNamen = ['Ma', 'Di', 'Wo', 'Do', 'Vr', 'Za', 'Zo'];
    @endphp

    <main class="beheer-lessen">

        {{-- Links: bestaande lessen --}}
        <aside class="paneel">
            <h2 class="paneel-titel">Bestaande lessen</h2>

            <form method="GET" action="{{ route('beheer.lessen') }}" class="zoek-form">
                <input type="search" name="zoek" value="{{ $zoek }}" placeholder="Zoeken..." class="veld-input" aria-label="Zoeken">
            </form>

            @if (session('fout'))
                <div class="login-error">{{ session('fout') }}</div>
            @endif

            @if ($lessen->isEmpty())
                <p class="lijst-leeg">Geen lessen gevonden.</p>
            @else
                <ul class="les-lijst">
                    @foreach ($lessen as $les)
                        <li>
                            <div class="les-info">
                                <strong>{{ $les->naam }}</strong>
                                <span>
                                    {{ $dagNamen[$les->starttijd->dayOfWeekIso - 1] }}
                                    {{ $les->starttijd->format('d-m') }} &middot; {{ $les->starttijd->format('H:i') }}
                                </span>
                            </div>

                            <div class="les-acties">
                                <a href="{{ route('beheer.lessen.bewerken', $les) }}"
                                   class="icoon-knop" title="Aanpassen" aria-label="Les {{ $les->naam }} aanpassen">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                                    </svg>
                                </a>

                                <form method="POST" action="{{ route('beheer.lessen.verwijderen', $les) }}"
                                      onsubmit="return confirm('Weet je zeker dat je de les {{ e($les->naam) }} van {{ $les->starttijd->format('d-m-Y H:i') }} wilt verwijderen?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="icoon-knop icoon-gevaar"
                                            title="Verwijderen" aria-label="Les {{ $les->naam }} verwijderen">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                            <line x1="10" y1="11" x2="10" y2="17"/>
                                            <line x1="14" y1="11" x2="14" y2="17"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>

                {{ $lessen->links() }}
            @endif

            <a href="{{ route('beheer.lessen') }}#nieuwe-les" class="btn">+ Nieuwe les</a>
        </aside>

        {{-- Rechts: formulier nieuwe les --}}
        <section class="paneel" id="nieuwe-les">
            <h2 class="paneel-titel">Nieuwe les aanmaken</h2>

            @if (session('succes'))
                <div class="melding-succes">{{ session('succes') }}</div>
            @endif

            @if ($errors->any())
                <div class="login-error">
                    Controleer het formulier: er ging iets mis bij het opslaan.
                </div>
            @endif

            @include('beheer._lesformulier', [
                'actie' => route('beheer.lessen.opslaan'),
                'les'   => null,
            ])
        </section>
    </main>

    @include('partials.footer')
</body>
</html>