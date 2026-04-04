@extends('layouts.site')

@section('content')
    <article class="mx-auto max-w-4xl px-6 py-16">
        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-amber-600">Page</p>
        <h1 class="mt-4 text-5xl font-semibold tracking-tight text-stone-950">{{ $page->title }}</h1>

        @if (filled($page->excerpt))
            <p class="mt-6 text-lg leading-8 text-stone-600">{{ $page->excerpt }}</p>
        @endif

        @if ($featuredImage = $page->featuredImageUrl())
            <div class="mt-10 overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
                <img src="{{ $featuredImage }}" alt="{{ $page->title }}" class="h-auto w-full object-cover">
            </div>
        @endif

        @if (filled($page->content))
            <div class="cms-builder mt-10">
                {!! $page->renderContent() !!}
            </div>
        @endif
    </article>
@endsection
