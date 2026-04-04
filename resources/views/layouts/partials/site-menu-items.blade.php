@php
    $depth = $depth ?? 0;
@endphp

@foreach ($items as $item)
    @php
        $title = $item->title ?? '';
        $url = $item->url ?? null;
        $children = collect($item->children ?? []);
        $hasChildren = $children->isNotEmpty();
        $isActive = method_exists($item, 'isActiveOrHasActiveChild') ? $item->isActiveOrHasActiveChild() : false;
        $target = $item->target instanceof \BackedEnum ? $item->target->value : ($item->target ?? null);
        $rel = filled($item->rel ?? null) ? $item->rel : ($target === '_blank' ? 'noopener noreferrer' : null);
        $classes = $item->classes ?? null;
        $baseClasses = $depth === 0
            ? 'text-sm font-medium transition hover:text-stone-950'
            : 'block rounded-xl px-3 py-2 text-sm transition hover:bg-stone-100 hover:text-stone-950';
        $stateClasses = $isActive ? 'text-amber-700' : 'text-stone-600';
    @endphp

    @if ($hasChildren)
        <div class="{{ $depth === 0 ? 'group/menu relative' : 'group/submenu relative' }}">
            <div class="flex items-center gap-2">
                @if (filled($url))
                    <a
                        href="{{ $url }}"
                        @if (filled($target)) target="{{ $target }}" @endif
                        @if (filled($rel)) rel="{{ $rel }}" @endif
                        @class([$baseClasses, $stateClasses, $classes])
                    >
                        {{ $title }}
                    </a>
                @else
                    <span @class([$baseClasses, $stateClasses, $classes])>{{ $title }}</span>
                @endif
                <span class="text-[10px] font-semibold uppercase tracking-[0.2em] text-stone-400">+</span>
            </div>

            <div class="{{ $depth === 0 ? 'invisible absolute left-0 top-full z-30 pt-4 opacity-0 transition duration-200 group-hover/menu:visible group-hover/menu:opacity-100 group-focus-within/menu:visible group-focus-within/menu:opacity-100' : 'mt-3 pl-4' }}">
                <div class="{{ $depth === 0 ? 'min-w-56 rounded-2xl border border-stone-200 bg-white p-3 shadow-xl shadow-stone-900/5' : 'border-l border-stone-200' }}">
                    @if (filled($url))
                        <a
                            href="{{ $url }}"
                            @if (filled($target)) target="{{ $target }}" @endif
                            @if (filled($rel)) rel="{{ $rel }}" @endif
                            class="block rounded-xl px-3 py-2 text-sm font-medium text-stone-950 transition hover:bg-stone-100"
                        >
                            {{ $title }}
                        </a>
                    @endif

                    <div class="{{ $depth === 0 ? 'space-y-1' : 'space-y-1 pl-3' }}">
                        @include('layouts.partials.site-menu-items', [
                            'items' => $children,
                            'depth' => $depth + 1,
                        ])
                    </div>
                </div>
            </div>
        </div>
    @elseif (filled($url))
        <a
            href="{{ $url }}"
            @if (filled($target)) target="{{ $target }}" @endif
            @if (filled($rel)) rel="{{ $rel }}" @endif
            @class([$baseClasses, $stateClasses, $classes])
        >
            {{ $title }}
        </a>
    @else
        <span @class([$baseClasses, $stateClasses, $classes])>{{ $title }}</span>
    @endif
@endforeach
