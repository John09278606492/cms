@php
    $cols = $columns ?? '3';
    $grid = match ($cols) {
        '2' => 'sm:grid-cols-2',
        '4' => 'sm:grid-cols-2 lg:grid-cols-4',
        default => 'sm:grid-cols-2 lg:grid-cols-3',
    };
    $items = is_array($items ?? null) ? $items : [];
@endphp
<section class="rounded-3xl border border-stone-200 bg-white px-8 py-10">
    @if (! empty($eyebrow))
        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-amber-700">{{ $eyebrow }}</p>
    @endif
    @if (! empty($heading))
        <h2 class="mt-2 text-3xl font-semibold tracking-tight text-stone-950">{{ $heading }}</h2>
    @endif
    @if (! empty($intro))
        <p class="mt-3 max-w-2xl leading-7 text-stone-600">{{ $intro }}</p>
    @endif
    @if (! empty($items))
        <div class="mt-8 grid gap-5 {{ $grid }}">
            @foreach ($items as $item)
                <div class="rounded-2xl border border-stone-200 bg-stone-50 p-5">
                    @if (! empty($item['emoji']))
                        <div class="mb-3 text-2xl">{{ $item['emoji'] }}</div>
                    @endif
                    <h3 class="font-semibold text-stone-950">{{ $item['title'] ?? '' }}</h3>
                    @if (! empty($item['description']))
                        <p class="mt-2 text-sm leading-6 text-stone-600">{{ $item['description'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</section>
