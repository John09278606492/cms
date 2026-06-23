@php
    $url = $url ?? '';
    $embed = null;
    if (preg_match('~youtu\.?be(?:\.com)?/(?:watch\?v=|embed/|shorts/)?([\w-]{11})~', $url, $m)) {
        $embed = 'https://www.youtube.com/embed/' . $m[1];
    } elseif (preg_match('~vimeo\.com/(\d+)~', $url, $m)) {
        $embed = 'https://player.vimeo.com/video/' . $m[1];
    }
    $width = $width ?? 'content';
    $max = match ($width) {
        'wide' => 'max-w-4xl',
        'full' => 'max-w-none',
        default => 'max-w-2xl',
    };
@endphp
@if ($embed)
    <div class="{{ $max }} overflow-hidden rounded-2xl border border-stone-200">
        <div class="relative" style="padding-bottom: 56.25%">
            <iframe src="{{ $embed }}" class="absolute inset-0 h-full w-full" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
        </div>
    </div>
@else
    <div class="rounded-2xl border border-dashed border-stone-300 p-10 text-center text-sm text-stone-400">Add a YouTube or Vimeo URL</div>
@endif
