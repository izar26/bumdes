@props([
    'label',
    'name',
    'type' => 'text',
    'icon' => null,
    'value' => '',
    'required' => false,
    'placeholder' => '',
])

<div class="mb-4">
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">
        @if($icon)
            <i class="fas {{ $icon }} mr-2 text-green-600"></i>
        @endif
        {{ $label }}
        @if($required)
            <span class="text-red-500">*</span>
        @endif
    </label>

    <div class="relative rounded-md shadow-sm">
        @if($icon)
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <i class="fas {{ $icon }} text-gray-400"></i>
            </div>
        @endif
        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $name }}"
            value="{{ old($name, $value) }}"
            {{ $required ? 'required' : '' }}
            placeholder="{{ $placeholder }}"
            {{ $attributes->merge(['class' => 'block w-full rounded-md border-gray-300 ' . ($icon ? 'pl-10' : 'pl-3') . ' focus:border-green-500 focus:ring-green-500 sm:text-sm py-2']) }}
        >
    </div>

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
