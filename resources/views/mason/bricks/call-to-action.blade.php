@php
    $wrapperClass = $theme === 'stone'
        ? 'border-stone-900 bg-stone-950 text-white'
        : 'border-amber-200 bg-amber-100 text-stone-950';
    $copyClass = $theme === 'stone' ? 'text-stone-200' : 'text-stone-700';
    $buttonClass = $theme === 'stone'
        ? 'bg-white text-stone-950 hover:bg-stone-100'
        : 'bg-stone-950 text-white hover:bg-stone-800';
    $eyebrowClass = $theme === 'stone' ? 'text-amber-300' : 'text-amber-700';
@endphp

<section class="rounded-[2rem] border px-8 py-10 shadow-sm {{ $wrapperClass }}">
    @if (filled($eyebrow))
        <p class="text-xs font-semibold uppercase tracking-[0.35em] {{ $eyebrowClass }}">{{ $eyebrow }}</p>
    @endif

    @if (filled($heading))
        <h2 class="mt-3 text-3xl font-semibold tracking-tight">{{ $heading }}</h2>
    @endif

    @if (filled($copy))
        <p class="mt-4 max-w-3xl text-base leading-8 {{ $copyClass }}">{{ $copy }}</p>
    @endif

    @if (filled($buttonLabel))
        <div class="mt-6">
            <a href="{{ $buttonUrl ?: '#' }}" class="inline-flex rounded-full px-5 py-3 text-sm font-medium transition {{ $buttonClass }}">
                {{ $buttonLabel }}
            </a>
        </div>
    @endif
</section>
