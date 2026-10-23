@props([
    'variant' => 'ok',
    'title' => null,
])

@php
$variantClasses = match($variant) {
    'ok' => 'bg-[#2E9E5B]/10 border-[#2E9E5B] text-[#2E9E5B]',
    'danger' => 'bg-[#D64545]/10 border-[#D64545] text-[#D64545]',
    'warning', 'orange' => 'bg-[#FBA93A]/10 border-[#FBA93A] text-[#d88214]',
    'info', 'royal' => 'bg-[#2E4BC0]/10 border-[#2E4BC0] text-[#2E4BC0]',
    default => 'bg-[#2E9E5B]/10 border-[#2E9E5B] text-[#2E9E5B]',
};
@endphp

<div {{ $attributes->merge(['class' => "rounded-2xl border-2 p-4 text-sm font-sans flex items-start gap-3 {$variantClasses}"]) }}>
    <div class="flex-1">
        @if($title)
            <div class="font-display font-bold text-base mb-1">{{ $title }}</div>
        @endif
        <div>{{ $slot }}</div>
    </div>
</div>
