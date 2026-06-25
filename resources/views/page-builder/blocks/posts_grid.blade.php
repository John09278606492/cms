@php
    $site = request()->route('site');
    $count = (int) ($count ?? 3);
    $cols = $columns ?? '3';
    $grid = match ($cols) {
        '2' => 'sm:grid-cols-2',
        '4' => 'sm:grid-cols-2 lg:grid-cols-4',
        default => 'sm:grid-cols-2 lg:grid-cols-3',
    };
    $posts = $site instanceof \App\Models\Site
        ? $site->posts()->published()->with(['author', 'featuredImage'])->latest('published_at')->take($count)->get()
        : collect();
@endphp
<section>
    @if (! empty($heading))
        <h2 class="text-3xl font-semibold tracking-tight text-stone-950">{{ $heading }}</h2>
    @endif
    @if (! empty($intro))
        <p class="mt-3 max-w-2xl leading-7 text-stone-600">{{ $intro }}</p>
    @endif
    @if ($posts->isNotEmpty())
        <div class="mt-8 grid gap-6 {{ $grid }}">
            @foreach ($posts as $post)
                <a href="{{ route('sites.blog.show', [$site, $post->slug]) }}"
                   class="group flex flex-col overflow-hidden rounded-2xl border border-stone-200 bg-white transition hover:shadow-lg">
                    @if ($img = $post->featuredImageUrl())
                        <img src="{{ $img }}" alt="{{ $post->title }}" class="h-44 w-full object-cover">
                    @endif
                    <div class="flex flex-1 flex-col p-5">
                        @if ($post->published_at)
                            <p class="text-xs font-medium uppercase tracking-wide text-stone-400">{{ $post->published_at->format('M j, Y') }}</p>
                        @endif
                        <h3 class="mt-1 text-lg font-semibold text-stone-950 group-hover:text-amber-700">{{ $post->title }}</h3>
                        @if (! empty($post->excerpt))
                            <p class="mt-2 line-clamp-3 text-sm leading-6 text-stone-600">{{ $post->excerpt }}</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="mt-6 rounded-2xl border border-dashed border-stone-300 bg-stone-50 p-8 text-center text-sm text-stone-500">
            Your latest published blog posts will appear here.
        </div>
    @endif
</section>
