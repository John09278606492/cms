@if (filled($banner ?? null))
    <div class="{{ $banner['wrapper_classes'] }}">
        <div class="overflow-hidden rounded-[2rem] border border-amber-200 bg-gradient-to-r from-amber-50 via-white to-stone-50 shadow-[0_30px_80px_-50px_rgba(217,119,6,0.75)]">
            <div class="flex flex-col gap-5 p-5 sm:p-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-100/80 text-amber-700 ring-1 ring-amber-200">
                        <svg aria-hidden="true" viewBox="0 0 24 24" class="h-7 w-7 fill-none stroke-current">
                            <path d="M9 7v10M15 7v10" stroke-width="1.8" stroke-linecap="round" />
                        </svg>
                    </div>

                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-amber-800">
                                {{ $banner['eyebrow'] }}
                            </span>
                            <span class="rounded-full border border-amber-200 bg-white px-3 py-1 text-xs font-semibold text-amber-700">
                                {{ $banner['badge'] }}
                            </span>
                        </div>

                        <h2 class="text-2xl font-semibold tracking-tight text-stone-950">
                            {{ $banner['title'] }}
                        </h2>

                        <p class="max-w-2xl text-sm leading-7 text-stone-600">
                            {{ $banner['description'] }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    @if (filled($banner['primary_url'] ?? null))
                        <a href="{{ $banner['primary_url'] }}" class="inline-flex items-center justify-center rounded-full bg-stone-950 px-5 py-3 text-sm font-medium text-white transition hover:bg-stone-800">
                            {{ $banner['primary_label'] }}
                        </a>
                    @endif

                    @if (filled($banner['secondary_url'] ?? null))
                        <a href="{{ $banner['secondary_url'] }}" class="inline-flex items-center justify-center rounded-full border border-stone-300 bg-white px-5 py-3 text-sm font-medium text-stone-700 transition hover:border-stone-950 hover:text-stone-950">
                            {{ $banner['secondary_label'] }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
