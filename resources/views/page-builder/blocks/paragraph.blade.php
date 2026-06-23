@php
    $width = $width ?? 'content';
    $max = match ($width) {
        'wide' => 'max-w-4xl',
        'full' => 'max-w-none',
        default => 'max-w-2xl',
    };
@endphp
<div class="cms-prose {{ $max }} leading-8 text-stone-700">
    {!! \Filament\Forms\Components\RichEditor\RichContentRenderer::make($content ?? '')->toHtml() !!}
</div>
