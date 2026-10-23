@props([
    'for' => null,
    'required' => false,
])

<label @if($for) for="{{ $for }}" @endif {{ $attributes->merge(['class' => 'block font-display font-semibold text-sm text-[#1D2B33] mb-1.5']) }}>
    {{ $slot }}
    @if($required)
        <span class="text-[#D64545]">*</span>
    @endif
</label>
