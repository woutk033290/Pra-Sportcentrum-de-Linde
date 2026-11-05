<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mijn lessen - De Linde</title>
    @vite(['resources/css/app.css'])
</head>
<body>
@include('partials.nav')
<main class="pagina">
    <h1 class="login-title">Mijn lessen</h1>
    <p class="mb-6">Welkom, {{ auth('web')->user()->voornaam }}. Hieronder staan de lessen die aan jou zijn gekoppeld.</p>
    <div class="grid gap-4">
        @forelse($lessen as $les)
            <article class="paneel">
                <h2 class="text-xl font-semibold">{{ $les->naam }}</h2>
                <p>{{ $les->starttijd->format('d-m-Y') }} · {{ $les->starttijd->format('H:i') }} – {{ $les->eindtijd->format('H:i') }}</p>
                <p>{{ $les->status === 'geannuleerd' ? 'Geannuleerd' : 'Gepland' }}</p>
            </article>
        @empty
            <p class="paneel">Er zijn nog geen lessen aan jou gekoppeld.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $lessen->links() }}</div>
</main>
@include('partials.footer')
</body>
</html>
