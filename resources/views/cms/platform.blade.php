@extends('layouts.site')

@section('content')
    @if (filled($cmsForgeBanner ?? null))
        @include('filament.partials.cms-forge-banner', ['banner' => $cmsForgeBanner])
    @endif

    <section class="mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-10 lg:grid-cols-[1.4fr,1fr]">
            <div class="space-y-6">
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-amber-600">Two panels</p>
                <h1 class="max-w-3xl text-5xl font-semibold tracking-tight text-stone-950">
                    A platform console for super admins and a separate workspace for site owners.
                </h1>
                <p class="max-w-2xl text-lg leading-8 text-stone-600">
                    Super admins manage sites, users, and activity from a dedicated Filament panel, while site owners stay inside their own tenant workspace for pages, posts, menus, media, and settings.
                </p>
                <div class="flex flex-wrap gap-3">
                    @if (filled($platformLoginUrl))
                        <a href="{{ $platformLoginUrl }}" class="rounded-full bg-stone-950 px-5 py-3 text-sm font-medium text-white">Platform login</a>
                    @endif
                    @if (filled($siteOwnerLoginUrl))
                        <a href="{{ $siteOwnerLoginUrl }}" class="rounded-full border border-stone-300 px-5 py-3 text-sm font-medium text-stone-700">Site owner login</a>
                    @endif
                    @if (filled($siteRegistrationUrl))
                        <a href="{{ $siteRegistrationUrl }}" class="rounded-full border border-amber-300 bg-amber-50 px-5 py-3 text-sm font-medium text-amber-900">Create an account</a>
                    @endif
                </div>
            </div>

            <div class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-semibold text-stone-500">Available sites</p>
                <div class="mt-4 grid gap-3">
                    @forelse ($sites as $site)
                        <a href="{{ route('sites.home', $site) }}" class="rounded-2xl border border-stone-200 px-4 py-4 transition hover:border-stone-950">
                            <p class="font-medium text-stone-950">{{ $site->name }}</p>
                            <p class="mt-1 text-sm text-stone-500">/sites/{{ $site->slug }}</p>
                        </a>
                    @empty
                        <div class="rounded-2xl border border-dashed border-stone-300 px-4 py-6 text-sm text-stone-500">
                            No active sites yet. Create the first tenant from the admin panel.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
@endsection
