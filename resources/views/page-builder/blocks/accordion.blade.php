@php
    $items = is_array($items ?? null) ? $items : [];
@endphp
<section class="mx-auto max-w-3xl">
    @if (! empty($heading))
        <h2 class="mb-5 text-3xl font-semibold tracking-tight text-stone-950">{{ $heading }}</h2>
    @endif
    @if (! empty($items))
        <div class="divide-y divide-stone-200 overflow-hidden rounded-2xl border border-stone-200 bg-white">
            @foreach ($items as $item)
                <details class="group p-5">
                    <summary class="flex cursor-pointer list-none items-center justify-between font-medium text-stone-900">
                        <span>{{ $item['question'] ?? '' }}</span>
                        <span class="ml-4 text-stone-400 transition group-open:rotate-180">&#9662;</span>
                    </summary>
                    <div class="cms-prose mt-3 leading-7 text-stone-600">
                        {!! \Filament\Forms\Components\RichEditor\RichContentRenderer::make($item['answer'] ?? '')->toHtml() !!}
                    </div>
                </details>
            @endforeach
        </div>
    @endif
</section>
