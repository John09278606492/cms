<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $context = app(\App\Support\ErrorPageContext::class)->resolve404(
            request(),
            blank($exception->getMessage()) ? null : $exception->getMessage(),
        );

        extract($context);
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-950 text-stone-50 antialiased">
    <main class="relative isolate flex min-h-screen items-center overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(245,158,11,0.20),_transparent_34%),radial-gradient(circle_at_bottom_right,_rgba(120,113,108,0.20),_transparent_30%),linear-gradient(180deg,#0c0a09_0%,#1c1917_100%)]"></div>
        <div class="absolute inset-x-0 top-0 h-px bg-white/10"></div>
        <div class="relative mx-auto w-full max-w-5xl px-6 py-16 lg:py-24">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.45em] text-amber-300">{{ $eyebrow }}</p>
                <h1 class="mt-4 text-5xl font-semibold tracking-tight text-white sm:text-6xl">{{ $title }}</h1>
                <p class="mt-6 max-w-2xl text-lg leading-8 text-stone-300">{{ $description }}</p>

                @if (filled($details))
                    <p class="mt-4 max-w-2xl rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm leading-7 text-stone-300">
                        {{ $details }}
                    </p>
                @endif

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ $primaryUrl }}" class="inline-flex items-center rounded-full bg-amber-400 px-5 py-3 text-sm font-medium text-stone-950 transition hover:bg-amber-300">
                        {{ $primaryLabel }}
                    </a>
                    <a href="{{ $secondaryUrl }}" class="inline-flex items-center rounded-full border border-white/15 px-5 py-3 text-sm font-medium text-white transition hover:border-white/30 hover:bg-white/5">
                        {{ $secondaryLabel }}
                    </a>
                </div>
            </div>

            <div class="mt-12 grid gap-4 lg:grid-cols-3">
                @foreach ($highlights as $highlight)
                    <section class="rounded-3xl border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/10 backdrop-blur">
                        <p class="text-sm font-semibold text-amber-300">{{ $highlight['title'] }}</p>
                        <p class="mt-3 text-sm leading-7 text-stone-300">{{ $highlight['copy'] }}</p>
                    </section>
                @endforeach
            </div>
        </div>
    </main>
</body>
</html>
