@php
    $theme = $theme ?? 'amber';
    $wrap = $theme === 'stone' ? 'bg-stone-950 text-white' : 'bg-amber-100 text-stone-900';
    $btn = $theme === 'stone' ? 'bg-white text-stone-950 hover:bg-stone-100' : 'pb-btn-primary';
    $copyCls = $theme === 'stone' ? 'text-stone-300' : 'text-stone-700';
@endphp
<section class="rounded-3xl px-8 py-12 text-center {{ $wrap }}">
    @if (! empty($eyebrow))
        <p class="text-xs font-semibold uppercase tracking-[0.35em]">{{ $eyebrow }}</p>
    @endif
    @if (! empty($heading))
        <h2 class="mt-2 text-3xl font-semibold tracking-tight">{{ $heading }}</h2>
    @endif
    @if (! empty($copy))
        <p class="mx-auto mt-3 max-w-2xl leading-7 {{ $copyCls }}">{{ $copy }}</p>
    @endif
    @if (! empty($button_label))
        <a href="{{ $button_url ?? '#' }}" class="mt-6 inline-flex rounded-full px-6 py-3 text-sm font-medium transition {{ $btn }}">{{ $button_label }}</a>
    @endif
</section>
