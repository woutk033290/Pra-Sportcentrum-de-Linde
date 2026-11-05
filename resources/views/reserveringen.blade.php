<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mijn reserveringen - De Linde</title>
    @vite(['resources/css/app.css'])
</head>
<body>
@include('partials.nav')
<main class="pagina">
    <h1 class="login-title">Mijn reserveringen</h1>
    <p class="mb-6">Annuleren kan tot uiterlijk 2 uur voor aanvang.</p>
    @if(session('status'))<div role="status" class="melding-succes">{{ session('status') }}</div>@endif
    @if($errors->any())<div role="alert" class="login-error">{{ $errors->first() }}</div>@endif
    <div class="grid gap-4">
        @forelse($reserveringen as $reservering)
            @php($les = $reservering->groepsles)
            <article class="paneel flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold"><a href="{{ route('les.tonen', $les) }}">{{ $les->naam }}</a></h2>
                    <p>{{ $les->starttijd->format('d-m-Y') }} · {{ $les->starttijd->format('H:i') }} – {{ $les->eindtijd->format('H:i') }}</p>
                    <p>Trainer: {{ $les->trainer->voornaam }} · {{ $les->status === 'geannuleerd' ? 'Les geannuleerd' : 'Bevestigd' }}</p>
                </div>
                @if(now()->lte($les->starttijd->copy()->subHours(2)))
                    <form method="POST" action="{{ route('reserveringen.destroy', $reservering) }}" onsubmit="return confirm('Wil je deze reservering annuleren?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn" type="submit">Reservering annuleren</button>
                    </form>
                @else
                    <p>Annuleren niet meer mogelijk</p>
                @endif
            </article>
        @empty
            <div class="paneel"><p>Je hebt nog geen reserveringen.</p><a href="{{ route('rooster') }}" class="btn mt-4">Bekijk het rooster</a></div>
        @endforelse
    </div>
    <div class="mt-6">{{ $reserveringen->links() }}</div>
</main>
@include('partials.footer')
</body>
</html>
