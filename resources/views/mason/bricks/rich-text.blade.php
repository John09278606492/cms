@php
    $containerClass = $width === 'wide' ? 'max-w-4xl' : 'max-w-3xl';
@endphp

<section class="{{ $containerClass }}">
    <div class="rounded-[2rem] border border-stone-200 bg-white px-8 py-10 shadow-sm">
        @if (filled($heading))
            <h2 class="text-3xl font-semibold tracking-tight text-stone-950" data-layup-edit="heading">{{ $heading }}</h2>
        @endif

        <div class="cms-rich-content {{ filled($heading) ? 'mt-6' : '' }}" data-layup-edit="content" data-layup-edit-html>
            {!! $content !!}
        </div>
    </div>
</section>
