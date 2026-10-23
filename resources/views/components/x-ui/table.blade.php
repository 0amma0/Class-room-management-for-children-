@props([
    'headers' => [],
])

<div class="overflow-x-auto">
    <table {{ $attributes->merge(['class' => 'w-full text-left border-separate border-spacing-y-2']) }}>
        @if(count($headers) > 0)
            <thead>
                <tr class="text-xs font-display uppercase tracking-wider text-[#65737D]">
                    @foreach($headers as $header)
                        <th class="px-4 py-2 font-bold">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody class="text-sm font-sans divide-y-0">
            {{ $slot }}
        </tbody>
    </table>
</div>
