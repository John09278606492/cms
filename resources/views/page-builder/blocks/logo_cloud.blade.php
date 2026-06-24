@php
    $logos = is_array($logos ?? null) ? $logos : [];
    $grayscale = $grayscale ?? true;
@endphp
<section class="text-center">
    @if (! empty($heading))
        <h2 class="mb-8 text-sm font-semibold uppercase tracking-[0.25em] text-stone-500">{{ $heading }}</h2>
    @endif
    @if (! empty($logos))
        <div class="flex flex-wrap items-center justify-center gap-x-12 gap-y-8">
            @foreach ($logos as $logo)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($logo) }}"
                     alt="Logo"
                     class="h-10 w-auto object-contain opacity-80 transition {{ $grayscale ? 'grayscale hover:grayscale-0 hover:opacity-100' : '' }}">
            @endforeach
        </div>
    @endif
</section>
