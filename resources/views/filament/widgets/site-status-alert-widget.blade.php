@php
    $isDismissed = ! $this->shouldRenderAlert();

    $attributes = (new \Illuminate\View\ComponentAttributeBag)->class([
        'fi-wi-site-status-alert',
    ]);

    if ($isDismissed) {
        $attributes = $attributes->merge([
            'hidden' => true,
            'wire:poll.15s' => true,
        ], escape: false);
    }
@endphp

<x-filament-widgets::widget
    {{ $attributes }}
>
    @if (! $isDismissed)
        {{ $this->content }}
    @endif
</x-filament-widgets::widget>
