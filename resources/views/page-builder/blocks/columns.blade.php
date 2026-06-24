@php
    $cols = is_array($columns ?? null) ? $columns : [];
    $n = count($cols);
    $gap = $gap ?? 'md';
    $gapClass = match ($gap) {
        'sm' => 'gap-4',
        'lg' => 'gap-10',
        default => 'gap-6',
    };
    $gridCols = match ($n) {
        1 => '',
        2 => 'md:grid-cols-2',
        3 => 'md:grid-cols-3',
        default => 'md:grid-cols-2 lg:grid-cols-4',
    };
@endphp
@if (! empty($cols))
    <div class="grid {{ $gridCols }} {{ $gapClass }}">
        @foreach ($cols as $col)
            <div>
                <x-page-builder :blocks="$col['blocks'] ?? []" class="!gap-6" />
            </div>
        @endforeach
    </div>
@endif
