@php
    $side = $image_side ?? 'left';
    $imgFirst = $side !== 'right';
    $path = is_array($image ?? null) ? ($image[0] ?? null) : ($image ?? null);
    $src = ! empty($path) ? \Illuminate\Support\Facades\Storage::disk('public')->url($path) : null;
@endphp
<div class="grid items-center gap-8 md:grid-cols-2">
    <div class="{{ $imgFirst ? '' : 'md:order-2' }}">
        @if ($src)
            <img src="{{ $src }}" alt="{{ $heading ?? '' }}" class="w-full rounded-2xl border border-stone-200 object-cover">
        @else
            <div class="rounded-2xl border border-dashed border-stone-300 p-10 text-center text-sm text-stone-400">No image selected</div>
        @endif
    </div>
    <div class="{{ $imgFirst ? '' : 'md:order-1' }}">
        @if (! empty($heading))
            <h2 class="text-3xl font-semibold tracking-tight text-stone-950">{{ $heading }}</h2>
        @endif
        <div class="cms-prose mt-3 leading-8 text-stone-700">
            {!! \Filament\Forms\Components\RichEditor\RichContentRenderer::make($body ?? '')->toHtml() !!}
        </div>
        @if (! empty($button_label))
            <a href="{{ $button_url ?? '#' }}" class="mt-5 inline-flex rounded-full bg-stone-950 px-5 py-3 text-sm font-medium text-white transition hover:bg-stone-800">{{ $button_label }}</a>
        @endif
    </div>
</div>
