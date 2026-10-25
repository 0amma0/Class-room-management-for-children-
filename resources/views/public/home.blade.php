<x-guest-layout title="Teaching Children's Classes — West Java Programme">
    <!-- 1. TOP NAVBAR matching 01-beranda-publik_part1.png -->
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-[#EFE4CC]/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 sm:h-20 flex items-center justify-between gap-3">
            <!-- Brand / Logo -->
            <a href="#home" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-full bg-[#FBA93A] text-white flex items-center justify-center font-display font-bold text-xl shadow-sm group-hover:scale-105 transition-transform">
                    K
                </div>
                <div>
                    <span class="font-display font-bold text-base sm:text-xl text-[#1F2F8A] tracking-tight">
                        <span class="sm:hidden">Children's Classes</span>
                        <span class="hidden sm:inline">Teaching Children's Classes</span>
                    </span>
                </div>
            </a>

            <!-- Anchor Navigation matching mockup -->
            <nav class="hidden md:flex items-center gap-8 font-sans font-semibold text-sm text-[#1D2B33]">
                <a href="#home" class="hover:text-[#2E4BC0] transition-colors">Home</a>
                <a href="#about" class="hover:text-[#2E4BC0] transition-colors">About</a>
                <a href="{{ route('regions') }}" class="hover:text-[#2E4BC0] transition-colors">Regions</a>
                <a href="{{ route('learning-materials.index') }}" class="hover:text-[#2E4BC0] transition-colors">Materi</a>
                    <a href="{{ route('classes') }}" class="hover:text-[#2E4BC0] transition-colors">Classes</a>
                <a href="#activities" class="hover:text-[#2E4BC0] transition-colors">Activities</a>
            </nav>

            <details class="relative md:hidden">
                <summary class="flex size-10 cursor-pointer list-none items-center justify-center rounded-xl border border-[#EFE4CC] bg-white text-[#1F2F8A]" aria-label="Open navigation">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="size-5">
                        <path d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </summary>
                <nav class="absolute right-0 top-12 z-50 flex w-48 flex-col gap-1 rounded-2xl border border-[#EFE4CC] bg-white p-3 text-sm font-semibold text-[#1D2B33] shadow-xl">
                    <a href="#home" class="rounded-lg px-3 py-2 hover:bg-[#FFF8EA]">Home</a>
                    <a href="#about" class="rounded-lg px-3 py-2 hover:bg-[#FFF8EA]">About</a>
                    <a href="{{ route('regions') }}" class="rounded-lg px-3 py-2 hover:bg-[#FFF8EA]">Regions</a>
                    <a href="{{ route('learning-materials.index') }}" class="rounded-lg px-3 py-2 hover:bg-[#FFF8EA]">Materi</a>
                    <a href="{{ route('classes') }}" class="rounded-lg px-3 py-2 hover:bg-[#FFF8EA]">Classes</a>
                    <a href="#activities" class="rounded-lg px-3 py-2 hover:bg-[#FFF8EA]">Activities</a>
                </nav>
            </details>

            <!-- Auth Action Button -->
            <div>
                @auth
                    @php
                        $dashboardRoute = auth()->user()->role === 'admin' ? route('admin.dashboard') : route('guru.dashboard');
                    @endphp
                    <a href="{{ $dashboardRoute }}"
                       class="px-6 py-2.5 rounded-full font-display font-bold text-sm bg-[#FBA93A] hover:bg-[#e89726] text-white shadow-sm transition-all hover:scale-102">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="px-7 py-2.5 rounded-full font-display font-bold text-sm bg-[#FBA93A] hover:bg-[#e89726] text-white shadow-sm transition-all hover:scale-102">
                        Sign In
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- 2. HERO SECTION matching 01-beranda-publik_part1.png -->
    <section id="home" class="relative bg-[#8ED6EC] pt-14 pb-28 px-6 overflow-hidden">
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-8 items-center relative z-10">
            <div class="home-hero-copy lg:col-span-7 space-y-6 text-center lg:text-left">
                <!-- Tag pill -->
                <div class="inline-block">
                    <span class="inline-block px-4 py-1.5 rounded-full text-xs font-display font-bold bg-[#FFF2D4] text-[#D97706] shadow-xs">
                        West Java · Children's Class Programme
                    </span>
                </div>

                <!-- Main Title -->
                <h1 class="font-display font-bold text-5xl sm:text-6xl text-[#1F2F8A] leading-tight">
                    Learn, Play,<br>
                    <span class="text-[#FBA93A]">Grow Together</span>
                </h1>

                <!-- Subtitle -->
                <p class="font-sans text-base sm:text-lg text-[#1D2B33]/85 max-w-xl leading-relaxed">
                    Community-based children's class management, from West Java to the classroom.
                </p>

                <!-- 2 CTA Buttons -->
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-4 pt-3">
                    <a href="{{ route('regions') }}"
                       class="px-8 py-3.5 rounded-full font-display font-bold text-base bg-[#FBA93A] hover:bg-[#e89726] text-white shadow-md shadow-[#FBA93A]/30 transition-all hover:scale-102">
                        Explore Regions
                    </a>
                    <a href="#about"
                       class="px-8 py-3.5 rounded-full font-display font-semibold text-base bg-[#8ED6EC] hover:bg-[#7dcfe8] text-[#1F2F8A] border-2 border-[#1F2F8A] transition-all">
                        About us
                    </a>
                </div>
            </div>

            <!-- Approved illustration asset from public/images -->
            <div class="home-hero-art lg:col-span-5 flex justify-center">
                <img
                    src="{{ asset('images/imganak.png') }}"
                    alt="Children holding hands and playing together"
                    class="home-hero-image w-full max-w-lg drop-shadow-xl"
                    fetchpriority="high"
                >
            </div>
        </div>

        <!-- Scalloped Repeating Cloud Edge Divider matching mockup -->
        <div class="absolute bottom-0 left-0 right-0 w-full overflow-hidden leading-none z-0">
            <svg class="home-cloud-divider relative block h-14 text-white" viewBox="0 0 1200 60" preserveAspectRatio="none">
                <path d="M0,60 C40,10 80,10 120,60 C160,10 200,10 240,60 C280,10 320,10 360,60 C400,10 440,10 480,60 C520,10 560,10 600,60 C640,10 680,10 720,60 C760,10 800,10 840,60 C880,10 920,10 960,60 C1000,10 1040,10 1080,60 C1120,10 1160,10 1200,60 L1200,60 L0,60 Z" fill="currentColor"></path>
            </svg>
        </div>
    </section>

    <!-- 3. PURPOSE SECTION matching 01-beranda-publik_part1.png & part2.png -->
    <section id="about" class="reveal-on-scroll py-20 px-6 bg-white">
        <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-12 gap-12 items-center">
            <!-- Left: Circle with 4 cartoon avatar faces in 2x2 grid -->
            <div class="md:col-span-5 flex justify-center">
                <div class="w-72 sm:w-80 h-72 sm:h-80 rounded-full bg-[#FFF2D4] flex items-center justify-center p-8 shadow-inner relative">
                    <div class="grid grid-cols-2 gap-4">
                        <x-ui-avatar type="short" size="lg" />
                        <x-ui-avatar type="beret" size="lg" />
                        <x-ui-avatar type="afro" size="lg" />
                        <x-ui-avatar type="bangs" size="lg" />
                    </div>
                </div>
            </div>

            <!-- Right: Text Content & Quote -->
            <div class="md:col-span-7 space-y-6">
                <div>
                    <span class="inline-block px-3.5 py-1 rounded-full text-xs font-display font-bold bg-[#FFF2D4] text-[#D97706]">
                        Purpose
                    </span>
                    <h2 class="font-display font-bold text-3xl sm:text-4xl text-[#1F2F8A] mt-3">
                        What are children's classes for?
                    </h2>
                </div>

                <blockquote class="text-[#1D2B33]/85 font-sans text-base sm:text-lg leading-relaxed italic border-l-4 border-[#FBA93A] pl-4">
                    "Aware of the aspirations of the children of the world and their need for spiritual education, they extend their efforts widely to involve ever-growing contingents of participants in classes that become centres of attraction for the young and strengthen the roots of the Faith in society."
                </blockquote>

                <p class="text-xs sm:text-sm text-[#65737D] font-sans">
                    21 April 2008 message from the Universal House of Justice
                </p>

                <div>
                    <a href="{{ route('regions') }}"
                       class="inline-block px-7 py-3 rounded-full font-display font-bold text-sm bg-[#FBA93A] hover:bg-[#e89726] text-white shadow-md shadow-[#FBA93A]/30 transition-all">
                        Learn more
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. OUR COMMUNITIES IN WEST JAVA (Deep Royal Blue `#1F2F8A` / `#253ca8`) matching 01-beranda-publik_part2.png -->
    <section id="communities" class="reveal-on-scroll relative bg-[#1F2F8A] pt-16 pb-24 px-6 text-white overflow-hidden">
        <!-- Top Scalloped Cloud Divider from White into Deep Blue -->
        <div class="absolute top-0 left-0 right-0 w-full overflow-hidden leading-none z-0">
            <svg class="home-cloud-divider relative block h-10 text-white" viewBox="0 0 1200 40" preserveAspectRatio="none">
                <path d="M0,0 C40,30 80,30 120,0 C160,30 200,30 240,0 C280,30 320,30 360,0 C400,30 440,30 480,0 C520,30 560,30 600,0 C640,30 680,30 720,0 C760,30 800,30 840,0 C880,30 920,30 960,0 C1000,30 1040,30 1080,0 C1120,30 1160,30 1200,0 L1200,0 L0,0 Z" fill="currentColor"></path>
            </svg>
        </div>

        <div class="max-w-6xl mx-auto relative z-10 pt-4">
            <!-- Header -->
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="inline-block px-4 py-1 rounded-full text-xs font-display font-semibold bg-white/15 text-[#FFD86B]">
                    West Java Region
                </span>
                <h2 class="font-display font-bold text-3xl sm:text-4xl text-white mt-3">
                    Our Communities in West Java
                </h2>
                <p class="text-sm sm:text-base text-white/80 mt-2 font-sans">
                    {{ count($communities) }} active communities bringing children's classes to local neighbourhoods.
                </p>
            </div>

            <!-- Schematic Map Card matching mockup -->
            <div class="bg-white rounded-[28px] overflow-hidden shadow-2xl text-[#1D2B33] border-4 border-white/20">
                <!-- Top Orange Header Bar -->
                <div class="bg-[#FBA93A] px-6 sm:px-8 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 text-white">
                    <div>
                        <h3 class="font-display font-bold text-xl sm:text-2xl text-white">West Java Region</h3>
                        <p class="text-xs text-white/90 font-sans">
                            {{ count($communities) }} active communities · schematic map, not to geographic scale
                        </p>
                    </div>

                    <div class="flex items-center gap-6 text-center">
                        <div>
                            <span class="font-display font-bold text-2xl sm:text-3xl text-white block">{{ $stats['active_classes'] }}</span>
                            <span class="text-[11px] font-sans text-white/85">Classes</span>
                        </div>
                        <div>
                            <span class="font-display font-bold text-2xl sm:text-3xl text-white block">{{ $stats['active_teachers'] }}</span>
                            <span class="text-[11px] font-sans text-white/85">Teachers</span>
                        </div>
                        <div>
                            <span class="font-display font-bold text-2xl sm:text-3xl text-white block">{{ $stats['active_students'] }}</span>
                            <span class="text-[11px] font-sans text-white/85">Participants</span>
                        </div>
                    </div>
                </div>

                <!-- Card Body (Schematic Map + Legend) -->
                <div class="grid grid-cols-1 lg:grid-cols-12">
                    <!-- Left: Stylized Map Illustration -->
                    <div class="lg:col-span-8 p-6 sm:p-8 bg-[#8ED6EC]/25 relative min-h-[340px] flex items-center justify-center overflow-hidden">
                        <!-- Schematic map note tag -->
                        <span class="absolute top-4 left-4 text-[10px] font-mono px-2.5 py-1 rounded-full bg-white/80 text-[#65737D] border border-[#EFE4CC]">
                            Schematic map — not to geographic scale
                        </span>

                        <!-- Rolling Green Hills Background -->
                        <div class="absolute inset-0 pointer-events-none">
                            <svg viewBox="0 0 600 340" class="w-full h-full" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0,200 Q150,150 300,190 T600,170 L600,340 L0,340 Z" fill="#86EFAC" opacity="0.6" />
                                <path d="M0,240 Q200,190 400,230 T600,210 L600,340 L0,340 Z" fill="#4ADE80" opacity="0.7" />
                                <!-- Mountains & Trees -->
                                <polygon points="500,170 540,100 580,170" fill="#2E4BC0" opacity="0.4" />
                                <polygon points="530,170 560,110 590,170" fill="#1F2F8A" opacity="0.3" />
                                <circle cx="80" cy="220" r="12" fill="#22C55E" />
                                <line x1="80" y1="220" x2="80" y2="240" stroke="#78350F" stroke-width="3" />
                                <circle cx="280" cy="200" r="14" fill="#22C55E" />
                                <line x1="280" y1="200" x2="280" y2="225" stroke="#78350F" stroke-width="3" />
                                <circle cx="480" cy="220" r="12" fill="#22C55E" />
                                <line x1="480" y1="220" x2="480" y2="240" stroke="#78350F" stroke-width="3" />
                            </svg>
                        </div>

                        <!-- Community bubbles use aggregate data only -->
                        @php
                            $communityColors = ['#FBA93A', '#2E4BC0'];
                            $communityAvatars = [
                                ['short', 'pigtails'],
                                ['bangs', 'afro'],
                            ];
                        @endphp
                        <div class="relative z-10 flex w-full flex-wrap items-center justify-center gap-4 px-2 sm:flex-nowrap">
                            @forelse($communities->take(2) as $communityIndex => $community)
                                @if($communityIndex > 0)
                                    <div class="hidden w-12 shrink-0 border-b-2 border-dashed border-[#1F2F8A]/40 sm:block"></div>
                                @endif

                                <div
                                    class="flex h-44 w-44 shrink-0 flex-col items-center justify-between rounded-full border-2 bg-white/75 p-3 text-center shadow-md backdrop-blur-sm sm:h-52 sm:w-52 sm:p-4"
                                    style="border-color: {{ $communityColors[$communityIndex % count($communityColors)] }};"
                                >
                                    <div class="w-full rounded-xl px-2 py-1.5 {{ $communityIndex === 0 ? 'bg-[#FFD86B]' : 'border border-[#EFE4CC] bg-white' }}">
                                        <span class="block truncate font-display text-xs font-bold text-[#1D2B33]">{{ $community['name'] }}</span>
                                        <div class="mt-1 flex justify-around text-[10px] font-semibold text-[#1D2B33]">
                                            <span>{{ $community['classes_count'] }} <span class="block text-[9px] text-[#65737D]">Classes</span></span>
                                            <span>{{ $community['teachers_count'] }} <span class="block text-[9px] text-[#65737D]">Teachers</span></span>
                                            <span>{{ $community['students_count'] }} <span class="block text-[9px] text-[#65737D]">Participants</span></span>
                                        </div>
                                    </div>
                                    <div class="flex items-center -space-x-2">
                                        @foreach($communityAvatars[$communityIndex % count($communityAvatars)] as $avatarType)
                                            <x-ui-avatar :type="$avatarType" size="sm" class="border border-white" />
                                        @endforeach
                                    </div>
                                    <span class="rounded-full bg-[#2E9E5B] px-2.5 py-0.5 text-[10px] font-bold text-white">Active</span>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-dashed border-[#1F2F8A]/30 bg-white/70 px-6 py-8 text-center text-sm text-[#65737D]">
                                    No active communities yet.
                                </div>
                            @endforelse
                        </div>

                        <!-- Compass Icon -->
                        <div class="absolute bottom-4 right-4 w-9 h-9 rounded-full bg-white/80 border border-[#EFE4CC] flex items-center justify-center text-xs font-bold text-[#1F2F8A] shadow-xs">
                            N ↑
                        </div>
                    </div>

                    <!-- Right: Legend Panel matching mockup -->
                    <div class="lg:col-span-4 p-6 sm:p-8 bg-white border-t lg:border-t-0 lg:border-l border-[#EFE4CC] flex flex-col justify-between space-y-6">
                        <div>
                            <h4 class="font-display font-bold text-base text-[#1D2B33] mb-4">Legend</h4>

                            <div class="space-y-4">
                                @forelse($communities as $communityIndex => $community)
                                    <div @class(['border-t border-[#EFE4CC] pt-4' => $communityIndex > 0])>
                                        <div class="mb-1 flex items-center justify-between gap-2 text-xs font-semibold">
                                            <div class="flex min-w-0 items-center gap-2">
                                                <span class="size-3.5 shrink-0 rounded-sm" style="background-color: {{ $communityColors[$communityIndex % count($communityColors)] }};"></span>
                                                <span class="truncate font-bold text-[#1D2B33]">{{ $community['name'] }}</span>
                                            </div>
                                            <span class="rounded-full bg-[#E1F4E8] px-2 py-0.5 text-[10px] font-bold text-[#2E9E5B]">Active</span>
                                        </div>
                                        <div class="flex flex-wrap gap-x-3 gap-y-1 pl-5 font-sans text-xs text-[#65737D]">
                                            <span>{{ $community['classes_count'] }} Classes</span>
                                            <span>{{ $community['teachers_count'] }} Teachers</span>
                                            <span>{{ $community['students_count'] }} Participants</span>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-[#65737D]">No active communities yet.</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- Coming Soon -->
                        <div class="pt-4 border-t border-[#EFE4CC]">
                            <h5 class="text-xs font-display font-bold text-[#65737D] uppercase tracking-wider mb-2">Coming Soon</h5>
                            <div class="space-y-1.5 text-xs text-[#65737D] font-sans">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-xs bg-[#EFE4CC]"></span>
                                    <span>Central Java</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-xs bg-[#EFE4CC]"></span>
                                    <span>East Java</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-xs bg-[#EFE4CC]"></span>
                                    <span>Banten</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. OUR CLASSES SECTION matching 01-beranda-publik_part3.png -->
    <section id="classes" class="reveal-on-scroll py-20 px-6 bg-[#1F2F8A] text-white relative">
        <!-- Subtle Star Accents -->
        <span class="absolute top-10 left-12 text-[#FFD86B] text-2xl select-none">★</span>
        <span class="absolute top-20 right-20 text-[#FFD86B] text-xl select-none">★</span>
        <span class="absolute bottom-16 left-6 text-[#FFD86B]/40 text-lg select-none">★</span>
        <span class="absolute bottom-24 right-8 text-[#FFD86B]/40 text-2xl select-none">★</span>

        <div class="max-w-6xl mx-auto relative z-10">
            <!-- Header -->
            <div class="text-center max-w-2xl mx-auto mb-14">
                <span class="inline-block px-4 py-1 rounded-full text-xs font-display font-semibold bg-white/15 text-[#FFD86B]">
                    Our Classes
                </span>
                <h2 class="font-display font-bold text-3xl sm:text-4xl text-white mt-3 leading-tight">
                    Bahá'í children's classes for spiritual education
                </h2>
                <p class="text-sm sm:text-base text-white/80 mt-2 font-sans">
                    Choose a community and explore active classes in West Java.
                </p>
            </div>

            <!-- 3 Class Cards from DB -->
            @if($classes->isEmpty())
                <div class="text-center py-16 bg-white/10 rounded-[28px] border-2 border-dashed border-white/20 p-8 max-w-lg mx-auto">
                    <p class="font-display font-bold text-lg text-white">Belum ada kelas</p>
                    <p class="text-xs text-white/70 mt-1">Saat ini belum ada kelas aktif yang terdaftar.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @foreach($classes as $class)
                        <div class="home-motion-card bg-white rounded-[28px] border-2 border-white/20 overflow-hidden shadow-xl text-[#1D2B33] flex flex-col justify-between hover:-translate-y-1 transition-transform">
                            <div class="relative aspect-[16/10] w-full overflow-hidden bg-[#8ED6EC]/35">
                                @if($class['photo_url'])
                                    <img
                                        src="{{ $class['photo_url'] }}"
                                        alt="Class photo for {{ $class['name'] }}"
                                        class="absolute inset-0 h-full w-full object-cover"
                                        loading="lazy"
                                    >
                                @else
                                    <img
                                        src="{{ asset('images/imganak.png') }}"
                                        alt="Children learning and playing together"
                                        class="absolute inset-0 m-auto h-full w-full object-contain p-3"
                                        loading="lazy"
                                    >
                                @endif
                            </div>

                            <!-- Card Body -->
                            <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2 mb-3">
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#E1F4E8] text-[#2E9E5B]">
                                            Active
                                        </span>
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#E3EAFB] text-[#2E4BC0]">
                                            {{ $class['environment_name'] }} Community
                                        </span>
                                    </div>

                                    <h3 class="font-display font-bold text-xl text-[#1F2F8A] mb-1">
                                        {{ $class['name'] }}
                                    </h3>

                                    <p class="text-xs font-sans font-semibold text-[#65737D] mb-3">
                                        {{ $class['schedule'] }}
                                    </p>

                                    <p class="text-xs text-[#65737D] font-sans leading-relaxed">
                                        {{ $class['description'] }}
                                    </p>
                                </div>

                                <div class="pt-4 border-t border-[#EFE4CC] flex items-center justify-between">
                                    <span class="text-xs font-sans text-[#65737D]">
                                        Participants: <span class="font-bold text-[#1F2F8A]">{{ $class['student_count_display'] }}</span>
                                    </span>
                                    <a href="{{ route('classes') }}"
                                       class="px-5 py-2 rounded-full font-display font-bold text-xs bg-[#FBA93A] hover:bg-[#e89726] text-white shadow-xs transition-all">
                                        View Class
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <!-- 6. WHAT WE DO / ACTIVITIES SECTION matching 01-beranda-publik_part4.png -->
    <section id="activities" class="reveal-on-scroll py-20 px-6 bg-[#FFF8EA]">
        <div class="max-w-6xl mx-auto">
            <div class="text-center max-w-2xl mx-auto mb-14">
                <span class="inline-block px-4 py-1 rounded-full text-xs font-display font-bold bg-[#FFF2D4] text-[#D97706]">
                    What we do
                </span>
                <h2 class="font-display font-bold text-3xl sm:text-4xl text-[#1F2F8A] mt-3">
                    Activities that bring children joy
                </h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @php
                    $fallbackProgramCards = [
                        ['title' => 'Learning Together', 'description' => 'Thoughtful lesson plans for every session.'],
                        ['title' => 'Creative Play', 'description' => 'Games that nurture teamwork and imagination.'],
                        ['title' => 'Prayers and Songs', 'description' => 'Child-friendly prayers, songs, and memorization.'],
                        ['title' => 'Family Visits', 'description' => 'Teachers build strong connections with parents.'],
                    ];
                    $cardsToDisplay = $programCards->isNotEmpty() ? $programCards : collect($fallbackProgramCards);
                @endphp

                @foreach($cardsToDisplay as $index => $card)
                    @php
                        $cardImagePath = is_array($card) ? ($card['image_path'] ?? null) : $card->image_path;
                        $cardImageUrl = null;
                        if (! empty($cardImagePath) && \Illuminate\Support\Facades\Storage::disk('public')->exists($cardImagePath)) {
                            $cardImageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($cardImagePath);
                        }
                        if ($cardImageUrl === null) {
                            $cardImageUrl = asset('images/Kartu Kegiatan.png');
                        }
                    @endphp
                    <article class="home-motion-card flex flex-col items-center overflow-hidden rounded-[28px] border-2 border-[#EFE4CC] bg-white p-7 text-center shadow-xs transition-shadow hover:shadow-md">
                        <img src="{{ $cardImageUrl }}" alt="" class="mb-5 aspect-square w-28 rounded-full object-cover" loading="lazy">
                        <h3 class="font-display text-lg font-bold text-[#1D2B33]">{{ is_array($card) ? $card['title'] : $card->title }}</h3>
                        <p class="mt-2 text-xs leading-relaxed text-[#65737D]">{{ is_array($card) ? $card['description'] : $card->description }}</p>
                    </article>
                @endforeach
            </div>

            <!-- Published Activities List from Database if available -->
            @if($activities->isNotEmpty())
                <div class="mt-14 pt-10 border-t border-[#EFE4CC]">
                    <div class="text-center mb-8">
                        <span class="text-xs font-display font-bold uppercase tracking-wider text-[#65737D]">
                            Kegiatan Terbit Terbaru
                        </span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($activities as $act)
                            <article class="overflow-hidden rounded-2xl border border-[#EFE4CC] bg-white shadow-xs">
                                @if($act->approvedPhotos->isNotEmpty())
                                    <img src="{{ route('activity-photos.show', $act->approvedPhotos->first()) }}" alt="" class="h-40 w-full object-cover" loading="lazy">
                                @else
                                    <img src="{{ asset('images/imganak.png') }}" alt="" class="h-40 w-full object-cover" loading="lazy">
                                @endif
                                <div class="p-5">
                                <span class="text-xs font-display font-bold text-[#FBA93A]">
                                    {{ $act->activity_date?->format('d M Y') ?? 'Tanggal menyusul' }}
                                </span>
                                <h4 class="font-display font-bold text-base text-[#1F2F8A] mt-1">{{ $act->title }}</h4>
                                <p class="text-xs text-[#65737D] mt-1 line-clamp-2">{{ $act->description }}</p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

    <!-- 7. BIG NUMBERS STATS PITA matching 01-beranda-publik_part4.png -->
    <section class="reveal-on-scroll relative bg-[#1F2F8A] pt-14 pb-14 px-6 text-white overflow-hidden">
        <!-- Top Scalloped Cloud Divider from Cream into Deep Blue -->
        <div class="absolute top-0 left-0 right-0 w-full overflow-hidden leading-none z-0">
            <svg class="home-cloud-divider relative block h-8 text-[#FFF8EA]" viewBox="0 0 1200 40" preserveAspectRatio="none">
                <path d="M0,0 C40,30 80,30 120,0 C160,30 200,30 240,0 C280,30 320,30 360,0 C400,30 440,30 480,0 C520,30 560,30 600,0 C640,30 680,30 720,0 C760,30 800,30 840,0 C880,30 920,30 960,0 C1000,30 1040,30 1080,0 C1120,30 1160,30 1200,0 L1200,0 L0,0 Z" fill="currentColor"></path>
            </svg>
        </div>

        <div class="max-w-6xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-8 text-center relative z-10 pt-2">
            <!-- Active classes -->
            <div class="p-2">
                <div class="font-display font-bold text-5xl sm:text-6xl text-[#FBA93A] tracking-tight">
                    {{ $stats['active_classes'] }}
                </div>
                <div class="text-xs sm:text-sm font-sans font-semibold text-white/90 mt-2">
                    Active classes <span class="sr-only">Kelas Aktif</span>
                </div>
            </div>

            <!-- Teachers -->
            <div class="p-2 border-l border-white/15">
                <div class="font-display font-bold text-5xl sm:text-6xl text-[#FBA93A] tracking-tight">
                    {{ $stats['active_teachers'] }}
                </div>
                <div class="text-xs sm:text-sm font-sans font-semibold text-white/90 mt-2">
                    Teachers <span class="sr-only">Guru Aktif</span>
                </div>
            </div>

            <!-- Participants -->
            <div class="p-2 border-l border-white/15">
                <div class="font-display font-bold text-5xl sm:text-6xl text-[#FBA93A] tracking-tight">
                    {{ $stats['active_students'] }}
                </div>
                <div class="text-xs sm:text-sm font-sans font-semibold text-white/90 mt-2">
                    Participants <span class="sr-only">Peserta Aktif</span>
                </div>
            </div>

            <!-- Communities -->
            <div class="p-2 border-l border-white/15">
                <div class="font-display font-bold text-5xl sm:text-6xl text-[#FBA93A] tracking-tight">
                    {{ $stats['active_environments'] }}
                </div>
                <div class="text-xs sm:text-sm font-sans font-semibold text-white/90 mt-2">
                    Communities <span class="sr-only">Lingkungan Aktif</span>
                </div>
            </div>
        </div>
    </section>

    <!-- 8. FOOTER matching 01-beranda-publik_part4.png -->
    <footer class="bg-[#8ED6EC] text-[#1D2B33] py-10 px-6">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-6">
            <!-- Left Branding -->
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-[#FBA93A] text-white flex items-center justify-center font-display font-bold text-base shadow-xs">
                    K
                </div>
                <div>
                    <h3 class="font-display font-bold text-base text-[#1F2F8A]">Children's Classes</h3>
                    <p class="text-xs text-[#1D2B33]/70 font-sans">
                        West Java · Teaching Children's Classes Programme
                    </p>
                </div>
            </div>

            <!-- Right Copyright & Links -->
            <div class="text-center sm:text-right space-y-1">
                <p class="text-xs text-[#1D2B33]/70 font-sans">
                    © {{ date('Y') }} Children's Class Management System by Ammabel_audrey
                </p>
                <div class="flex items-center justify-center sm:justify-end gap-5 text-xs font-display font-semibold text-[#1F2F8A]">
                    <a href="{{ route('regions') }}" class="hover:underline">Regions</a>
                    <a href="#classes" class="hover:underline">Classes</a>
                    <a href="#about" class="hover:underline">About</a>
                </div>
            </div>
        </div>
    </footer>

<script>
    if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.documentElement.classList.add('motion-enabled');
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                } else {
                    entry.target.classList.remove('is-visible');
                }
            });
        }, { threshold: 0.12 });

        document.querySelectorAll('.reveal-on-scroll').forEach((section) => revealObserver.observe(section));
    }
</script>
</x-guest-layout>
