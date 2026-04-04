@php
    $depth = $depth ?? 0;
@endphp

@foreach ($items as $item)
    @php
        $title = $item->title ?? '';
        $url = $item->url ?? null;
        $children = collect($item->children ?? []);
        $isActive = method_exists($item, 'isActiveOrHasActiveChild') ? $item->isActiveOrHasActiveChild() : false;
        $target = $item->target instanceof \BackedEnum ? $item->target->value : ($item->target ?? null);
        $rel = filled($item->rel ?? null) ? $item->rel : ($target === '_blank' ? 'noopener noreferrer' : null);
        $classes = $item->classes ?? null;
    @endphp
    <div class="{{ $depth > 0 ? 'mt-2 pl-4' : '' }}">
        @if (filled($url))
            <a
                href="{{ $url }}"
                @if (filled($target)) target="{{ $target }}" @endif
                @if (filled($rel)) rel="{{ $rel }}" @endif
                @class([
                    'inline-flex text-sm transition hover:text-stone-950',
                    $isActive ? 'text-amber-700' : 'text-stone-600',
                    $classes,
                ])
            >
                {{ $title }}
            </a>
        @else
            <span @class([
                'inline-flex text-sm',
                $isActive ? 'text-amber-700' : 'text-stone-600',
                $classes,
            ])>
                {{ $title }}
            </span>
        @endif

        @if ($children->isNotEmpty())
            @include('layouts.partials.site-footer-menu-items', [
                'items' => $children,
                'depth' => $depth + 1,
            ])
        @endif
    </div>
@endforeach
