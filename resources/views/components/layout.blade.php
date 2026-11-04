@props(['title' => 'Sportcentrum De Linde'])
<!doctype html><html lang="nl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#123c83"><title>{{ $title }} · De Linde</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
<a href="#inhoud" class="sr-only focus:not-sr-only">Naar de inhoud</a>
<header class="border-b border-slate-200 bg-white"><div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-5 px-5 py-5 lg:px-8">
<a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="De Linde startpagina"><span class="grid size-11 place-items-center rounded-2xl bg-blue-800 text-2xl font-black text-white">L<span class="sr-only">ogo</span></span><span class="text-xl font-extrabold tracking-tight">de linde<span class="block text-[10px] font-semibold uppercase tracking-[.23em] text-slate-500">Sportcentrum</span></span></a>
<nav aria-label="Hoofdnavigatie" class="flex flex-wrap items-center gap-3 text-sm font-semibold sm:gap-6">
<a class="nav-link" href="{{ route('home') }}">Home</a><a class="nav-link" href="{{ route('rooster') }}">Lesrooster</a>
@auth
@if(auth()->user()->rol === 'lid')<a class="nav-link" href="{{ route('reserveringen.index') }}">Mijn reserveringen</a>@endif
@if(auth()->user()->rol === 'trainer')<a class="nav-link" href="{{ route('trainer') }}">Mijn lessen</a>@endif
@if(auth()->user()->rol === 'beheerder')<a class="nav-link" href="{{ route('beheer.dashboard') }}">Beheer</a>@endif
<form method="post" action="{{ route('uitloggen') }}">@csrf<button class="button-secondary">Uitloggen</button></form>
@else
<a class="nav-link" href="{{ route('login') }}">Inloggen</a><a class="button" href="{{ route('registreren') }}">Word lid <span aria-hidden="true">↗</span></a>
@endauth
</nav></div></header>
<main id="inhoud" class="mx-auto w-full max-w-7xl flex-1 px-5 py-8 lg:px-8 lg:py-12">
@if(session('status'))<div role="status" class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-900">{{ session('status') }}</div>@endif
@if($errors->any())<div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800"><strong>Controleer je gegevens</strong><ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
{{ $slot }}
</main>
<footer class="mt-12 border-t border-slate-200 bg-white"><div class="mx-auto flex max-w-7xl flex-wrap justify-between gap-4 px-5 py-8 text-sm text-slate-500"><p>© {{ date('Y') }} Sportcentrum De Linde</p><p>Samen bewegen. Jezelf zijn.</p><a class="text-blue-800" href="{{ route('rooster') }}">Vind jouw volgende les →</a></div></footer></body></html>
