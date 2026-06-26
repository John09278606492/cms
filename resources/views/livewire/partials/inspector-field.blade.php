@php
    $wire = $path . '.' . $field['key'];
    $type = $field['type'];
    $inputClass = 'w-full rounded-lg border border-stone-300 px-3 py-1.5 text-sm text-stone-900 focus:border-stone-900 focus:ring-stone-900';
@endphp

@if ($type === 'toggle')
    <label class="flex items-center justify-between gap-3 py-1">
        <span class="text-sm text-stone-700">{{ $field['label'] }}</span>
        <input type="checkbox" wire:model.live="{{ $wire }}" class="h-5 w-9 cursor-pointer rounded-full">
    </label>
@else
    <div>
        <label class="mb-1 block text-xs font-medium text-stone-600">{{ $field['label'] }}</label>

        @switch($type)
            @case('textarea')
            @case('richtext')
                <textarea wire:model.live.debounce.500ms="{{ $wire }}" rows="3" class="{{ $inputClass }}"></textarea>
                @break

            @case('select')
                <select wire:model.live="{{ $wire }}" class="{{ $inputClass }}">
                    @foreach ($field['options'] as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @break

            @case('number')
                <input type="number" wire:model.live.debounce.500ms="{{ $wire }}" class="{{ $inputClass }}">
                @break

            @case('datetime')
                <input type="datetime-local" wire:model.live="{{ $wire }}" class="{{ $inputClass }}">
                @break

            @case('color')
                <div class="flex items-center gap-2">
                    <input type="color" wire:model.live="{{ $wire }}" class="h-8 w-10 shrink-0 cursor-pointer rounded border border-stone-300">
                    <input type="text" wire:model.live.debounce.500ms="{{ $wire }}" placeholder="#hex or empty" class="{{ $inputClass }}">
                </div>
                @break

            @case('image')
                @php
                    $imgVal = $data[$field['key']] ?? null;
                    $multiple = $field['multiple'] ?? false;
                    $current = $multiple ? (is_array($imgVal) ? $imgVal : []) : (filled($imgVal) ? [$imgVal] : []);
                @endphp
                <div wire:key="img-{{ $field['key'] }}">
                    @if (! empty($current))
                        <div class="mb-2 flex flex-wrap gap-2">
                            @foreach ($current as $ci => $imgPath)
                                <div class="relative">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($imgPath) }}" alt="" class="h-14 w-14 rounded-lg border border-stone-200 object-cover">
                                    @if ($multiple)
                                        <button type="button" wire:click="removeImageAt('{{ $field['key'] }}', {{ $ci }})" class="absolute -right-1.5 -top-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-stone-900 text-xs text-white hover:bg-red-600">&times;</button>
                                    @else
                                        <button type="button" wire:click="clearImage('{{ $field['key'] }}')" class="absolute -right-1.5 -top-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-stone-900 text-xs text-white hover:bg-red-600">&times;</button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <label class="flex cursor-pointer items-center justify-center gap-1.5 rounded-lg border border-dashed border-stone-300 px-3 py-2 text-xs font-medium text-stone-600 hover:border-stone-400 hover:bg-stone-50">
                        <span wire:loading.remove wire:target="pendingUploads.{{ $field['key'] }}">Upload image{{ $multiple ? 's' : '' }}</span>
                        <span wire:loading wire:target="pendingUploads.{{ $field['key'] }}">Uploading…</span>
                        <input type="file" accept="image/*" @if ($multiple) multiple @endif wire:model="pendingUploads.{{ $field['key'] }}" class="hidden">
                    </label>
                </div>
                @break

            @case('repeater')
                @php $items = is_array($data[$field['key']] ?? null) ? $data[$field['key']] : []; @endphp
                <div class="space-y-2">
                    @foreach ($items as $ri => $item)
                        <div wire:key="rep-{{ $field['key'] }}-{{ $ri }}" class="rounded-lg border border-stone-200 bg-stone-50 p-2.5">
                            <div class="mb-1.5 flex items-center justify-between">
                                <span class="text-[11px] font-semibold uppercase tracking-wide text-stone-400">#{{ $ri + 1 }}</span>
                                <button type="button" wire:click="removeItem('{{ $field['key'] }}', {{ $ri }})" class="text-xs text-red-500 hover:text-red-700">Remove</button>
                            </div>
                            <div class="space-y-2">
                                @foreach ($field['fields'] as $sub)
                                    @include('livewire.partials.inspector-field', ['field' => $sub, 'path' => $path . '.' . $field['key'] . '.' . $ri, 'data' => is_array($item) ? $item : []])
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                    <button type="button" wire:click="addItem('{{ $field['key'] }}')" class="w-full rounded-lg border border-dashed border-stone-300 py-1.5 text-xs font-medium text-stone-600 hover:border-stone-400 hover:bg-stone-50">+ Add item</button>
                </div>
                @break

            @default
                <input type="text" wire:model.live.debounce.500ms="{{ $wire }}" class="{{ $inputClass }}">
        @endswitch
    </div>
@endif
