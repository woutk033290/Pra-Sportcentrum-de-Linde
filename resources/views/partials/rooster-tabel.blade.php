@php
    $dagNamen = ['Ma', 'Di', 'Wo', 'Do', 'Vr', 'Za', 'Zo'];
    $dagdelen = ['ochtend' => 'Ochtend', 'middag' => 'Middag', 'avond' => 'Avond'];
@endphp

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
                                    $vol = ! $geannuleerd
                                        && $les->bevestigd_aantal >= $les->maximaal_aantal_deelnemers;
                                @endphp

                                <a href="{{ route('les.tonen', $les) }}"
                                   class="les-blok {{ $geannuleerd ? 'les-geannuleerd' : '' }}"
                                   title="{{ $les->trainer->voornaam }} {{ $les->trainer->achternaam }} · {{ $les->locatie->naam }}">
                                    <span class="les-blok-naam">
                                        {{ $les->naam ?: $les->lestype->naam }} {{ $les->starttijd->format('H:i') }}
                                    </span>

                                    @if ($geannuleerd)
                                        <span class="les-badge">Geannuleerd</span>
                                    @elseif ($vol)
                                        <span class="les-badge">0 plekken vrij · Vol</span>
                                    @else
                                        <span class="les-badge">{{ max(0, $les->maximaal_aantal_deelnemers - $les->bevestigd_aantal) }} {{ $les->maximaal_aantal_deelnemers - $les->bevestigd_aantal === 1 ? 'plek' : 'plekken' }} vrij</span>
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
