@php
    $query = $query ?? '';
    $zoom = (int) ($zoom ?? 13);
    $height = match ($height ?? 'md') {
        'sm' => 'h-64',
        'lg' => 'h-[32rem]',
        default => 'h-96',
    };
@endphp
@if (! empty($query))
    <div class="overflow-hidden rounded-2xl border border-stone-200">
        <iframe
            class="w-full {{ $height }}"
            style="border:0"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            title="Map of {{ $query }}"
            src="https://www.google.com/maps?q={{ urlencode($query) }}&z={{ $zoom }}&output=embed"></iframe>
    </div>
@endif
