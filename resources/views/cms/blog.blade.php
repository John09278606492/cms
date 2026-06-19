@extends('layouts.site')

@section('content')
    <section class="mx-auto max-w-6xl px-6 py-16">
        <div class="max-w-3xl">
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-amber-600">{{ $settings->site_tagline ?? 'Journal' }}</p>
            <h1 class="mt-4 text-5xl font-semibold tracking-tight text-stone-950">Latest from {{ $settings->site_name ?? config('app.name') }}</h1>
            <p class="mt-4 text-lg leading-8 text-stone-600">
                {{ $settings->meta_description ?: ($settings->site_description ?: 'Stories, field notes, and updates.') }}
            </p>
        </div>

        <div class="mt-12 grid gap-6">
            @forelse ($posts as $post)
                <article class="rounded-3xl border border-stone-200 bg-white p-8 shadow-sm">
                    @if ($featuredImage = $post->featuredImageUrl())
                        <a href="{{ route('sites.blog.show', ['site' => $site, 'slug' => $post->slug]) }}" class="mb-6 block overflow-hidden rounded-2xl">
                            <img src="{{ $featuredImage }}" alt="{{ $post->title }}" class="h-72 w-full object-cover">
                        </a>
                    @endif
                    <p class="text-sm text-stone-500">{{ optional($post->published_at)->format('M d, Y') ?: $post->updated_at->format('M d, Y') }}</p>
                    <h2 class="mt-3 text-3xl font-semibold tracking-tight text-stone-950">
                        <a href="{{ route('sites.blog.show', ['site' => $site, 'slug' => $post->slug]) }}">{{ $post->title }}</a>
                    </h2>
                    <p class="mt-4 max-w-3xl text-base leading-8 text-stone-600">{{ $post->excerpt }}</p>
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-stone-300 bg-white p-8 text-sm text-stone-500">
                    No published posts yet.
                </div>
            @endforelse
        </div>

        <div class="mt-10">
            {{ $posts->links() }}
        </div>
    </section>
@endsection
