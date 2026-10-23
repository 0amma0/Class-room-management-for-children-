<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? "Children's Classes — Management" }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-cream text-ink antialiased">
    @php
        $user = auth()->user();
        $navigationItems = $user->role === 'admin'
            ? [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'dashboard'],
                ['label' => 'Regions', 'route' => 'admin.regions', 'icon' => 'regions'],
                ['label' => 'Cluster', 'route' => 'admin.clusters', 'icon' => 'clusters'],
                ['label' => 'Communities', 'route' => 'admin.environments', 'icon' => 'communities'],
                ['label' => 'Classes', 'route' => 'admin.classes', 'icon' => 'classes'],
                ['label' => 'Kelas', 'route' => 'admin.class-progress', 'icon' => 'classes'],
                ['label' => 'Participants', 'route' => 'admin.students', 'icon' => 'participants'],
                ['label' => 'Teacher Accounts', 'route' => 'admin.teachers', 'icon' => 'teachers'],
                ['label' => 'Draft Lagu & Materi', 'route' => 'admin.materials', 'icon' => 'materials', 'badge' => $pendingDownloadRequestCount],
                ['label' => 'Inbox Bantuan', 'route' => 'admin.support', 'icon' => 'notifications', 'badge' => $pendingSupportConversationCount],
                ['label' => 'Notifikasi', 'route' => 'notifications.index', 'icon' => 'notifications', 'badge' => $unreadNotificationCount],
                ['label' => 'Public Activity Cards', 'route' => 'admin.activity-cards', 'icon' => 'activities'],
                ['label' => 'Activity Review', 'route' => 'admin.activities', 'icon' => 'activities'],
                ['label' => 'Database Backups', 'route' => 'admin.backups', 'icon' => 'backups'],
                ['label' => 'Pengaturan Kontak', 'route' => 'admin.contacts', 'icon' => 'profile'],
                ['label' => 'Panduan Admin', 'route' => 'admin.user-guide', 'icon' => 'materials'],
                ['label' => 'Profile', 'route' => 'profile', 'icon' => 'profile'],
            ]
            : [
                ['label' => 'Dashboard', 'route' => 'guru.dashboard', 'icon' => 'dashboard'],
                ['label' => 'Classes', 'route' => 'guru.classes', 'icon' => 'classes'],
                ['label' => 'Lesson Checklist', 'route' => 'guru.class-progress', 'icon' => 'classes'],
                ['label' => 'Participants', 'route' => 'guru.students', 'icon' => 'participants'],
                ['label' => 'Ekspor data kelas', 'route' => 'guru.students.export', 'icon' => 'backups'],
                ['label' => 'Attendance', 'route' => 'guru.attendances', 'icon' => 'attendance'],
                ['label' => 'Development', 'route' => 'guru.developments', 'icon' => 'development'],
                ['label' => 'Activities', 'route' => 'guru.activities', 'icon' => 'activities'],
                ['label' => 'Galeri kelas', 'route' => 'guru.gallery', 'icon' => 'activities'],
                ['label' => 'Lesson Plan', 'route' => 'guru.lesson-plans', 'icon' => 'classes'],
                ['label' => 'Cerita & Ilustrasi', 'route' => 'guru.stories', 'icon' => 'activities'],
                ['label' => 'Parent Visits', 'route' => 'guru.parent-visits', 'icon' => 'teachers'],
                ['label' => 'Refleksi Guru', 'route' => 'guru.teacher-module', 'icon' => 'classes'],
                ['label' => 'Draft Lagu & Materi', 'route' => 'guru.materials', 'icon' => 'materials'],
                ['label' => 'Bantuan Admin', 'route' => 'guru.support', 'icon' => 'notifications'],
                ['label' => 'Notifikasi', 'route' => 'notifications.index', 'icon' => 'notifications', 'badge' => $unreadNotificationCount],
                ['label' => 'Panduan Guru', 'route' => 'guru.user-guide', 'icon' => 'materials'],
                ['label' => 'Profile', 'route' => 'profile', 'icon' => 'profile'],
            ];
    @endphp

    <div class="min-h-screen lg:flex">
        <aside class="hidden w-[248px] shrink-0 flex-col bg-navy px-4 py-5 text-white lg:sticky lg:top-0 lg:flex lg:h-screen">
            <a href="{{ $user->role === 'admin' ? route('admin.dashboard') : route('guru.dashboard') }}" class="mb-7 flex items-center gap-3 px-2">
                <span class="flex size-10 items-center justify-center rounded-xl bg-orange font-display text-xl font-bold text-white shadow-sm">K</span>
                <span class="font-display text-sm font-semibold leading-tight">Children's Classes</span>
            </a>

            <div class="mb-4 px-2">
                <span class="inline-flex rounded-full bg-sun px-3 py-1 text-xs font-bold text-navy">
                    {{ $user->role === 'admin' ? 'Admin' : 'Teachers' }}
                </span>
            </div>

            <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.16em] text-white/45">Navigation</p>
            <nav class="scrollbar-hidden flex min-h-0 flex-1 flex-col gap-1 overflow-y-auto overscroll-contain" aria-label="Main navigation">
                @include('layouts.partials.navigation-links', ['navigationItems' => $navigationItems])
            </nav>

            <div class="mt-6 shrink-0 border-t border-white/15 pt-5">
                <div class="flex items-center gap-3 px-2">
                    @if($user->avatar_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($user->avatar_path) }}" alt="{{ $user->name }}" class="size-10 rounded-full border-2 border-white/30 object-cover">
                    @else
                        <x-ui-avatar :type="$user->avatar_type ?? 'bangs'" size="md" class="border-2 border-white/30" :alt="$user->name" />
                    @endif
                    <div class="min-w-0">
                        <p class="truncate text-sm font-bold text-white">{{ $user->name }}</p>
                        <p class="text-xs text-white/65">{{ $user->role === 'admin' ? 'Administrator' : 'Teacher' }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-4">
                    @csrf
                    <button type="submit" class="w-full rounded-xl px-3 py-2 text-left text-sm font-semibold text-white/70 transition-colors hover:bg-white/10 hover:text-white">
                        Sign Out
                    </button>
                </form>
            </div>
        </aside>

        <div class="min-w-0 flex-1">
            <header class="sticky top-0 z-40 border-b border-line bg-cream/95 px-4 py-3 backdrop-blur sm:px-6 lg:hidden">
                <div class="flex items-center justify-between gap-4">
                    <a href="{{ $user->role === 'admin' ? route('admin.dashboard') : route('guru.dashboard') }}" class="flex min-w-0 items-center gap-3">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-orange font-display text-lg font-bold text-white">K</span>
                        <span class="truncate font-display text-sm font-semibold text-navy">Children's Classes</span>
                    </a>
                    <details class="relative shrink-0">
                        <summary class="flex size-10 cursor-pointer list-none items-center justify-center rounded-xl border border-line bg-white text-navy shadow-sm" aria-label="Open navigation">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="size-5">
                                <path d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </summary>
                        <div class="scrollbar-hidden absolute right-0 top-12 z-50 max-h-[min(70vh,36rem)] w-[min(20rem,calc(100vw-2rem))] overflow-y-auto overscroll-contain rounded-2xl border border-line bg-navy p-4 text-white shadow-xl">
                            <div class="mb-3 flex items-center gap-3 border-b border-white/15 px-1 pb-3">
                                @if($user->avatar_path)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($user->avatar_path) }}" alt="{{ $user->name }}" class="size-8 rounded-full border border-white/30 object-cover">
                                @else
                                    <x-ui-avatar :type="$user->avatar_type ?? 'bangs'" size="sm" class="border border-white/30" :alt="$user->name" />
                                @endif
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold">{{ $user->name }}</p>
                                    <p class="text-xs text-white/65">{{ $user->role === 'admin' ? 'Admin' : 'Teacher' }}</p>
                                </div>
                            </div>
                            <nav class="flex flex-col gap-1" aria-label="Mobile navigation">
                                @include('layouts.partials.navigation-links', ['navigationItems' => $navigationItems])
                            </nav>
                            <form method="POST" action="{{ route('logout') }}" class="mt-3 border-t border-white/15 pt-3">
                                @csrf
                                <button type="submit" class="w-full rounded-xl px-3 py-2 text-left text-sm font-semibold text-white/75 hover:bg-white/10 hover:text-white">
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </details>
                </div>
            </header>

            <main class="min-h-screen px-4 py-5 sm:px-6 sm:py-7 lg:px-8 lg:py-8">
                {{ $slot }}
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
