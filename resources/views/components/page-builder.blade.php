@props(['blocks' => []])
@php
    $blocks = is_array($blocks) ? $blocks : [];
@endphp
<div {{ $attributes->merge(['class' => 'flex flex-col gap-10']) }}>
    @foreach ($blocks as $block)
        @php
            $type = $block['type'] ?? null;
            $data = is_array($block['data'] ?? null) ? $block['data'] : [];
            $bg = $data['_bg'] ?? null;
            $pad = $data['_pad'] ?? 'none';
            $width = $data['_width'] ?? 'default';
            $align = $data['_align'] ?? null;
            $padClass = match ($pad) {
                'sm' => 'py-6',
                'md' => 'py-10',
                'lg' => 'py-16',
                'xl' => 'py-24',
                default => '',
            };
            $widthClass = match ($width) {
                'narrow' => 'mx-auto max-w-2xl',
                'wide' => 'mx-auto max-w-5xl',
                'full' => 'max-w-none',
                default => '',
            };
            $alignClass = match ($align) {
                'center' => 'text-center',
                'right' => 'text-right',
                'left' => 'text-left',
                default => '',
            };
            $hasDesign = ! empty($bg) || $pad !== 'none' || $width !== 'default' || ! empty($align);
        @endphp
        @if ($type && view()->exists('page-builder.blocks.' . $type))
            @if ($hasDesign)
                <div class="{{ $padClass }} {{ $alignClass }} {{ ! empty($bg) ? 'rounded-2xl px-6' : '' }}" @if (! empty($bg)) style="background-color: {{ $bg }};" @endif>
                    <div class="{{ $widthClass }}">
                        @include('page-builder.blocks.' . $type, $data)
                    </div>
                </div>
            @else
                @include('page-builder.blocks.' . $type, $data)
            @endif
        @endif
    @endforeach
</div>
