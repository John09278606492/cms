@php
    $h = $height ?? 'md';
    $cls = match ($h) {
        'sm' => 'h-4',
        'lg' => 'h-16',
        'xl' => 'h-24',
        default => 'h-8',
    };
@endphp
<div class="{{ $cls }}" aria-hidden="true"></div>
