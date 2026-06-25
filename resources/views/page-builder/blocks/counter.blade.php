@php
    $items = is_array($items ?? null) ? $items : [];
    $cols = $columns ?? '3';
    $grid = match ($cols) {
        '2' => 'sm:grid-cols-2',
        '4' => 'sm:grid-cols-2 lg:grid-cols-4',
        default => 'sm:grid-cols-3',
    };
@endphp
<section>
    @if (! empty($heading))
        <h2 class="mb-8 text-center text-3xl font-semibold tracking-tight text-stone-950">{{ $heading }}</h2>
    @endif
    @if (! empty($items))
        <div class="grid gap-8 {{ $grid }} text-center">
            @foreach ($items as $item)
                <div>
                    <div class="text-4xl font-bold tracking-tight text-stone-950 sm:text-5xl">
                        {{ $item['prefix'] ?? '' }}<span class="pb-counter" data-target="{{ (int) ($item['value'] ?? 0) }}">{{ (int) ($item['value'] ?? 0) }}</span>{{ $item['suffix'] ?? '' }}
                    </div>
                    <div class="mt-2 text-sm font-medium uppercase tracking-wide text-stone-500">{{ $item['label'] ?? '' }}</div>
                </div>
            @endforeach
        </div>
    @endif
</section>
