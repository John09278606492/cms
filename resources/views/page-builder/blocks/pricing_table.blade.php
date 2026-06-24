@php
    $plans = is_array($plans ?? null) ? $plans : [];
    $cols = $columns ?? '3';
    $grid = match ($cols) {
        '2' => 'sm:grid-cols-2',
        '4' => 'sm:grid-cols-2 lg:grid-cols-4',
        default => 'sm:grid-cols-2 lg:grid-cols-3',
    };
@endphp
<section>
    @if (! empty($eyebrow))
        <p class="text-center text-xs font-semibold uppercase tracking-[0.35em] text-amber-700">{{ $eyebrow }}</p>
    @endif
    @if (! empty($heading))
        <h2 class="mt-2 text-center text-3xl font-semibold tracking-tight text-stone-950">{{ $heading }}</h2>
    @endif
    @if (! empty($intro))
        <p class="mx-auto mt-3 max-w-2xl text-center leading-7 text-stone-600">{{ $intro }}</p>
    @endif
    @if (! empty($plans))
        <div class="mt-8 grid items-start gap-6 {{ $grid }}">
            @foreach ($plans as $plan)
                @php
                    $featured = ! empty($plan['featured']);
                    $features = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) ($plan['features'] ?? ''))));
                @endphp
                <div class="flex h-full flex-col rounded-3xl border p-7 {{ $featured ? 'border-stone-950 bg-stone-950 text-white shadow-xl' : 'border-stone-200 bg-white text-stone-900' }}">
                    @if ($featured)
                        <span class="mb-3 inline-flex w-fit rounded-full bg-amber-400 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-stone-950">Most popular</span>
                    @endif
                    <h3 class="text-lg font-semibold {{ $featured ? 'text-white' : 'text-stone-950' }}">{{ $plan['name'] ?? '' }}</h3>
                    <div class="mt-4 flex items-baseline gap-1">
                        <span class="text-4xl font-bold tracking-tight">{{ $plan['price'] ?? '' }}</span>
                        @if (! empty($plan['period']))
                            <span class="{{ $featured ? 'text-stone-400' : 'text-stone-500' }}">{{ $plan['period'] }}</span>
                        @endif
                    </div>
                    @if (! empty($plan['description']))
                        <p class="mt-3 text-sm leading-6 {{ $featured ? 'text-stone-300' : 'text-stone-600' }}">{{ $plan['description'] }}</p>
                    @endif
                    @if (! empty($features))
                        <ul class="mt-6 space-y-3 text-sm">
                            @foreach ($features as $feature)
                                <li class="flex items-start gap-2">
                                    <span class="mt-0.5 {{ $featured ? 'text-amber-400' : 'text-amber-600' }}">&#10003;</span>
                                    <span class="{{ $featured ? 'text-stone-200' : 'text-stone-700' }}">{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    @if (! empty($plan['button_label']) && ! empty($plan['button_url']))
                        <a href="{{ $plan['button_url'] }}"
                           class="mt-7 inline-flex w-full items-center justify-center rounded-full px-5 py-2.5 text-sm font-semibold transition {{ $featured ? 'bg-amber-400 text-stone-950 hover:bg-amber-300' : 'bg-stone-950 text-white hover:bg-stone-800' }}">
                            {{ $plan['button_label'] }}
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</section>
