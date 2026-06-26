@php
    $blocks = is_array($blocks ?? null) ? $blocks : [];
    $direction = $direction ?? 'column';
    $gap = $gap ?? 'md';
    $align = $align ?? 'stretch';
    $gapClass = match ($gap) {
        'sm' => 'gap-3',
        'lg' => 'gap-8',
        default => 'gap-5',
    };
    $alignClass = match ($align) {
        'start' => 'items-start',
        'center' => 'items-center',
        'end' => 'items-end',
        default => 'items-stretch',
    };
    $dirClass = $direction === 'row' ? 'flex-row flex-wrap' : 'flex-col';
@endphp
@if (! empty($blocks))
    <div class="flex {{ $dirClass }} {{ $gapClass }} {{ $alignClass }}">
        @foreach ($blocks as $child)
            <div class="{{ $direction === 'row' ? 'min-w-0 flex-1 basis-60' : '' }}">
                <x-page-builder :blocks="[$child]" :page="$__page ?? null" />
            </div>
        @endforeach
    </div>
@endif
