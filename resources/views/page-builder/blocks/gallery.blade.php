@php
    $cols = $columns ?? '3';
    $grid = match ($cols) {
        '2' => 'grid-cols-2',
        '4' => 'grid-cols-2 sm:grid-cols-4',
        default => 'grid-cols-2 sm:grid-cols-3',
    };
    $images = is_array($images ?? null) ? $images : [];
@endphp
@if (! empty($images))
    <div class="grid gap-3 {{ $grid }}">
        @foreach ($images as $img)
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($img) }}" alt="" class="h-44 w-full rounded-xl border border-stone-200 object-cover">
        @endforeach
    </div>
@else
    <div class="rounded-2xl border border-dashed border-stone-300 p-10 text-center text-sm text-stone-400">No images yet</div>
@endif
