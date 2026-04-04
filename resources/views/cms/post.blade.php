@extends('layouts.site')

@section('content')
    <article class="mx-auto max-w-4xl px-6 py-16">
        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-amber-600">Post</p>
        <h1 class="mt-4 text-5xl font-semibold tracking-tight text-stone-950">{{ $post->title }}</h1>

        <div class="mt-6 flex flex-wrap items-center gap-3 text-sm text-stone-500">
            <span>{{ optional($post->published_at)->format('M d, Y') ?: $post->updated_at->format('M d, Y') }}</span>
            @if ($post->author)
                <span>by {{ $post->author->name }}</span>
            @endif
        </div>

        @if (filled($post->excerpt))
            <p class="mt-6 text-lg leading-8 text-stone-600">{{ $post->excerpt }}</p>
        @endif

        @if ($featuredImage = $post->featuredImageUrl())
            <div class="mt-10 overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
                <img src="{{ $featuredImage }}" alt="{{ $post->title }}" class="h-auto w-full object-cover">
            </div>
        @endif

        @if (filled($post->content))
            <div class="cms-builder mt-10">
                {!! $post->renderContent() !!}
            </div>
        @endif
    </article>
@endsection
