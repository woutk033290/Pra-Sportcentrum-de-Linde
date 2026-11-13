
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">

    <title>Rooster - Sportschool De Linde</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #333;
        }

        h1 {
            color: #006fba;
            margin-bottom: 5px;
        }

        .periode {
            margin-bottom: 25px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background-color: #006fba;
            color: white;
            padding: 9px;
            text-align: left;
        }

        td {
            border-bottom: 1px solid #ddd;
            padding: 9px;
        }

        tr:nth-child(even) {
            background-color: #f4f7f9;
        }

        .geannuleerd {
            color: #c0392b;
            font-weight: bold;
        }

        .footer {
            margin-top: 25px;
            color: #777;
            font-size: 9px;
        }
    </style>
</head>

<body>

    <h1>Sportschool De Linde</h1>
    <h2>Trainingsrooster</h2>

    <p class="periode">
        Periode:
        {{ \Carbon\Carbon::parse($datumVan)->format('d-m-Y') }}
        t/m
        {{ \Carbon\Carbon::parse($datumTot)->format('d-m-Y') }}
    </p>

    @if($lessen->isEmpty())
        <p>Er zijn geen lessen gevonden voor deze selectie.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Datum</th>
                    <th>Tijd</th>
                    <th>Les</th>
                    <th>Lestype</th>
                    <th>Trainer</th>
                    <th>Locatie</th>
                    <th>Aanmeldingen</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @foreach($lessen as $les)
                    <tr>
                        <td>
                            {{ $les->starttijd->format('d-m-Y') }}
                        </td>

                        <td>
                            {{ $les->starttijd->format('H:i') }}
                            -
                            {{ $les->eindtijd->format('H:i') }}
                        </td>

                        <td>
                            {{ $les->naam }}
                        </td>

                        <td>
                            {{ $les->lestype->naam ?? '-' }}
                        </td>

                        <td>
                            {{ $les->trainer->voornaam ?? '' }}
                            {{ $les->trainer->achternaam ?? '' }}
                        </td>

                        <td>
                            {{ $les->locatie->naam ?? '-' }}
                        </td>

                        <td>
                            {{ $les->reserveringen
                                ->where('status', 'bevestigd')
                                ->count() }}
                            /
                            {{ $les->maximaal_aantal_deelnemers }}
                        </td>

                        <td>
                            @if($les->status === 'geannuleerd')
                                <span class="geannuleerd">Geannuleerd</span>
                            @else
                                Gepland
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <p class="footer">
        Gegenereerd op {{ now()->format('d-m-Y H:i') }}
        · Sportschool De Linde
    </p>

</body>
</html>
