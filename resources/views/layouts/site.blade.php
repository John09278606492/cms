<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $currentSite = $site ?? null;
        $currentPage = $page ?? null;
        $currentPost = $post ?? null;
        $headerMenuItems = $headerMenuItems ?? collect();
        $footerMenuItems = $footerMenuItems ?? collect();
        $canManageCurrentSite = $canManageCurrentSite ?? false;
        $metaTitle = $title
            ?? $currentPage?->meta_title
            ?? $currentPage?->title
            ?? $currentPost?->meta_title
            ?? $currentPost?->title
            ?? $settings->meta_title
            ?? $settings->site_name
            ?? config('app.name');
        $metaDescription = $description
            ?? $currentPage?->meta_description
            ?? $currentPage?->excerpt
            ?? $currentPost?->meta_description
            ?? $currentPost?->excerpt
            ?? $settings->meta_description
            ?? $settings->site_description;
        $metaImage = $image
            ?? $currentPage?->featuredImageUrl()
            ?? $currentPost?->featuredImageUrl()
            ?? $settings->defaultOgImageUrl()
            ?? $settings->siteLogoUrl();
        $logoUrl = $settings->siteLogoUrl();
        $faviconUrl = $settings->siteFaviconUrl();
        $dashboardUrl = $currentSite ? \Filament\Facades\Filament::getUrl($currentSite) : null;
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="{{ filled($metaImage) ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    @if (filled($metaImage))
        <meta property="og:image" content="{{ $metaImage }}">
        <meta name="twitter:image" content="{{ $metaImage }}">
    @endif
    @if (filled($faviconUrl))
        <link rel="icon" href="{{ $faviconUrl }}">
        <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @php
        $themeFontsUrl = $settings->googleFontsUrl();
        $headingFont = $settings->heading_font;
        $bodyFont = $settings->body_font;
        $brandPrimary = $settings->brand_primary;
        $brandAccent = $settings->brand_accent;
    @endphp
    @if ($themeFontsUrl)
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="{{ $themeFontsUrl }}" rel="stylesheet">
    @endif
    @if ($headingFont || $bodyFont || $brandPrimary || $brandAccent)
        <style>
            :root {
                @if ($brandPrimary) --brand-primary: {{ $brandPrimary }}; --brand-primary-hover: {{ $brandPrimary }}; @endif
                @if ($brandAccent) --brand-accent: {{ $brandAccent }}; --brand-accent-on-dark: {{ $brandAccent }}; @endif
                @if ($bodyFont) --font-body: '{{ $bodyFont }}', ui-sans-serif, system-ui, sans-serif; @endif
                @if ($headingFont) --font-heading: '{{ $headingFont }}', ui-sans-serif, system-ui, sans-serif; @endif
            }
            @if ($bodyFont) body { font-family: var(--font-body); } @endif
            @if ($headingFont) h1, h2, h3, h4, h5, h6 { font-family: var(--font-heading); } @endif
            @if ($brandPrimary) main a:not([class*="bg-"]) { color: var(--brand-primary); } @endif
        </style>
    @endif
</head>
<body class="min-h-screen bg-stone-100 text-stone-900">
    <div class="border-b border-stone-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-col gap-4 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <a href="{{ $currentSite ? route('sites.home', $currentSite) : route('platform.home') }}" class="flex items-center gap-4 text-stone-950">
                    @if (filled($logoUrl))
                        <img src="{{ $logoUrl }}" alt="{{ $settings->site_name ?? config('app.name') }}" class="h-12 w-12 rounded-2xl border border-stone-200 object-cover">
                    @endif
                    <span class="block">
                        <span class="block text-2xl font-semibold tracking-tight">{{ $settings->site_name ?? config('app.name') }}</span>
                        @if (filled($settings->site_tagline))
                            <span class="text-sm text-stone-500">{{ $settings->site_tagline }}</span>
                        @endif
                    </span>
                </a>
            </div>

            <div class="flex flex-col gap-3 lg:items-end">
                <nav class="flex flex-wrap items-center gap-5">
                    @if ($headerMenuItems->isNotEmpty())
                        @include('layouts.partials.site-menu-items', ['items' => $headerMenuItems])
                    @endif
                </nav>

                @if ($currentSite && $canManageCurrentSite)
                    <div class="flex flex-wrap items-center gap-4 text-sm font-medium text-stone-500">
                        <a href="{{ $dashboardUrl }}" class="rounded-full border border-stone-300 px-4 py-2 text-stone-600 transition hover:border-stone-950 hover:text-stone-950">
                            Dashboard
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <main>
        @yield('content')
    </main>

    <footer class="border-t border-stone-200 bg-white">
        <div class="mx-auto max-w-6xl px-6 py-10">
            <div class="grid gap-8 lg:grid-cols-[1.5fr,2fr]">
                <div>
                    <p class="text-xl font-semibold tracking-tight text-stone-950">{{ $settings->site_name ?? config('app.name') }}</p>
                    @if (filled($settings->site_tagline))
                        <p class="mt-1 text-sm text-stone-500">{{ $settings->site_tagline }}</p>
                    @endif
                    @if (filled($settings->site_description))
                        <p class="mt-3 max-w-md text-sm leading-7 text-stone-600">{{ $settings->site_description }}</p>
                    @endif
                    <div class="mt-4 space-y-1 text-sm text-stone-500">
                        @if (filled($settings->site_email))
                            <p><a href="mailto:{{ $settings->site_email }}" class="hover:text-stone-950">{{ $settings->site_email }}</a></p>
                        @endif
                        @if (filled($settings->site_phone))
                            <p>{{ $settings->site_phone }}</p>
                        @endif
                        @if (filled($settings->site_address))
                            <p>{{ $settings->site_address }}</p>
                        @endif
                    </div>
                </div>

                @if ($footerMenuItems->isNotEmpty())
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-stone-400">Explore</p>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            @include('layouts.partials.site-footer-menu-items', ['items' => $footerMenuItems])
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-10 border-t border-stone-200 pt-6 text-sm text-stone-400">
                &copy; {{ now()->year }} {{ $settings->site_name ?? config('app.name') }}. All rights reserved.
            </div>
        </div>
    </footer>

@stack('scripts')
</body>
</html>
