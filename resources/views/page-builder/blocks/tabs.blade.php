@php
    $items = is_array($items ?? null) ? $items : [];
    $uid = 'tabs-' . \Illuminate\Support\Str::random(8);
@endphp
@if (! empty($items))
    <section class="mx-auto max-w-3xl" data-pb-tabs id="{{ $uid }}">
        <div class="flex flex-wrap gap-1 border-b border-stone-200" role="tablist">
            @foreach ($items as $item)
                <button type="button"
                        data-pb-tab="{{ $loop->index }}"
                        class="-mb-px rounded-t-lg border-b-2 px-4 py-2.5 text-sm font-medium transition {{ $loop->first ? 'border-stone-950 text-stone-950' : 'border-transparent text-stone-500 hover:text-stone-800' }}">
                    {{ $item['label'] ?? ('Tab ' . $loop->iteration) }}
                </button>
            @endforeach
        </div>
        <div class="pt-5">
            @foreach ($items as $item)
                <div data-pb-panel="{{ $loop->index }}" class="cms-prose leading-7 text-stone-700 {{ ! $loop->first ? 'pb-tab-hidden' : '' }}">
                    {!! \App\Support\RichText::render($item['content'] ?? null) !!}
                </div>
            @endforeach
        </div>
    </section>
    @once
        @push('scripts')
            <style>[data-pb-tabs] .pb-tab-hidden { display: none; }</style>
            <script>
                document.querySelectorAll('[data-pb-tabs]').forEach(function (root) {
                    var buttons = root.querySelectorAll('[data-pb-tab]');
                    var panels = root.querySelectorAll('[data-pb-panel]');
                    buttons.forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            var idx = btn.getAttribute('data-pb-tab');
                            buttons.forEach(function (b) {
                                var active = b.getAttribute('data-pb-tab') === idx;
                                b.classList.toggle('border-stone-950', active);
                                b.classList.toggle('text-stone-950', active);
                                b.classList.toggle('border-transparent', !active);
                                b.classList.toggle('text-stone-500', !active);
                            });
                            panels.forEach(function (p) {
                                p.classList.toggle('pb-tab-hidden', p.getAttribute('data-pb-panel') !== idx);
                            });
                        });
                    });
                });
            </script>
        @endpush
    @endonce
@endif
