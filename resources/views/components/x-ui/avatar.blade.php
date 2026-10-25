@props([
    'type' => 'bangs',
    'size' => 'md',
    'alt' => '',
    'image' => null,
])

@php
$sizeClasses = match($size) {
    'xs' => 'size-6',
    'sm' => 'size-8',
    'lg' => 'size-14',
    'xl' => 'size-20',
    default => 'size-10',
};

$avatarImages = [
    'bangs' => 'Bangs-01.png',
    'pigtails' => 'Pigtails-01.png',
    'afro' => 'Afro-01.png',
    'short' => 'Short-01.png',
    'bun' => 'Bun-01.png',
    'braids' => 'Braids-01.png',
    'short-blue' => 'Short-02.png',
    'beret' => 'Short-02.png',
    'hat' => 'Short-02.png',
];

$avatarImage = $avatarImages[$type] ?? $avatarImages['bangs'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex shrink-0 overflow-hidden rounded-full {$sizeClasses}"]) }}>
    <img
        src="{{ $image ? \Illuminate\Support\Facades\Storage::disk('public')->url($image) : asset('images/Avatar/'.$avatarImage) }}"
        alt="{{ $alt }}"
        class="h-full w-full object-cover"
        loading="lazy"
    >
</span>
