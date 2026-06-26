@php
    $labels = collect($this->palette)->pluck('label', 'name');
    $grouped = collect($this->palette)->groupBy('group');
    $canvasWidth = match ($device) {
        'mobile' => 'max-w-[375px]',
        'tablet' => 'max-w-[768px]',
        default => 'max-w-5xl',
    };
    $backUrl = \App\Filament\Resources\Pages\PageResource::getUrl('edit', ['record' => $pageId], panel: 'admin', tenant: $site);
@endphp
<div class="flex h-screen flex-col" x-data="{
    dragType: null,
    dragName: null,
    dragFromPath: null,
    overKey: null,
    handleDropAt(listPath, index) {
        if (this.dragType === 'add' && this.dragName) {
            $wire.insertInto(listPath, this.dragName, index);
        } else if (this.dragType === 'move' && this.dragFromPath !== null) {
            $wire.moveTo(this.dragFromPath, listPath, index);
        }
        this.reset();
    },
    reset() { this.dragType = null; this.dragName = null; this.dragFromPath = null; this.overKey = null; },
}">
    {{-- Top bar --}}
    <header class="flex items-center justify-between gap-4 border-b border-stone-200 bg-white px-4 py-2.5">
        <div class="flex items-center gap-3">
            <a href="{{ $backUrl }}" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-sm font-medium text-stone-600 hover:bg-stone-100">
                @svg('heroicon-o-arrow-left', 'h-4 w-4') Exit
            </a>
            <span class="text-sm font-semibold text-stone-900">{{ $pageTitle }}</span>
            @if ($dirty)
                <span class="inline-flex items-center gap-1 text-xs text-amber-600"><span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> Unsaved</span>
            @else
                <span class="inline-flex items-center gap-1 text-xs text-stone-400">@svg('heroicon-o-check', 'h-3.5 w-3.5') Saved</span>
            @endif
        </div>

        <div class="flex items-center gap-1 rounded-xl bg-stone-100 p-1">
            @foreach (['desktop' => 'heroicon-o-computer-desktop', 'tablet' => 'heroicon-o-device-tablet', 'mobile' => 'heroicon-o-device-phone-mobile'] as $d => $icon)
                <button wire:click="$set('device', '{{ $d }}')" title="{{ ucfirst($d) }}"
                        class="rounded-lg p-1.5 transition {{ $device === $d ? 'bg-white text-stone-900 shadow-sm' : 'text-stone-500 hover:text-stone-800' }}">
                    @svg($icon, 'h-4 w-4')
                </button>
            @endforeach
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('sites.pages.show', [$site, \App\Models\Page::find($pageId)?->slug]) }}" target="_blank"
               class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-sm font-medium text-stone-600 hover:bg-stone-100">
                @svg('heroicon-o-eye', 'h-4 w-4') Preview
            </a>
            <button wire:click="save" wire:loading.attr="disabled"
                    class="pb-btn-primary inline-flex items-center gap-1.5 rounded-lg px-4 py-1.5 text-sm font-semibold disabled:opacity-60">
                <span wire:loading.remove wire:target="save">Save</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
        </div>
    </header>

    <div class="flex min-h-0 flex-1">
        {{-- Palette --}}
        <aside class="w-60 shrink-0 overflow-y-auto border-r border-stone-800 bg-stone-900 pb-6 text-stone-100">
            <div class="sticky top-0 z-10 border-b border-stone-800 bg-stone-900 px-3 py-3">
                <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Widgets</p>
            </div>
            @foreach ($grouped as $group => $items)
                <p class="px-3 pb-1 pt-4 text-[10px] font-semibold uppercase tracking-wider text-stone-500">{{ $group }}</p>
                <div class="grid grid-cols-2 gap-1.5 px-2">
                    @foreach ($items as $item)
                        <button wire:click="addBlock('{{ $item['name'] }}')"
                                draggable="true"
                                x-on:dragstart="dragType = 'add'; dragName = '{{ $item['name'] }}'; $event.dataTransfer.effectAllowed = 'copy'"
                                x-on:dragend="reset()"
                                class="flex cursor-grab flex-col items-center gap-1.5 rounded-lg border border-transparent bg-stone-800/60 px-2 py-3 text-center transition hover:border-stone-600 hover:bg-stone-800 active:cursor-grabbing">
                            @svg($item['icon'], 'h-5 w-5 text-stone-300')
                            <span class="text-[11px] leading-tight text-stone-300">{{ $item['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            @endforeach
        </aside>

        {{-- Canvas --}}
        <main class="flex-1 overflow-y-auto bg-stone-200 p-6">
            <div class="mx-auto {{ $canvasWidth }} transition-[max-width] duration-300">
                <div class="min-h-[70vh] overflow-hidden rounded-xl bg-white shadow-sm"
                     x-on:dragover.prevent="$event.dataTransfer.dropEffect = (dragType === 'add' ? 'copy' : 'move')">
                    @if (empty($blocks))
                        <div class="flex min-h-[70vh] flex-col items-center justify-center gap-3 p-10 text-center"
                             x-on:dragover.prevent.stop="overKey = ':0'"
                             x-on:drop.prevent.stop="handleDropAt('', 0)"
                             :class="overKey === ':0' && dragType ? 'bg-amber-50 ring-2 ring-inset ring-amber-300' : ''">
                            @svg('heroicon-o-square-3-stack-3d', 'h-10 w-10 text-stone-300')
                            <p class="text-stone-500">Your page is empty.</p>
                            <p class="text-sm text-stone-400">Drag a widget here, or click one on the left to start building.</p>
                        </div>
                    @else
                        {{-- Mirror the live site's content container (max-w-4xl px-6). --}}
                        <div class="mx-auto max-w-4xl px-6 py-8">
                            @include('livewire.partials.canvas-list', ['blocks' => $blocks, 'listPath' => '', 'labels' => $labels])
                        </div>
                    @endif
                </div>
            </div>
        </main>

        {{-- Inspector --}}
        <aside class="w-80 shrink-0 overflow-y-auto border-l border-stone-200 bg-white">
            <div class="border-b border-stone-200 px-4 py-3">
                <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Inspector</p>
            </div>
            @php $sel = $this->blockAt($selectedPath); @endphp
            @if ($sel)
                @php
                    $selData = is_array($sel['data'] ?? null) ? $sel['data'] : [];
                    $selPath = 'blocks.' . $selectedPath . '.data';
                    $contentFields = \App\PageBuilder\BlockFields::for($sel['type']);
                    $isNested = str_contains((string) $selectedPath, '.data.');
                    $parentPath = $isNested ? strstr((string) $selectedPath, '.data.', true) : null;
                @endphp
                <div wire:key="inspector-{{ $selectedPath }}" class="p-4">
                    @if ($isNested && $parentPath !== null)
                        <button wire:click="select('{{ $parentPath }}')" class="mb-3 inline-flex items-center gap-1 text-xs font-medium text-amber-700 hover:text-amber-800">
                            @svg('heroicon-o-arrow-up-left', 'h-3.5 w-3.5') Back to container
                        </button>
                    @endif
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-semibold text-stone-900">{{ $labels[$sel['type']] ?? $sel['type'] }}</h3>
                    </div>
                    <div class="mt-3 grid grid-cols-4 gap-1.5">
                        <button wire:click="moveUp('{{ $selectedPath }}')" title="Move up" class="rounded-lg border border-stone-200 py-2 hover:bg-stone-50">@svg('heroicon-o-chevron-up', 'mx-auto h-4 w-4')</button>
                        <button wire:click="moveDown('{{ $selectedPath }}')" title="Move down" class="rounded-lg border border-stone-200 py-2 hover:bg-stone-50">@svg('heroicon-o-chevron-down', 'mx-auto h-4 w-4')</button>
                        <button wire:click="duplicate('{{ $selectedPath }}')" title="Duplicate" class="rounded-lg border border-stone-200 py-2 hover:bg-stone-50">@svg('heroicon-o-document-duplicate', 'mx-auto h-4 w-4')</button>
                        <button wire:click="remove('{{ $selectedPath }}')" title="Delete" class="rounded-lg border border-red-200 py-2 text-red-600 hover:bg-red-50">@svg('heroicon-o-trash', 'mx-auto h-4 w-4')</button>
                    </div>

                    {{-- Content fields --}}
                    @if (! empty($contentFields))
                        <div class="mt-5 space-y-3">
                            <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Content</p>
                            @foreach ($contentFields as $field)
                                @include('livewire.partials.inspector-field', ['field' => $field, 'path' => $selPath, 'data' => $selData])
                            @endforeach
                        </div>
                    @endif

                    {{-- Container: manage the widgets inside it --}}
                    @if ($sel['type'] === 'container')
                        <div class="mt-5 space-y-2">
                            <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Contents</p>
                            <div class="rounded-lg border border-stone-200 bg-stone-50 p-2.5">
                                @include('livewire.partials.child-list', ['listPath' => $selectedPath . '.data.blocks', 'childBlocks' => $selData['blocks'] ?? [], 'labels' => $labels])
                            </div>
                        </div>
                    @endif

                    {{-- Columns container: manage the widgets inside each column --}}
                    @if ($sel['type'] === 'columns')
                        @php $cols = is_array($selData['columns'] ?? null) ? $selData['columns'] : []; @endphp
                        <div class="mt-5 space-y-3">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Columns &amp; contents</p>
                                <button wire:click="addColumn('{{ $selectedPath }}')" class="text-xs font-medium text-amber-700 hover:text-amber-800">+ Column</button>
                            </div>
                            @foreach ($cols as $c => $col)
                                <div wire:key="col-{{ $selectedPath }}-{{ $c }}" class="rounded-lg border border-stone-200 bg-stone-50 p-2.5">
                                    <div class="mb-1.5 flex items-center justify-between">
                                        <span class="text-[11px] font-semibold uppercase tracking-wide text-stone-400">Column {{ $c + 1 }}</span>
                                        @if (count($cols) > 1)
                                            <button wire:click="removeColumn('{{ $selectedPath }}', {{ $c }})" class="text-xs text-red-500 hover:text-red-700">Remove</button>
                                        @endif
                                    </div>
                                    @include('livewire.partials.child-list', ['listPath' => $selectedPath . '.data.columns.' . $c . '.blocks', 'childBlocks' => is_array($col['blocks'] ?? null) ? $col['blocks'] : [], 'labels' => $labels])
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Design fields (collapsible) --}}
                    <div x-data="{ open: false }" class="mt-5 border-t border-stone-200 pt-4">
                        <button type="button" x-on:click="open = !open" class="flex w-full items-center justify-between text-xs font-semibold uppercase tracking-wider text-stone-400 hover:text-stone-600">
                            <span>Design</span>
                            <span x-text="open ? '–' : '+'"></span>
                        </button>
                        <div x-show="open" x-cloak class="mt-3 space-y-3">
                            @foreach (\App\PageBuilder\BlockFields::design() as $field)
                                @include('livewire.partials.inspector-field', ['field' => $field, 'path' => $selPath, 'data' => $selData])
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="p-6 text-center text-sm text-stone-400">
                    Select a block on the canvas to see its options.
                </div>
            @endif
        </aside>
    </div>

    @script
    <script>
        // Autosave every 20s while there are unsaved changes.
        setInterval(() => {
            if ($wire.dirty) { $wire.save(); }
        }, 20000);

        // Friendly nudge when leaving with unsaved changes.
        window.addEventListener('beforeunload', (e) => {
            if ($wire.dirty) { e.preventDefault(); e.returnValue = ''; }
        });
    </script>
    @endscript
</div>
