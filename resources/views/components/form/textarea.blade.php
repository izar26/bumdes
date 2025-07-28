@props([
    'label',
    'name',
    'icon' => null,
    'rows' => 4,
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
        <textarea
            name="{{ $name }}"
            id="{{ $name }}"
            rows="{{ $rows }}"
            {{ $required ? 'required' : '' }}
            placeholder="{{ $placeholder }}"
            {{ $attributes->merge(['class' => 'block w-full rounded-md border-gray-300 pl-10 focus:border-green-500 focus:ring-green-500 sm:text-sm py-3']) }}
        >{{ old($name, $value) }}</textarea>
    </div>

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
