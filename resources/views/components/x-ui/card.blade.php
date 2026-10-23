@props([
    'title' => null,
    'subtitle' => null,
    'padding' => 'p-6',
    'badge' => null,
])

<div {{ $attributes->merge(['class' => "bg-white rounded-[20px] border-2 border-[#EFE4CC] shadow-sm {$padding} transition-all duration-200"]) }}>
    @if($title || $subtitle || $badge)
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                @if($title)
                    <h3 class="font-display font-bold text-lg text-[#1D2B33]">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="text-sm text-[#65737D] mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            @if($badge)
                <div>
                    {{ $badge }}
                </div>
            @endif
        </div>
    @endif

    <div>
        {{ $slot }}
    </div>
</div>
