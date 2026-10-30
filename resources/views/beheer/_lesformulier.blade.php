@php
    $les = $les ?? null;
    $bewerken = $les !== null;
@endphp

<form method="POST" action="{{ $actie }}" enctype="multipart/form-data">
    @csrf
    @if ($bewerken)
        @method('PUT')
    @endif

    <div class="form-rij form-rij-2">
        <div class="veld">
            <label for="naam">Naam van de les</label>
            <input type="text" id="naam" name="naam" value="{{ old('naam', $les?->naam) }}" class="veld-input" required>
            @error('naam') <span class="veld-fout">{{ $message }}</span> @enderror
        </div>

        <div class="veld">
            <label for="lestype_id">Type</label>
            <select id="lestype_id" name="lestype_id" class="veld-input" required>
                <option value="">Kies</option>
                @foreach ($lestypen as $type)
                    <option value="{{ $type->id }}"
                            data-capaciteit="{{ $type->standaard_capaciteit }}"
                            data-duur="{{ $type->duur_minuten }}"
                            @selected((int) old('lestype_id', $les?->lestype_id) === $type->id)>
                        {{ $type->naam }}
                    </option>
                @endforeach
            </select>
            @error('lestype_id') <span class="veld-fout">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="form-rij form-rij-3">
        <div class="veld">
            <label for="datum">Datum</label>
            <input type="date" id="datum" name="datum"
                   value="{{ old('datum', $les?->starttijd?->toDateString()) }}"
                   @unless ($bewerken) min="{{ now()->toDateString() }}" @endunless
                   class="veld-input" required>
            @error('datum') <span class="veld-fout">{{ $message }}</span> @enderror
        </div>

        <div class="veld">
            <label for="starttijd">Starttijd</label>
            <input type="time" id="starttijd" name="starttijd"
                   value="{{ old('starttijd', $les?->starttijd?->format('H:i')) }}" class="veld-input" required>
            @error('starttijd') <span class="veld-fout">{{ $message }}</span> @enderror
        </div>

        <div class="veld">
            <label for="eindtijd">Eindtijd</label>
            <input type="time" id="eindtijd" name="eindtijd"
                   value="{{ old('eindtijd', $les?->eindtijd?->format('H:i')) }}" class="veld-input" required>
            @error('eindtijd') <span class="veld-fout">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="form-rij form-rij-3">
        <div class="veld">
            <label for="trainer_id">Trainer</label>
            <select id="trainer_id" name="trainer_id" class="veld-input" required>
                <option value="">Kies</option>
                @foreach ($trainers as $trainer)
                    <option value="{{ $trainer->id }}" @selected((int) old('trainer_id', $les?->trainer_id) === $trainer->id)>
                        {{ $trainer->voornaam }} {{ $trainer->achternaam }}
                    </option>
                @endforeach
            </select>
            @error('trainer_id') <span class="veld-fout">{{ $message }}</span> @enderror
        </div>

        <div class="veld">
            <label for="locatie_id">Zaal</label>
            <select id="locatie_id" name="locatie_id" class="veld-input" required>
                <option value="">Kies</option>
                @foreach ($locaties as $locatie)
                    <option value="{{ $locatie->id }}" @selected((int) old('locatie_id', $les?->locatie_id) === $locatie->id)>
                        {{ $locatie->naam }}
                    </option>
                @endforeach
            </select>
            @error('locatie_id') <span class="veld-fout">{{ $message }}</span> @enderror
        </div>

        <div class="veld">
            <label for="maximaal_aantal_deelnemers">Max. deelnemers</label>
            <input type="number" id="maximaal_aantal_deelnemers" name="maximaal_aantal_deelnemers"
                   value="{{ old('maximaal_aantal_deelnemers', $les?->maximaal_aantal_deelnemers) }}"
                   min="1" max="200" class="veld-input" required>
            @error('maximaal_aantal_deelnemers') <span class="veld-fout">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="form-rij form-rij-2">
        <div class="veld">
            <label for="beschrijving">Beschrijving</label>
            <textarea id="beschrijving" name="beschrijving" rows="5" class="veld-input" required>{{ old('beschrijving', $les?->beschrijving) }}</textarea>
            @error('beschrijving') <span class="veld-fout">{{ $message }}</span> @enderror
        </div>

        <div class="veld">
            <label for="foto">Foto</label>
            <label for="foto" class="upload-vak">
                @if ($les?->foto_pad)
                    <img src="{{ asset('storage/' . $les->foto_pad) }}" alt="Huidige foto" class="upload-preview">
                @endif
                <span id="foto-tekst">{{ $les?->foto_pad ? 'klik om te vervangen' : 'upload' }}</span>
            </label>
            <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp" class="upload-input">
            @error('foto') <span class="veld-fout">{{ $message }}</span> @enderror
        </div>
    </div>

    @unless ($bewerken)
        <div class="form-rij">
            <div class="veld">
                <label for="herhalen">Herhalen</label>
                <select id="herhalen" name="herhalen" class="veld-input">
                    <option value="0" @selected(old('herhalen', '0') === '0')>Eenmalig</option>
                    <option value="1" @selected(old('herhalen') === '1')>Wekelijks</option>
                    <option value="2" @selected(old('herhalen') === '2')>Om de week</option>
                </select>
                @error('herhalen') <span class="veld-fout">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-rij" id="herhaling-einde" @if (old('herhalen', '0') === '0') hidden @endif>
            <div class="veld">
                <label for="herhalen_tot">Herhalen tot en met</label>
                <input type="date" id="herhalen_tot" name="herhalen_tot" value="{{ old('herhalen_tot') }}" class="veld-input">
                @error('herhalen_tot') <span class="veld-fout">{{ $message }}</span> @enderror
            </div>
        </div>
    @endunless

    <div class="form-knoppen">
        <button type="submit" class="btn">Opslaan</button>
        <a href="{{ route('beheer.lessen') }}" class="btn btn-rand">Annuleren</a>
    </div>
</form>

<script>
    (function () {
        const type = document.getElementById('lestype_id');
        const max = document.getElementById('maximaal_aantal_deelnemers');
        const start = document.getElementById('starttijd');
        const eind = document.getElementById('eindtijd');
        const herhalen = document.getElementById('herhalen');
        const einde = document.getElementById('herhaling-einde');
        const eindeInput = document.getElementById('herhalen_tot');
        const foto = document.getElementById('foto');
        const fotoTekst = document.getElementById('foto-tekst');

        function gekozenType() {
            return type.options[type.selectedIndex];
        }

        // Eindtijd invullen op basis van de duur van het lestype (alleen als hij leeg is)
        function zetEindtijd() {
            const optie = gekozenType();
            if (!start.value || eind.value !== '' || !optie || !optie.dataset.duur) return;

            const delen = start.value.split(':').map(Number);
            const totaal = delen[0] * 60 + delen[1] + Number(optie.dataset.duur);
            if (totaal >= 24 * 60) return;

            eind.value = String(Math.floor(totaal / 60)).padStart(2, '0') + ':' + String(totaal % 60).padStart(2, '0');
        }

        type.addEventListener('change', function () {
            const optie = gekozenType();
            if (optie && optie.dataset.capaciteit && max.value === '') {
                max.value = optie.dataset.capaciteit;
            }
            zetEindtijd();
        });

        start.addEventListener('change', zetEindtijd);

        if (herhalen) {
            herhalen.addEventListener('change', function () {
                const herhaalt = herhalen.value !== '0';
                einde.hidden = !herhaalt;
                eindeInput.required = herhaalt;
            });
            eindeInput.required = herhalen.value !== '0';
        }

        foto.addEventListener('change', function () {
            if (foto.files.length) {
                fotoTekst.textContent = foto.files[0].name;
            }
        });
    })();
</script>