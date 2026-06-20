@php
    $gridClass = $columns === '2' ? 'md:grid-cols-2' : 'md:grid-cols-2 xl:grid-cols-3';
@endphp

<section class="rounded-[2rem] border border-stone-200 bg-white px-8 py-10 shadow-sm">
    @if (filled($eyebrow))
        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-amber-700" data-layup-edit="eyebrow">{{ $eyebrow }}</p>
    @endif

    @if (filled($heading))
        <h2 class="mt-3 text-3xl font-semibold tracking-tight text-stone-950" data-layup-edit="heading">{{ $heading }}</h2>
    @endif

    @if (filled($intro))
        <p class="mt-4 max-w-3xl text-base leading-8 text-stone-600" data-layup-edit="intro">{{ $intro }}</p>
    @endif

    @if (filled($items))
        <div class="mt-8 grid gap-5 {{ $gridClass }}">
            @foreach ($items as $item)
                <article class="rounded-[1.5rem] border border-stone-200 bg-stone-50 p-6">
                    @if (filled($item['emoji'] ?? null))
                        <div class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-xl shadow-sm ring-1 ring-stone-200">
                            {{ $item['emoji'] }}
                        </div>
                    @endif
                    <h3 class="text-lg font-semibold text-stone-950">{{ $item['title'] ?? '' }}</h3>
                    <p class="mt-3 text-sm leading-7 text-stone-600">{{ $item['description'] ?? '' }}</p>
                </article>
            @endforeach
        </div>
    @endif
</section>
