{{-- Edits a list of child widgets at $listPath (a dot-path into $blocks). --}}
@php $childBlocks = is_array($childBlocks ?? null) ? $childBlocks : []; @endphp
<div class="space-y-1">
    @forelse ($childBlocks as $j => $cb)
        <div wire:key="cl-{{ $listPath }}-{{ $j }}" class="flex items-center justify-between rounded-md border border-stone-200 bg-white px-2 py-1">
            <button wire:click="select('{{ $listPath . '.' . $j }}')" class="truncate text-left text-xs font-medium text-stone-700 hover:text-stone-950">{{ $labels[$cb['type']] ?? $cb['type'] }}</button>
            <span class="flex shrink-0 items-center gap-0.5 text-stone-400">
                <button wire:click="moveUp('{{ $listPath . '.' . $j }}')" title="Up" class="rounded p-0.5 hover:bg-stone-100">@svg('heroicon-o-chevron-up', 'h-3.5 w-3.5')</button>
                <button wire:click="moveDown('{{ $listPath . '.' . $j }}')" title="Down" class="rounded p-0.5 hover:bg-stone-100">@svg('heroicon-o-chevron-down', 'h-3.5 w-3.5')</button>
                <button wire:click="remove('{{ $listPath . '.' . $j }}')" title="Remove" class="rounded p-0.5 text-red-400 hover:bg-red-50">@svg('heroicon-o-x-mark', 'h-3.5 w-3.5')</button>
            </span>
        </div>
    @empty
        <p class="px-1 py-1 text-xs text-stone-400">No widgets yet.</p>
    @endforelse
</div>
<div x-data="{ w: '' }" class="mt-2 flex gap-1">
    <select x-model="w" class="w-full rounded-md border border-stone-300 px-2 py-1 text-xs text-stone-700">
        <option value="">Add widget…</option>
        @foreach ($this->palette as $p)
            @if (! in_array($p['name'], ['columns', 'container'], true))
                <option value="{{ $p['name'] }}">{{ $p['label'] }}</option>
            @endif
        @endforeach
    </select>
    <button x-on:click="if (w) { $wire.addInto('{{ $listPath }}', w); w = ''; }" class="shrink-0 rounded-md bg-stone-900 px-2.5 py-1 text-xs font-medium text-white hover:bg-stone-700">Add</button>
</div>
