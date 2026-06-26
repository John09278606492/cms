{{-- Renders a draggable, selectable list of blocks at $listPath, with drop
     zones between every item. Used recursively for containers and columns. --}}
@php $blocks = is_array($blocks ?? null) ? $blocks : []; @endphp
<div>
    @foreach ($blocks as $i => $block)
        @php
            $childPath = $listPath === '' ? (string) $i : $listPath . '.' . $i;
            $zoneKey = $listPath . ':' . $i;
        @endphp
        <div class="transition-all"
             x-on:dragover.prevent.stop="overKey = '{{ $zoneKey }}'"
             x-on:drop.prevent.stop="handleDropAt('{{ $listPath }}', {{ $i }})"
             :class="overKey === '{{ $zoneKey }}' ? 'h-12 my-1 rounded-lg border-2 border-dashed border-amber-400 bg-amber-50' : (dragType !== null ? 'h-3' : 'h-4')"></div>
        @include('livewire.partials.canvas-block', ['block' => $block, 'path' => $childPath, 'labels' => $labels])
    @endforeach

    @if (empty($blocks))
        <div class="flex min-h-[90px] items-center justify-center rounded-lg text-xs text-stone-400"
             x-on:dragover.prevent.stop="overKey = '{{ $listPath }}:0'"
             x-on:drop.prevent.stop="handleDropAt('{{ $listPath }}', 0)"
             :class="overKey === '{{ $listPath }}:0' && dragType ? 'bg-amber-50 ring-2 ring-inset ring-amber-300' : ''">
            Drag widgets here
        </div>
    @else
        @php $endKey = $listPath . ':' . count($blocks); @endphp
        <div class="transition-all"
             x-on:dragover.prevent.stop="overKey = '{{ $endKey }}'"
             x-on:drop.prevent.stop="handleDropAt('{{ $listPath }}', {{ count($blocks) }})"
             :class="overKey === '{{ $endKey }}' ? 'h-12 my-1 rounded-lg border-2 border-dashed border-amber-400 bg-amber-50' : 'h-4'"></div>
    @endif
</div>
