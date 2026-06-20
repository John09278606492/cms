@php
    $containerClass = $width === 'wide' ? 'max-w-4xl' : 'max-w-3xl';
    $styles = $styles ?? [];
    $rootStyle = trim(
        (filled($styles['paddingY'] ?? null) ? "padding-top: {$styles['paddingY']}px; padding-bottom: {$styles['paddingY']}px;" : '')
        . (filled($styles['bg'] ?? null) ? "background-color: {$styles['bg']};" : '')
    );
    $headingStyle = trim(
        (filled($styles['headingSize'] ?? null) ? "font-size: {$styles['headingSize']}px; line-height: 1.15;" : '')
        . (filled($styles['headingColor'] ?? null) ? "color: {$styles['headingColor']};" : '')
    );
@endphp

<section class="{{ $containerClass }}">
    <div class="rounded-[2rem] border border-stone-200 bg-white px-8 py-10 shadow-sm" style="{{ $rootStyle }}" data-lyp-root>
        @if (filled($heading))
            <h2 class="text-3xl font-semibold tracking-tight text-stone-950" style="{{ $headingStyle }}" data-layup-edit="heading">{{ $heading }}</h2>
        @endif

        <div class="cms-rich-content {{ filled($heading) ? 'mt-6' : '' }}" data-layup-edit="content" data-layup-edit-html>
            {!! $content !!}
        </div>
    </div>
</section>
