@props([
    'variant' => 'sun',
    'size' => 'md',
])

@php
$baseClasses = 'inline-flex items-center justify-center font-display font-semibold rounded-full';

$sizeClasses = match($size) {
    'sm' => 'px-2.5 py-0.5 text-xs',
    'lg' => 'px-4 py-1.5 text-sm',
    default => 'px-3 py-1 text-xs',
};

$variantClasses = match($variant) {
    'sun' => 'bg-[#FFD86B] text-[#1D2B33]',
    'orange' => 'bg-[#FBA93A] text-[#1D2B33]',
    'ok' => 'bg-[#2E9E5B]/15 text-[#2E9E5B] border border-[#2E9E5B]/30',
    'danger' => 'bg-[#D64545]/15 text-[#D64545] border border-[#D64545]/30',
    'navy' => 'bg-[#1F2F8A] text-white',
    'sky' => 'bg-[#8ED6EC] text-[#1D2B33]',
    'muted' => 'bg-[#EFE4CC] text-[#65737D]',
    default => 'bg-[#FFD86B] text-[#1D2B33]',
};

$classes = "{$baseClasses} {$sizeClasses} {$variantClasses}";
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
