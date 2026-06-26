@php
    $surface = $surface ?? 'contrast';
    $align = $align ?? 'center';
    $container = match ($surface) {
        'contrast' => 'bg-stone-950 text-white',
        'minimal' => 'bg-transparent text-stone-900',
        default => 'bg-amber-50 text-stone-900',
    };
    $alignClass = $align === 'center' ? 'items-center text-center mx-auto' : ($align === 'right' ? 'items-end text-right ml-auto' : 'items-start text-left');
    $eyebrowClass = $surface === 'contrast' ? 'pb-eyebrow-on-dark' : 'pb-eyebrow';
    $copyClass = $surface === 'contrast' ? 'text-stone-300' : 'text-stone-600';
    $primaryBtn = $surface === 'contrast' ? 'bg-white text-stone-950 hover:bg-stone-100' : 'pb-btn-primary';
@endphp
<section class="rounded-3xl px-8 py-14 {{ $container }}">
    <div class="flex w-full max-w-3xl flex-col gap-5 {{ $alignClass }}">
        @if (! empty($eyebrow))
            <p class="text-xs font-semibold uppercase tracking-[0.35em] {{ $eyebrowClass }}">{{ $eyebrow }}</p>
        @endif
        @if (! empty($heading))
            <h2 class="text-4xl font-semibold tracking-tight sm:text-5xl">{{ $heading }}</h2>
        @endif
        @if (! empty($copy))
            <p class="max-w-2xl text-lg leading-8 {{ $copyClass }}">{{ $copy }}</p>
        @endif
        @if (! empty($primary_label) || ! empty($secondary_label))
            <div class="mt-2 flex flex-wrap gap-3 {{ $align === 'center' ? 'justify-center' : '' }}">
                @if (! empty($primary_label))
                    <a href="{{ $primary_url ?? '#' }}" class="rounded-full px-5 py-3 text-sm font-medium {{ $primaryBtn }}">{{ $primary_label }}</a>
                @endif
                @if (! empty($secondary_label))
                    <a href="{{ $secondary_url ?? '#' }}" class="rounded-full border border-current/20 px-5 py-3 text-sm font-medium">{{ $secondary_label }}</a>
                @endif
            </div>
        @endif
    </div>
</section>
