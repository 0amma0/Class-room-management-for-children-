@props([
    'type' => 'text',
    'name' => null,
    'id' => null,
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
])

<input
    type="{{ $type }}"
    @if($name) name="{{ $name }}" @endif
    @if($id) id="{{ $id }}" @endif
    @if($value !== null) value="{{ $value }}" @endif
    @if($placeholder) placeholder="{{ $placeholder }}" @endif
    @if($required) required @endif
    @if($disabled) disabled @endif
    {{ $attributes->merge(['class' => 'w-full rounded-xl border-2 border-[#EFE4CC] bg-white px-4 py-2.5 text-[#1D2B33] placeholder-[#65737D]/60 focus:border-[#FBA93A] focus:outline-none focus:ring-2 focus:ring-[#FBA93A]/20 transition-all font-sans text-sm disabled:bg-gray-100 disabled:cursor-not-allowed']) }}
>
