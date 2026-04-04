@php
    $containerClass = match ($width) {
        'full' => 'max-w-none',
        'wide' => 'max-w-5xl',
        default => 'max-w-3xl',
    };
@endphp

@if (filled($imageUrl))
    <figure class="{{ $containerClass }}">
        <div class="overflow-hidden rounded-[2rem] border border-stone-200 bg-white shadow-sm">
            <img src="{{ $imageUrl }}" alt="{{ $alt ?: '' }}" class="h-auto w-full object-cover">
        </div>

        @if (filled($caption))
            <figcaption class="mt-4 text-sm leading-7 text-stone-500">{{ $caption }}</figcaption>
        @endif
    </figure>
@endif
