@php
    $rating = (int) ($rating ?? 5);
    $align = $align ?? 'center';
    $justify = match ($align) {
        'left' => 'justify-start',
        'right' => 'justify-end',
        default => 'justify-center',
    };
    $text = match ($align) {
        'left' => 'text-left',
        'right' => 'text-right',
        default => 'text-center',
    };
@endphp
<div class="{{ $text }}">
    <div class="flex {{ $justify }} gap-1 text-2xl text-amber-400" role="img" aria-label="{{ $rating }} out of 5">
        @for ($i = 1; $i <= 5; $i++)
            <span>{{ $i <= $rating ? '★' : '☆' }}</span>
        @endfor
    </div>
    @if (! empty($label))
        <p class="mt-2 leading-7 text-stone-600">{{ $label }}</p>
    @endif
</div>
