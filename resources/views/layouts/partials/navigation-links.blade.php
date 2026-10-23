@foreach($navigationItems as $item)
    @php
        $isActive = request()->routeIs($item['route']);
    @endphp

    <a
        href="{{ route($item['route']) }}"
        @if($isActive) aria-current="page" @endif
        class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition-colors {{ $isActive ? 'bg-orange text-ink shadow-sm' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
    >
        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0">
            @switch($item['icon'])
                @case('dashboard')
                    <rect x="3" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="14" width="7" height="7" rx="1"></rect><rect x="3" y="14" width="7" height="7" rx="1"></rect>
                    @break
                @case('regions')
                    <path d="m3 6 6-3 6 3 6-3v15l-6 3-6-3-6 3z"></path><path d="M9 3v15M15 6v15"></path>
                    @break
                @case('clusters')
                    <circle cx="6" cy="6" r="2.5"></circle><circle cx="18" cy="6" r="2.5"></circle><circle cx="12" cy="18" r="2.5"></circle><path d="m8 7.5 2.5 7M16 7.5l-2.5 7M9 6h6"></path>
                    @break
                @case('communities')
                    <path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"></path>
                    @break
                @case('classes')
                    <path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v17H6.5A2.5 2.5 0 0 0 4 21z"></path><path d="M4 4.5V21M8 6h8M8 10h8"></path>
                    @break
                @case('teachers')
                    <circle cx="9" cy="8" r="3"></circle><path d="M3 20v-1a6 6 0 0 1 12 0v1zM16 5.5a3 3 0 0 1 0 5.8M18 14a5 5 0 0 1 3 4.5V20h-3"></path>
                    @break
                @case('participants')
                    <circle cx="9" cy="8" r="3"></circle><path d="M3 20v-1a6 6 0 0 1 12 0v1zM16 8h5M18.5 5.5v5"></path>
                    @break
                @case('attendance')
                    <rect x="4" y="4" width="16" height="17" rx="2"></rect><path d="M8 2v4M16 2v4M4 9h16M8 13h2M14 13h2M8 17h2"></path>
                    @break
                @case('materials')
                    <path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v17H6.5A2.5 2.5 0 0 0 4 21z"></path><path d="M4 4.5V21M8 6h8M8 10h8M8 14h6"></path>
                    @break
                @case('notifications')
                    <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path><path d="M10 21h4"></path>
                    @break
                @case('development')
                    <path d="m3 17 6-6 4 4 8-9"></path><path d="M15 6h6v6"></path>
                    @break
                @case('activities')
                    <path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9z"></path>
                    @break
                @case('backups')
                    <ellipse cx="12" cy="5" rx="8" ry="3"></ellipse><path d="M4 5v14c0 1.7 3.6 3 8 3s8-1.3 8-3V5M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"></path>
                    @break
                @case('profile')
                    <circle cx="12" cy="8" r="4"></circle><path d="M4 21a8 8 0 0 1 16 0"></path>
                    @break
            @endswitch
        </svg>
        <span class="flex-1">{{ $item['label'] }}</span>
        @if(($item['badge'] ?? 0) > 0)
            <span class="inline-flex min-w-5 items-center justify-center rounded-full bg-orange px-1.5 py-0.5 text-[10px] font-bold text-white" aria-label="{{ $item['badge'] }} pending">
                {{ $item['badge'] }}
            </span>
        @endif
    </a>
@endforeach
