@php
    $align = $align ?? 'center';
    $alignClass = match ($align) {
        'left' => 'items-start text-left',
        'right' => 'items-end text-right',
        default => 'items-center text-center',
    };
@endphp
<div class="flex flex-col gap-3 {{ $alignClass }}">
    @if (! empty($icon))
        <div class="text-5xl leading-none">{{ $icon }}</div>
    @endif
    @if (! empty($title))
        <h3 class="text-xl font-semibold text-stone-950">{{ $title }}</h3>
    @endif
    @if (! empty($text))
        <p class="max-w-prose leading-7 text-stone-600">{{ $text }}</p>
    @endif
    @if (! empty($link_label) && ! empty($link_url))
        <a href="{{ $link_url }}" class="pb-accent font-semibold transition hover:opacity-80">{{ $link_label }} &rarr;</a>
    @endif
</div>
