@php
    $width = $width ?? 'content';
    $max = match ($width) {
        'wide' => 'max-w-4xl',
        'full' => 'max-w-none',
        default => 'max-w-2xl',
    };
    $rounded = ($rounded ?? true) ? 'rounded-2xl' : '';
    $path = is_array($image ?? null) ? ($image[0] ?? null) : ($image ?? null);
    $src = ! empty($path) ? \Illuminate\Support\Facades\Storage::disk('public')->url($path) : null;
@endphp
@if ($src)
    <figure class="{{ $max }}">
        <img src="{{ $src }}" alt="{{ $alt ?? '' }}" class="w-full border border-stone-200 object-cover {{ $rounded }}">
        @if (! empty($caption))
            <figcaption class="mt-3 text-sm text-stone-500">{{ $caption }}</figcaption>
        @endif
    </figure>
@else
    <div class="rounded-2xl border border-dashed border-stone-300 p-10 text-center text-sm text-stone-400">No image selected</div>
@endif
