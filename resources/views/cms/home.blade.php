@extends('layouts.site')

@section('content')
    @php
        $canManageSite = auth()->user()?->canAccessTenant($site) ?? false;
    @endphp

    <section class="mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-10 lg:grid-cols-[1.5fr,1fr]">
            <div class="space-y-6">
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-amber-600">Website Workspace</p>
                <h1 class="max-w-3xl text-5xl font-semibold tracking-tight text-stone-950">
                    {{ $settings->site_name ?? config('app.name') }}
                </h1>
                <p class="max-w-2xl text-lg leading-8 text-stone-600">
                    {{ $settings->site_description ?: 'Start with your own pages, posts, menus, and settings instead of placeholder content.' }}
                </p>
            </div>

            <div class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
                @if ($canManageSite)
                    <p class="text-sm font-semibold text-stone-500">Start Building</p>
                    <div class="mt-4 grid gap-3">
                        <p class="text-sm leading-7 text-stone-600">
                            Create a homepage or another page first, then publish posts and organize the navigation only when you are ready.
                        </p>
                        <a href="{{ url("/admin/site/{$site->slug}/pages") }}" class="rounded-2xl bg-stone-950 px-4 py-3 text-sm font-medium text-white">Create pages</a>
                        <a href="{{ url("/admin/site/{$site->slug}/posts") }}" class="rounded-2xl border border-stone-200 px-4 py-3 text-sm font-medium text-stone-700">Write posts</a>
                        <a href="{{ url("/admin/site/{$site->slug}/menus") }}" class="rounded-2xl border border-stone-200 px-4 py-3 text-sm font-medium text-stone-700">Manage menus</a>
                    </div>
                @elseif ($posts->isNotEmpty())
                    <p class="text-sm font-semibold text-stone-500">Published Content</p>
                    <div class="mt-4 space-y-3">
                        <p class="text-sm leading-7 text-stone-600">
                            This site already has live posts. Use the navigation to explore the published content.
                        </p>
                        <a href="{{ route('sites.blog.index', $site) }}" class="inline-flex rounded-2xl bg-stone-950 px-4 py-3 text-sm font-medium text-white">
                            View the blog
                        </a>
                    </div>
                @else
                    <p class="text-sm font-semibold text-stone-500">Coming Soon</p>
                    <div class="mt-4 space-y-3">
                        <p class="text-sm leading-7 text-stone-600">
                            This site is still being prepared. Published pages and posts will appear here once the owner is ready.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        @if ($posts->isNotEmpty())
            <div class="mt-16">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-semibold text-stone-950">Latest published content</h2>
                    <a href="{{ route('sites.blog.index', $site) }}" class="text-sm font-medium text-amber-700">View all posts</a>
                </div>

                <div class="mt-6 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($posts as $post)
                        <article class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
                            @if ($featuredImage = $post->featuredImageUrl())
                                <a href="{{ route('sites.blog.show', ['site' => $site, 'slug' => $post->slug]) }}" class="mb-4 block overflow-hidden rounded-2xl">
                                    <img src="{{ $featuredImage }}" alt="{{ $post->title }}" class="h-56 w-full object-cover">
                                </a>
                            @endif
                            <p class="text-sm text-stone-500">{{ optional($post->published_at)->format('M d, Y') ?: $post->updated_at->format('M d, Y') }}</p>
                            <h3 class="mt-3 text-xl font-semibold text-stone-950">
                                <a href="{{ route('sites.blog.show', ['site' => $site, 'slug' => $post->slug]) }}">{{ $post->title }}</a>
                            </h3>
                            <p class="mt-3 line-clamp-3 text-sm leading-7 text-stone-600">{{ $post->excerpt }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif
    </section>
@endsection
