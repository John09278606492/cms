@php
    $style = $style ?? 'primary';
    $align = $align ?? 'left';
    $cls = match ($style) {
        'secondary' => 'bg-stone-200 text-stone-900 hover:bg-stone-300',
        'outline' => 'border border-stone-400 text-stone-800 hover:bg-stone-100',
        default => 'pb-btn-primary',
    };
    $wrap = $align === 'center' ? 'text-center' : ($align === 'right' ? 'text-right' : 'text-left');
@endphp
<div class="{{ $wrap }}">
    <a href="{{ $url ?? '#' }}" @if (! empty($new_tab)) target="_blank" rel="noopener" @endif class="inline-flex rounded-full px-5 py-3 text-sm font-medium transition {{ $cls }}">{{ $label ?? 'Button' }}</a>
</div>
