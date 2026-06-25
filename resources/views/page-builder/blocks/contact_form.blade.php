@php
    $site = request()->route('site');
    $action = $site instanceof \App\Models\Site ? route('sites.contact', $site) : null;
    $showSubject = $show_subject ?? true;
    $showPhone = $show_phone ?? false;
    $buttonLabel = $button_label ?? 'Send message';
    $sent = $action && session('contact_form_success');
    $page = ($__page ?? null);

    // Only the public site has a resolved {site} route, so only there do we
    // render a real, submittable <form>. Inside the Filament page editor this
    // partial is shown as a *block preview*; a nested <form> with `required`
    // inputs would be hoisted into the editor's own form by the browser and
    // block its Save button — so there we render an inert visual mock instead.
    $interactive = $action !== null;
@endphp
<section class="mx-auto max-w-2xl rounded-3xl border border-stone-200 bg-white p-8">
    @if (! empty($heading))
        <h2 class="text-2xl font-semibold tracking-tight text-stone-950">{{ $heading }}</h2>
    @endif
    @if (! empty($intro))
        <p class="mt-2 leading-7 text-stone-600">{{ $intro }}</p>
    @endif

    @if ($sent)
        <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
            {{ session('contact_form_success') }}
        </div>
    @else
        @if ($interactive && isset($errors) && $errors->any())
            <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                Please check the form and try again.
            </div>
        @endif
        @if ($interactive)
            @php
                // Delivery settings travel in an encrypted, tamper-proof field so the
                // recipient address is never exposed in the page source and visitors
                // can never redirect submissions to an arbitrary inbox.
                $config = encrypt([
                    'to' => $to_email ?? null,
                    'send' => $send_email ?? true,
                    'redirect' => $redirect_url ?? null,
                    'success' => $success_message ?? null,
                    'page_id' => $page instanceof \App\Models\Page ? $page->getKey() : null,
                ]);
            @endphp
            <form method="POST" action="{{ $action }}" class="mt-6 space-y-4">
                @csrf
                <input type="hidden" name="config" value="{{ $config }}">
                {{-- Honeypot: bots fill this, humans never see it. --}}
                <div class="hidden" aria-hidden="true">
                    <label>Leave this empty <input type="text" name="company" tabindex="-1" autocomplete="off"></label>
                </div>
        @else
            <div class="mt-6 space-y-4">
        @endif
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-stone-700" for="cf-name">Name</label>
                        <input id="cf-name" type="text" value="{{ old('name') }}" @if ($interactive) name="name" required @endif
                               class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-stone-900 focus:border-stone-950 focus:ring-stone-950">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-stone-700" for="cf-email">Email</label>
                        <input id="cf-email" type="email" value="{{ old('email') }}" @if ($interactive) name="email" required @endif
                               class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-stone-900 focus:border-stone-950 focus:ring-stone-950">
                    </div>
                </div>
                @if ($showPhone)
                    <div>
                        <label class="mb-1 block text-sm font-medium text-stone-700" for="cf-phone">Phone</label>
                        <input id="cf-phone" type="tel" value="{{ old('phone') }}" @if ($interactive) name="phone" @endif
                               class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-stone-900 focus:border-stone-950 focus:ring-stone-950">
                    </div>
                @endif
                @if ($showSubject)
                    <div>
                        <label class="mb-1 block text-sm font-medium text-stone-700" for="cf-subject">Subject</label>
                        <input id="cf-subject" type="text" value="{{ old('subject') }}" @if ($interactive) name="subject" @endif
                               class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-stone-900 focus:border-stone-950 focus:ring-stone-950">
                    </div>
                @endif
                <div>
                    <label class="mb-1 block text-sm font-medium text-stone-700" for="cf-message">Message</label>
                    <textarea id="cf-message" rows="5" @if ($interactive) name="message" required @endif
                              class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-stone-900 focus:border-stone-950 focus:ring-stone-950">{{ old('message') }}</textarea>
                </div>
        @if ($interactive)
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-full bg-stone-950 px-6 py-3 text-sm font-semibold text-white transition hover:bg-stone-800">
                    {{ $buttonLabel }}
                </button>
            </form>
        @else
                <button type="button" disabled
                        class="inline-flex items-center justify-center rounded-full bg-stone-950 px-6 py-3 text-sm font-semibold text-white opacity-60">
                    {{ $buttonLabel }}
                </button>
            </div>
        @endif
    @endif
</section>
