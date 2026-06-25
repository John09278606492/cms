@php
    $items = is_array($items ?? null) ? $items : [];
@endphp
<section class="mx-auto max-w-3xl">
    @if (! empty($heading))
        <h2 class="mb-6 text-3xl font-semibold tracking-tight text-stone-950">{{ $heading }}</h2>
    @endif
    @if (! empty($items))
        <div class="space-y-5">
            @foreach ($items as $item)
                @php $pct = max(0, min(100, (int) ($item['percent'] ?? 0))); $color = $item['color'] ?? null; @endphp
                <div>
                    <div class="mb-1 flex items-center justify-between text-sm font-medium text-stone-700">
                        <span>{{ $item['label'] ?? '' }}</span>
                        <span>{{ $pct }}%</span>
                    </div>
                    <div class="h-3 w-full overflow-hidden rounded-full bg-stone-200">
                        <div class="h-full rounded-full bg-stone-900 transition-[width] duration-700 ease-out"
                             style="width: {{ $pct }}%;@if ($color) background-color: {{ $color }};@endif"></div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>
