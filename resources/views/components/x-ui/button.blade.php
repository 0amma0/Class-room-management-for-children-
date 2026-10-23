@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
    'icon' => null,
])

@php
$baseClasses = 'inline-flex items-center justify-center gap-2 font-display rounded-full transition-all duration-200 focus:outline-none cursor-pointer';

$sizeClasses = match($size) {
    'sm' => 'px-4 py-1.5 text-sm',
    'lg' => 'px-8 py-3.5 text-lg font-bold shadow-md hover:scale-105 active:scale-95',
    default => 'px-6 py-2.5 text-base font-semibold shadow-sm hover:scale-102 active:scale-98',
};

$variantClasses = match($variant) {
    'primary' => 'bg-[#FBA93A] hover:bg-[#e89726] text-[#1D2B33] border-2 border-[#1D2B33]/10 shadow-[0_4px_0_#d88214]',
    'secondary' => 'bg-[#1F2F8A] hover:bg-[#162266] text-white shadow-[0_4px_0_#0f1747]',
    'royal' => 'bg-[#2E4BC0] hover:bg-[#233ca0] text-white shadow-[0_4px_0_#1b2d78]',
    'sun' => 'bg-[#FFD86B] hover:bg-[#f0c54f] text-[#1D2B33] border-2 border-[#1D2B33]/10',
    'outline' => 'bg-white hover:bg-[#FFF8EA] text-[#1F2F8A] border-2 border-[#1F2F8A]',
    'danger' => 'bg-[#D64545] hover:bg-[#b53434] text-white shadow-[0_4px_0_#8c2222]',
    default => 'bg-[#FBA93A] hover:bg-[#e89726] text-[#1D2B33]',
};

$classes = "{$baseClasses} {$sizeClasses} {$variantClasses}";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
