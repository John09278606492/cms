@php
    $images = is_array($images ?? null) ? $images : [];
    $ratio = $ratio ?? 'video';
    $aspect = match ($ratio) {
        'wide' => 'aspect-[21/9]',
        'square' => 'aspect-square',
        default => 'aspect-video',
    };
@endphp
@if (! empty($images))
    <div class="pb-carousel group relative overflow-hidden rounded-2xl border border-stone-200 bg-stone-100"
         data-autoplay="{{ ($autoplay ?? true) ? '1' : '0' }}" data-interval="{{ (int) ($interval ?? 5000) }}">
        <div class="pb-carousel-track flex transition-transform duration-500 ease-out">
            @foreach ($images as $img)
                <div class="pb-carousel-slide w-full shrink-0 {{ $aspect }}">
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($img) }}" alt="" class="h-full w-full object-cover">
                </div>
            @endforeach
        </div>
        @if (count($images) > 1)
            <button type="button" data-carousel-prev aria-label="Previous"
                    class="absolute left-3 top-1/2 -translate-y-1/2 flex h-10 w-10 items-center justify-center rounded-full bg-white/80 text-stone-900 opacity-0 transition group-hover:opacity-100 hover:bg-white">&#8249;</button>
            <button type="button" data-carousel-next aria-label="Next"
                    class="absolute right-3 top-1/2 -translate-y-1/2 flex h-10 w-10 items-center justify-center rounded-full bg-white/80 text-stone-900 opacity-0 transition group-hover:opacity-100 hover:bg-white">&#8250;</button>
            <div class="pb-carousel-dots absolute inset-x-0 bottom-3 flex justify-center gap-2">
                @foreach ($images as $i => $img)
                    <button type="button" data-carousel-dot="{{ $i }}" aria-label="Slide {{ $i + 1 }}"
                            class="h-2.5 w-2.5 rounded-full bg-white/60 transition data-[active]:bg-white"></button>
                @endforeach
            </div>
        @endif
    </div>
@endif
