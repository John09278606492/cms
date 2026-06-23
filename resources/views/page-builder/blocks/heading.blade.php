@php
    $level = $level ?? 'h2';
    $tag = in_array($level, ['h1', 'h2', 'h3', 'h4'], true) ? $level : 'h2';
    $align = $align ?? 'left';
    $size = match ($tag) {
        'h1' => 'text-5xl',
        'h2' => 'text-4xl',
        'h3' => 'text-2xl',
        default => 'text-xl',
    };
@endphp
<{{ $tag }} class="font-semibold tracking-tight text-stone-950 {{ $size }}" style="text-align: {{ $align }};@if (! empty($color)) color: {{ $color }};@endif">{{ $text ?? '' }}</{{ $tag }}>
