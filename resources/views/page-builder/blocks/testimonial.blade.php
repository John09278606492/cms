@php
    $path = is_array($avatar ?? null) ? ($avatar[0] ?? null) : ($avatar ?? null);
    $src = ! empty($path) ? \Illuminate\Support\Facades\Storage::disk('public')->url($path) : null;
@endphp
<section class="mx-auto max-w-3xl rounded-3xl border border-stone-200 bg-white px-8 py-10 text-center">
    <blockquote class="text-xl leading-9 text-stone-800">&ldquo;{{ $quote ?? '' }}&rdquo;</blockquote>
    <div class="mt-6 flex items-center justify-center gap-3">
        @if ($src)
            <img src="{{ $src }}" alt="{{ $author ?? '' }}" class="h-12 w-12 rounded-full object-cover">
        @endif
        <div class="text-left">
            @if (! empty($author))
                <div class="font-semibold text-stone-950">{{ $author }}</div>
            @endif
            @if (! empty($role))
                <div class="text-sm text-stone-500">{{ $role }}</div>
            @endif
        </div>
    </div>
</section>
