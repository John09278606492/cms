{{-- One block on the canvas: selectable, draggable, with a toolbar. Selection
     uses an inset ring (box-shadow) so it never shifts the layout, keeping the
     canvas a faithful preview of the live site. Containers/columns recurse. --}}
@php
    $type = $block['type'] ?? '';
    $isSel = $selectedPath === $path;
    $isLayout = in_array($type, ['container', 'columns'], true);
@endphp
<div wire:key="cv-{{ $path }}"
     wire:click.stop="select('{{ $path }}')"
     draggable="true"
     x-on:dragstart.stop="dragType = 'move'; dragFromPath = '{{ $path }}'; $event.dataTransfer.effectAllowed = 'move'"
     x-on:dragend="reset()"
     class="group/cv relative cursor-pointer transition {{ $isSel ? 'ring-2 ring-inset ring-amber-500' : 'hover:ring-2 hover:ring-inset hover:ring-amber-300' }}">
    {{-- Toolbar (absolute, so it never affects layout) --}}
    <div class="absolute right-1 top-1 z-30 flex items-center gap-0.5 rounded-lg bg-stone-900/90 p-0.5 text-white opacity-0 shadow-lg transition group-hover/cv:opacity-100 {{ $isSel ? '!opacity-100' : '' }}">
        <span class="flex cursor-grab items-center gap-1 px-1.5 text-[10px] font-medium text-stone-300 active:cursor-grabbing">@svg('heroicon-o-bars-2', 'h-3 w-3') {{ $labels[$type] ?? $type }}</span>
        <button wire:click.stop="moveUp('{{ $path }}')" title="Move up" class="rounded p-1 hover:bg-white/15">@svg('heroicon-o-chevron-up', 'h-3 w-3')</button>
        <button wire:click.stop="moveDown('{{ $path }}')" title="Move down" class="rounded p-1 hover:bg-white/15">@svg('heroicon-o-chevron-down', 'h-3 w-3')</button>
        <button wire:click.stop="duplicate('{{ $path }}')" title="Duplicate" class="rounded p-1 hover:bg-white/15">@svg('heroicon-o-document-duplicate', 'h-3 w-3')</button>
        <button wire:click.stop="remove('{{ $path }}')" title="Delete" class="rounded p-1 text-red-300 hover:bg-red-500/30">@svg('heroicon-o-trash', 'h-3 w-3')</button>
    </div>

    @if ($type === 'container')
        {{-- Children flow naturally (no editor box); empty state lives in canvas-list. --}}
        @include('livewire.partials.canvas-list', ['blocks' => $block['data']['blocks'] ?? [], 'listPath' => $path . '.data.blocks', 'labels' => $labels])
    @elseif ($type === 'columns')
        @php $cols = is_array($block['data']['columns'] ?? null) ? $block['data']['columns'] : []; @endphp
        <div class="grid gap-4" style="grid-template-columns: repeat({{ max(1, count($cols)) }}, minmax(0, 1fr));">
            @foreach ($cols as $c => $col)
                <div class="rounded border border-stone-100">
                    @include('livewire.partials.canvas-list', ['blocks' => $col['blocks'] ?? [], 'listPath' => $path . '.data.columns.' . $c . '.blocks', 'labels' => $labels])
                </div>
            @endforeach
        </div>
    @else
        {{-- Leaf widget: rendered flush, exactly as on the public site. --}}
        <div class="pointer-events-none">
            <x-page-builder :blocks="[$block]" />
        </div>
    @endif
</div>
