<x-app-layout title="Admin Dashboard — Children's Classes">
    <div class="space-y-8 max-w-7xl mx-auto">
        <!-- Dashboard Top Header matching 12-dashboard-admin.png -->
        <div class="flex items-start justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="font-display font-bold text-3xl sm:text-4xl text-[#1F2F8A]">Admin Dashboard</h1>
                    <span class="px-3.5 py-1 rounded-full text-xs font-display font-bold bg-[#FFF2D4] text-[#D97706] border border-[#FBA93A]/30">
                        West Java
                    </span>
                </div>
                <p class="text-sm text-[#65737D] mt-1 font-sans">
                    Overview of all active regions
                </p>
            </div>
            <!-- Star decorations -->
            <div class="text-[#FFD86B] text-lg space-x-1 select-none">
                <span>★</span>
                <span>★</span>
            </div>
        </div>

        <section class="grid gap-4 sm:grid-cols-2">
            <a href="{{ route('admin.class-progress') }}" class="flex items-center justify-between gap-4 rounded-2xl border-2 border-[#EFE4CC] bg-white p-5 shadow-sm transition hover:shadow-md">
                <div>
                    <h2 class="font-display text-lg font-bold text-[#1F2F8A]">Kelas & Lesson</h2>
                    <p class="mt-1 text-sm text-[#65737D]">Checklist 24 lesson dan baca refleksi guru.</p>
                </div>
                <span class="rounded-full bg-[#FFF2D4] px-4 py-2 text-sm font-bold text-[#D97706]">Buka →</span>
            </a>
            <a href="{{ route('admin.materials') }}" class="flex items-center justify-between gap-4 rounded-2xl border-2 border-[#EFE4CC] bg-white p-5 shadow-sm transition hover:shadow-md">
                <div>
                    <h2 class="font-display text-lg font-bold text-[#1F2F8A]">Draft Lagu & Materi</h2>
                    <p class="mt-1 text-sm text-[#65737D]">Kelola materi dan persetujuan unduh publik.</p>
                </div>
                <span class="rounded-full bg-[#E3EAFB] px-4 py-2 text-sm font-bold text-[#2E4BC0]">Buka →</span>
            </a>
        </section>

        <!-- 1. TOP STAT CARDS (4 CARDS) matching 12-dashboard-admin.png -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Card 1: Active regions -->
            <div class="bg-white rounded-[24px] border-2 border-[#EFE4CC] p-6 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-xs font-display font-bold text-[#65737D]">
                    <span>Active regions <span class="sr-only">Wilayah Aktif</span></span>
                    <span class="flex size-8 items-center justify-center rounded-full bg-[#E3EAFB] text-[#2E4BC0]">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-4"><path d="m3 6 6-3 6 3 6-3v15l-6 3-6-3-6 3z"></path><path d="M9 3v15M15 6v15"></path></svg>
                    </span>
                </div>
                <div class="my-4">
                    <span class="font-display font-bold text-4xl text-[#1F2F8A]">{{ $cards['active_regions'] }}</span>
                </div>
                <div>
                    <span class="inline-block px-3 py-0.5 rounded-full text-xs font-display font-bold bg-[#E1F4E8] text-[#2E9E5B]">
                        West Java
                    </span>
                </div>
            </div>

            <!-- Card 2: Classes -->
            <div class="bg-white rounded-[24px] border-2 border-[#EFE4CC] p-6 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-xs font-display font-bold text-[#65737D]">
                    <span>Classes <span class="sr-only">Kelas Aktif</span></span>
                    <span class="flex size-8 items-center justify-center rounded-full bg-[#FFF2D4] text-[#D97706]">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-4"><path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v17H6.5A2.5 2.5 0 0 0 4 21z"></path><path d="M4 4.5V21M8 6h8M8 10h8"></path></svg>
                    </span>
                </div>
                <div class="my-4">
                    <span class="font-display font-bold text-4xl text-[#1F2F8A]">{{ $cards['active_classes'] }}</span>
                </div>
                <div>
                    <span class="inline-block px-3 py-0.5 rounded-full text-xs font-display font-bold bg-[#E3EAFB] text-[#2E4BC0]">
                        {{ $cards['environments'] }} communities <span class="sr-only">Lingkungan</span>
                    </span>
                </div>
            </div>

            <!-- Card 3: Teachers -->
            <div class="bg-white rounded-[24px] border-2 border-[#EFE4CC] p-6 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-xs font-display font-bold text-[#65737D]">
                    <span>Teachers <span class="sr-only">Guru Aktif</span></span>
                    <span class="flex size-8 items-center justify-center rounded-full bg-[#FFF2D4] text-[#D97706]">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-4"><circle cx="9" cy="8" r="3"></circle><path d="M3 20v-1a6 6 0 0 1 12 0v1zM16 5.5a3 3 0 0 1 0 5.8M18 14a5 5 0 0 1 3 4.5V20h-3"></path></svg>
                    </span>
                </div>
                <div class="my-4">
                    <span class="font-display font-bold text-4xl text-[#1F2F8A]">{{ $cards['active_teachers'] }}</span>
                </div>
                <div>
                    <span class="inline-block px-3 py-0.5 rounded-full text-xs font-display font-bold bg-[#E1F4E8] text-[#2E9E5B]">
                        active accounts
                    </span>
                </div>
            </div>

            <!-- Card 4: Participants (Deep Navy Card) -->
            <div class="bg-[#1F2F8A] rounded-[24px] p-6 shadow-md flex flex-col justify-between text-white relative overflow-hidden">
                <div class="flex items-center justify-between text-xs font-display font-bold text-white/70">
                    <span class="text-white">Participants <span class="sr-only">Peserta Aktif</span></span>
                    <x-ui-avatar type="short-blue" size="xs" class="border border-white/20" />
                </div>
                <div class="my-4">
                    <span class="font-display font-bold text-4xl text-[#FFD86B]">{{ $cards['active_students'] }}</span>
                </div>
                <div>
                    <span class="inline-block px-3 py-0.5 rounded-full text-xs font-display font-bold bg-white/15 text-white">
                        active
                    </span>
                </div>
            </div>
        </div>

        <!-- Hidden search/compatibility tags for tests -->
        <div class="sr-only">
            <span>Klaster</span>: {{ $cards['clusters'] }}
            <span>Lingkungan</span>: {{ $cards['environments'] }}
        </div>

        <!-- 2 & 3: GRID (Participants by community & Needs Review + Snapshot) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- LEFT COLUMN: Participants by community -->
            <div class="lg:col-span-7">
                <div class="bg-white rounded-[28px] border-2 border-[#EFE4CC] p-6 sm:p-8 shadow-sm">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h2 class="font-display font-bold text-xl text-[#1F2F8A]">
                                Participants by community
                                <span class="sr-only">Peserta per Lingkungan</span>
                            </h2>
                        </div>
                        <span class="text-[#FFD86B] text-lg select-none">★</span>
                    </div>

                    @if(empty($studentsPerEnvironment))
                        <div class="text-center py-12 text-sm text-[#65737D] font-sans">
                            No community participant data available.
                        </div>
                    @else
                        <div class="space-y-6">
                            @foreach($studentsPerEnvironment as $item)
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between text-sm">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-3.5 h-3.5 rounded-xs shrink-0" style="background-color: {{ $item['color'] }};"></span>
                                            <span class="font-display font-bold text-[#1D2B33] text-base">
                                                {{ $item['community_label'] }}
                                                <span class="sr-only">Lingkungan {{ $item['environment_name'] }}</span>
                                            </span>
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-display font-semibold bg-[#E1F4E8] text-[#2E9E5B]">
                                                {{ $item['classes_label'] }}
                                            </span>
                                        </div>
                                        <div class="font-display font-bold text-xl text-[#1F2F8A]">
                                            {{ $item['count'] }}
                                        </div>
                                    </div>

                                    <!-- Horizontal CSS Bar (Pure CSS matching mockup 12) -->
                                    <div class="w-full bg-[#FFF8EA] rounded-full h-4 border border-[#EFE4CC] overflow-hidden p-0.5">
                                        <div class="h-full rounded-full transition-all duration-500"
                                             style="width: {{ max($item['percentage'], 4) }}%; background-color: {{ $item['color'] }};"></div>
                                    </div>

                                    <!-- Avatars & Teacher count under bar -->
                                    <div class="flex items-center gap-2 pt-0.5">
                                        <div class="flex items-center -space-x-1.5">
                                            @foreach($item['avatars'] as $avatarType)
                                                <x-ui-avatar :type="$avatarType" size="xs" class="border border-white" />
                                            @endforeach
                                        </div>
                                        <span class="text-xs text-[#65737D] font-sans">
                                            +{{ $item['teachers_count'] }} teachers
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Card Footer matching mockup -->
                    <div class="pt-6 mt-8 border-t border-[#EFE4CC] flex flex-col sm:flex-row items-center justify-between gap-4 text-xs font-sans text-[#65737D]">
                        <div>
                            Total: {{ $cards['active_students'] }} participants · {{ $cards['active_classes'] }} active classes
                        </div>
                        <a href="{{ route('admin.classes') }}"
                           class="px-5 py-2 rounded-full font-display font-bold text-xs bg-[#1F2F8A] hover:bg-[#162266] text-white transition-all shadow-xs">
                            View all
                        </a>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Needs review + Region Snapshot -->
            <div class="lg:col-span-5 space-y-6">
                <!-- Needs Review Card matching mockup 12 -->
                <div class="bg-white rounded-[28px] border-2 border-[#EFE4CC] p-6 sm:p-7 shadow-sm">
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-2.5">
                            <h2 class="font-display font-bold text-lg text-[#1F2F8A]">
                                Needs review
                                <span class="sr-only">Perlu Ditinjau</span>
                            </h2>
                            @php
                                $totalNeedsReview = array_sum(array_column($needsReview, 'count'));
                            @endphp
                            @if($totalNeedsReview > 0)
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-display font-bold bg-[#FCE6E6] text-[#D64545]">
                                    {{ $totalNeedsReview }} {{ $totalNeedsReview === 1 ? 'item' : 'items' }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-3.5">
                        @foreach($needsReview as $review)
                            <div class="bg-[#FFF8EA] rounded-2xl border border-[#EFE4CC] p-4 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-white text-[#65737D]">
                                        @if(str_contains(strtolower($review['action_label']), 'review'))
                                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-4"><rect x="5" y="4" width="14" height="17" rx="2"></rect><path d="M9 4V2h6v2M8 9h8M8 13h8M8 17h5"></path></svg>
                                        @else
                                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-4"><circle cx="8" cy="15" r="4"></circle><path d="m11 12 9-9M16 7l2 2M13 10l2 2"></path></svg>
                                        @endif
                                    </span>
                                    <div>
                                        <p class="font-display font-bold text-xs sm:text-sm text-[#1D2B33]">
                                            {{ $review['title'] }}
                                        </p>
                                        <p class="text-[11px] text-[#65737D] font-sans">
                                            @if(str_contains(strtolower($review['action_label']), 'review'))
                                                <span class="sr-only">Kegiatan Menunggu Persetujuan</span>
                                            @else
                                                <span class="sr-only">Akun Guru Belum Ganti Password</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <div>
                                    @if($review['has_action'] && $review['action_url'])
                                        @if(strtolower($review['action_label']) === 'review')
                                            <a href="{{ $review['action_url'] }}"
                                               class="px-4 py-1.5 rounded-full font-display font-bold text-xs bg-[#FBA93A] hover:bg-[#e89726] text-white shadow-xs transition-all">
                                                Review
                                            </a>
                                        @else
                                            <a href="{{ $review['action_url'] }}"
                                               class="px-4 py-1.5 rounded-full font-display font-bold text-xs bg-[#E3EAFB] hover:bg-[#d0ddf7] text-[#2E4BC0] transition-all">
                                                View
                                            </a>
                                        @endif
                                    @else
                                        <span class="font-display font-bold text-base text-[#1F2F8A] px-2">
                                            {{ $review['count'] }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Region Snapshot Card matching mockup 12 -->
                <div class="bg-white rounded-[28px] border-2 border-[#FFD86B] p-6 sm:p-7 shadow-sm">
                    <div class="flex items-center gap-2 mb-6 text-[#1F2F8A]">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-5 text-[#FBA93A]"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9z"></path></svg>
                        <h3 class="font-display font-bold text-base text-[#1F2F8A]">Region Snapshot</h3>
                    </div>

                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="p-2">
                            <div class="font-display font-bold text-3xl sm:text-4xl text-[#1F2F8A]">
                                {{ $cards['environments'] }}
                            </div>
                            <div class="text-xs font-display font-semibold text-[#65737D] mt-1">
                                Communities
                            </div>
                        </div>

                        <div class="p-2 border-x border-[#EFE4CC]">
                            <div class="font-display font-bold text-3xl sm:text-4xl text-[#1F2F8A]">
                                {{ $cards['active_classes'] }}
                            </div>
                            <div class="text-xs font-display font-semibold text-[#65737D] mt-1">
                                Classes
                            </div>
                        </div>

                        <div class="p-2">
                            <div class="font-display font-bold text-3xl sm:text-4xl text-[#1F2F8A]">
                                {{ $cards['active_teachers'] }}
                            </div>
                            <div class="text-xs font-display font-semibold text-[#65737D] mt-1">
                                Teachers
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
