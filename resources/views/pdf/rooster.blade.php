
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rooster downloaden - De Linde</title>
    @vite(['resources/css/app.css'])

    <style>
        .pdf-pagina {
            max-width: 950px;
            margin: 45px auto;
            padding: 0 20px;
        }

        .pdf-header {
            margin-bottom: 28px;
        }

        .pdf-header h1 {
            color: #006fba;
            font-size: 30px;
            margin-bottom: 8px;
        }

        .pdf-header p {
            color: #687582;
            margin: 0;
        }

        .pdf-kaart {
            background: #fff;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.07);
        }

        .pdf-sectie {
            margin-bottom: 28px;
        }

        .pdf-sectie h2 {
            font-size: 18px;
            color: #263746;
            margin: 0 0 15px;
        }

        .pdf-datums {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .pdf-veld label {
            display: block;
            font-weight: 600;
            color: #34495e;
            margin-bottom: 8px;
        }

        .pdf-veld input[type="date"] {
            width: 100%;
            box-sizing: border-box;
            padding: 13px;
            border: 1px solid #d8e0e8;
            border-radius: 9px;
            font-size: 15px;
            background: #f9fbfd;
        }

        .pdf-opties {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
        }

        .pdf-optie {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 15px;
            border: 1px solid #e0e7ed;
            border-radius: 9px;
            background: #f9fbfd;
            cursor: pointer;
            transition: 0.2s;
        }

        .pdf-optie:hover {
            border-color: #006fba;
            background: #f0f8fd;
        }

        .pdf-optie input {
            width: 17px;
            height: 17px;
            accent-color: #006fba;
            cursor: pointer;
        }

        .pdf-hulp {
            color: #7a8792;
            font-size: 13px;
            margin-top: 10px;
        }

        .pdf-fout {
            background: #fff0f0;
            color: #b42318;
            border: 1px solid #f3c5c5;
            border-radius: 9px;
            padding: 13px 16px;
            margin-bottom: 22px;
        }

        .pdf-acties {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 12px;
            border-top: 1px solid #e8edf1;
            padding-top: 22px;
        }

        .pdf-download {
            display: inline-block;
            background: #006fba;
            color: white;
            padding: 13px 23px;
            border: none;
            border-radius: 9px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .pdf-download:hover {
            background: #00558f;
        }

        .pdf-terug {
            display: inline-block;
            color: #52616d;
            background: #edf1f4;
            padding: 13px 20px;
            border-radius: 9px;
            text-decoration: none;
            font-weight: 600;
        }

        .pdf-terug:hover {
            background: #dfe6eb;
        }

        @media (max-width: 600px) {
            .pdf-kaart {
                padding: 20px;
            }

            .pdf-datums {
                grid-template-columns: 1fr;
            }

            .pdf-acties {
                flex-direction: column-reverse;
            }

            .pdf-acties a,
            .pdf-acties button {
                width: 100%;
                box-sizing: border-box;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    @include('partials.nav')

    <main class="pdf-pagina">

        <div class="pdf-header">
            <h1>Rooster downloaden</h1>
            <p>Stel je filters in en download het trainingsrooster als PDF.</p>
        </div>

        <div class="pdf-kaart">

            <form method="POST" action="{{ route('rooster.pdf.downloaden') }}">
                @csrf

                @if($errors->any())
                    <div class="pdf-fout">
                        {{ $errors->first() }}
                    </div>
                @endif

                {{-- Periode --}}
                <section class="pdf-sectie">
                    <h2>1. Kies een periode</h2>

                    <div class="pdf-datums">

                        <div class="pdf-veld">
                            <label for="datum_van">Vanaf datum</label>

                            <input
                                type="date"
                                id="datum_van"
                                name="datum_van"
                                value="{{ old('datum_van', now()->toDateString()) }}"
                                required
                            >
                        </div>

                        <div class="pdf-veld">
                            <label for="datum_tot">Tot en met datum</label>

                            <input
                                type="date"
                                id="datum_tot"
                                name="datum_tot"
                                value="{{ old('datum_tot', now()->addMonth()->toDateString()) }}"
                                required
                            >
                        </div>

                    </div>
                </section>

                {{-- Trainers: uitsluitend beheerder --}}
                @if($isBeheerder)

                    <section class="pdf-sectie">
                        <h2>2. Kies trainers</h2>

                        <div class="pdf-opties">

                            @foreach($trainers as $trainer)

                                <label class="pdf-optie">

                                    <input
                                        type="checkbox"
                                        name="trainers[]"
                                        value="{{ $trainer->id }}"
                                        @checked(in_array($trainer->id, old('trainers', [])))
                                    >

                                    <span>
                                        {{ $trainer->voornaam }}
                                        {{ $trainer->achternaam }}
                                    </span>

                                </label>

                            @endforeach

                        </div>

                        <p class="pdf-hulp">
                            Laat alles uitgevinkt om lessen van alle trainers te tonen.
                        </p>
                    </section>

                @endif

                {{-- Lestypen --}}
                <section class="pdf-sectie">
                    <h2>{{ $isBeheerder ? '3.' : '2.' }} Kies lestypen</h2>

                    <div class="pdf-opties">

                        @foreach($lestypen as $lestype)

                            <label class="pdf-optie">

                                <input
                                    type="checkbox"
                                    name="lestypen[]"
                                    value="{{ $lestype->id }}"
                                    @checked(in_array($lestype->id, old('lestypen', [])))
                                >

                                <span>{{ $lestype->naam }}</span>

                            </label>

                        @endforeach

                    </div>

                    <p class="pdf-hulp">
                        Laat alles uitgevinkt om alle lestypen te tonen.
                    </p>
                </section>

                {{-- Alleen leden kunnen eigen reserveringen filteren --}}
                @if($isLid)

                    <section class="pdf-sectie">
                        <h2>3. Overige filters</h2>

                        <label class="pdf-optie">

                            <input
                                type="checkbox"
                                name="alleen_mijn_lessen"
                                value="1"
                                @checked(old('alleen_mijn_lessen'))
                            >

                            <span>Alleen mijn aangemelde lessen tonen</span>

                        </label>
                    </section>

                @endif

                {{-- Knoppen --}}
                <div class="pdf-acties">

                    <a href="{{ route('rooster') }}" class="pdf-terug">
                        Terug naar rooster
                    </a>

                    <button type="submit" class="pdf-download">
                        PDF downloaden
                    </button>

                </div>

            </form>

        </div>

    </main>

    @include('partials.footer')

</body>
</html>
