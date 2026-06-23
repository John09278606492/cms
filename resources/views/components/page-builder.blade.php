@props(['blocks' => []])
@php
    $blocks = is_array($blocks) ? $blocks : [];
@endphp
<div {{ $attributes->merge(['class' => 'flex flex-col gap-10']) }}>
    @foreach ($blocks as $block)
        @php
            $type = $block['type'] ?? null;
            $data = is_array($block['data'] ?? null) ? $block['data'] : [];
        @endphp
        @if ($type && view()->exists('page-builder.blocks.' . $type))
            @include('page-builder.blocks.' . $type, $data)
        @endif
    @endforeach
</div>
