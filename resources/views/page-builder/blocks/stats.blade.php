@php
    $cols = $columns ?? '3';
    $grid = match ($cols) {
        '2' => 'sm:grid-cols-2',
        '4' => 'grid-cols-2 sm:grid-cols-4',
        default => 'grid-cols-1 sm:grid-cols-3',
    };
    $items = is_array($items ?? null) ? $items : [];
@endphp
<section class="rounded-3xl border border-stone-200 bg-white px-8 py-10 text-center">
    @if (! empty($heading))
        <h2 class="text-2xl font-semibold tracking-tight text-stone-950">{{ $heading }}</h2>
    @endif
    @if (! empty($items))
        <div class="mt-6 grid gap-6 {{ $grid }}">
            @foreach ($items as $item)
                <div>
                    <div class="text-4xl font-semibold text-amber-600">{{ $item['value'] ?? '' }}</div>
                    <div class="mt-1 text-sm text-stone-500">{{ $item['label'] ?? '' }}</div>
                </div>
            @endforeach
        </div>
    @endif
</section>
