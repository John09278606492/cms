@php
    $containerClass = match ($width) {
        'full' => 'max-w-none',
        'wide' => 'max-w-5xl',
        default => 'max-w-3xl',
    };
    $styles = $styles ?? [];
    $rootStyle = trim(
        (filled($styles['paddingY'] ?? null) ? "padding-top: {$styles['paddingY']}px; padding-bottom: {$styles['paddingY']}px;" : '')
        . (filled($styles['bg'] ?? null) ? "background-color: {$styles['bg']};" : '')
    );
@endphp

@if (filled($imageUrl))
    <figure class="{{ $containerClass }}" style="{{ $rootStyle }}" data-lyp-root>
        <div class="overflow-hidden rounded-[2rem] border border-stone-200 bg-white shadow-sm">
            <img src="{{ $imageUrl }}" alt="{{ $alt ?: '' }}" class="h-auto w-full object-cover">
        </div>

        @if (filled($caption))
            <figcaption class="mt-4 text-sm leading-7 text-stone-500">{{ $caption }}</figcaption>
        @endif
    </figure>
@endif
