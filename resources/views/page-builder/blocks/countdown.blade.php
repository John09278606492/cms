@php
    $until = ! empty($until) ? \Illuminate\Support\Carbon::parse($until)->toIso8601String() : '';
    $units = ['days' => 'Days', 'hours' => 'Hours', 'minutes' => 'Minutes', 'seconds' => 'Seconds'];
@endphp
<section class="text-center">
    @if (! empty($heading))
        <h2 class="mb-6 text-3xl font-semibold tracking-tight text-stone-950">{{ $heading }}</h2>
    @endif
    <div class="pb-countdown flex flex-wrap justify-center gap-4" data-until="{{ $until }}" data-expired="{{ $expired_text ?? '' }}">
        @foreach ($units as $key => $label)
            <div class="min-w-[5rem] rounded-2xl border border-stone-200 bg-white px-5 py-4">
                <div class="text-3xl font-bold tabular-nums text-stone-950 sm:text-4xl" data-unit="{{ $key }}">00</div>
                <div class="mt-1 text-xs font-medium uppercase tracking-wide text-stone-500">{{ $label }}</div>
            </div>
        @endforeach
    </div>
</section>
