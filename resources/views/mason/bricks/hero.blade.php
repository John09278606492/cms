@php
    $containerClass = match ($surface) {
        'contrast' => 'bg-stone-950 text-white border-stone-900',
        'minimal' => 'bg-transparent text-stone-950 border-transparent',
        default => 'bg-amber-50 text-stone-950 border-amber-200',
    };

    $headingClass = $surface === 'contrast' ? 'text-white' : 'text-stone-950';
    $copyClass = $surface === 'contrast' ? 'text-stone-200' : 'text-stone-600';
    $eyebrowClass = $surface === 'contrast' ? 'text-amber-300' : 'text-amber-700';
    $alignmentClass = $alignment === 'center' ? 'items-center text-center' : 'items-start text-left';
    $primaryButtonClass = $surface === 'contrast'
        ? 'bg-white text-stone-950 hover:bg-stone-100'
        : 'bg-stone-950 text-white hover:bg-stone-800';
@endphp

<section class="rounded-[2rem] border px-8 py-12 shadow-sm {{ $containerClass }}">
    <div class="flex max-w-3xl flex-col gap-6 {{ $alignmentClass }}">
        @if (filled($eyebrow))
            <p class="text-xs font-semibold uppercase tracking-[0.35em] {{ $eyebrowClass }}">{{ $eyebrow }}</p>
        @endif

        @if (filled($heading))
            <h2 class="text-4xl font-semibold tracking-tight sm:text-5xl {{ $headingClass }}">{{ $heading }}</h2>
        @endif

        @if (filled($copy))
            <p class="max-w-2xl text-lg leading-8 {{ $copyClass }}">{{ $copy }}</p>
        @endif

        @if (filled($primaryLabel) || filled($secondaryLabel))
            <div class="flex flex-wrap gap-3 {{ $alignment === 'center' ? 'justify-center' : '' }}">
                @if (filled($primaryLabel))
                    <a
                        href="{{ $primaryUrl ?: '#' }}"
                        class="rounded-full px-5 py-3 text-sm font-medium transition {{ $primaryButtonClass }}"
                    >
                        {{ $primaryLabel }}
                    </a>
                @endif

                @if (filled($secondaryLabel))
                    <a
                        href="{{ $secondaryUrl ?: '#' }}"
                        class="rounded-full border border-current/15 px-5 py-3 text-sm font-medium transition hover:border-current/40"
                    >
                        {{ $secondaryLabel }}
                    </a>
                @endif
            </div>
        @endif
    </div>
</section>
