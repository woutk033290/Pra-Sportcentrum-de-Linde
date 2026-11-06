<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sportschool De Linde</title>
    @vite(['resources/css/app.css'])
</head>
<body>
    @include('partials.nav')

    <main class="pagina">

        {{-- De lessoorten --}}
        <section class="lessen-grid">
            @foreach ($lestypen as $type)
                <article class="les-kaart">
                    <div class="les-afbeelding" role="img" aria-label="{{ $type->naam }}">
                        @if ($type->foto_pad)
                            <img src="{{ asset('storage/' . $type->foto_pad) }}" alt="">
                        @endif
                    </div>

                    <div class="les-inhoud">
                        <h3>{{ $type->naam }}</h3>
                        <p>{{ \Illuminate\Support\Str::limit($type->beschrijving, 90) }}</p>
                        <a href="{{ route('lestype.tonen', \Illuminate\Support\Str::slug($type->naam)) }}" class="btn btn-rand">Meer info &rarr;</a>
                    </div>
                </article>
            @endforeach
        </section>

        {{-- Mini rooster --}}
        <section class="home-rooster" id="rooster">
            <h2 class="sectie-titel">Rooster deze week</h2>

            <div class="kaart rooster-kaart">
                <div class="rooster-balk">
                    <div class="rooster-pills">
                        <span class="rooster-filter-label">Filter:</span>

                        <a href="{{ route('home') }}#rooster"
                           class="pill {{ $gekozenType ? '' : 'pill-actief' }}">Alle</a>

                        @foreach ($lestypen as $type)
                            <a href="{{ route('home', ['type' => $type->id]) }}#rooster"
                               class="pill {{ $gekozenType === $type->id ? 'pill-actief' : '' }}">{{ $type->naam }}</a>
                        @endforeach
                    </div>

                    <a href="{{ route('rooster', array_filter(['type' => $gekozenType])) }}"
                       class="btn btn-rand">Volledig rooster &rarr;</a>
                </div>

                @include('partials.rooster-tabel')

                @if (empty($grid))
                    <p class="rooster-leeg">Er zijn deze week geen lessen gepland.</p>
                @endif
            </div>
        </section>

    </main>

    @include('partials.footer')
</body>
</html>