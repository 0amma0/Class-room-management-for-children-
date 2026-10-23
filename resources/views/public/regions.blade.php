<x-guest-layout title="{{ $regionName ? $regionName.' Region — Children’s Classes' : 'Regions — Children’s Classes' }}">
    @php
        $communityColors = ['#FBA93A', '#2E4BC0', '#2E9E5B'];
    @endphp

    <header class="sticky top-0 z-50 border-b border-[#EFE4CC]/80 bg-white/95 backdrop-blur-md">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-3 px-4 sm:h-20 sm:px-6">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <span class="flex size-10 items-center justify-center rounded-full bg-[#2E4BC0] font-display text-xl font-bold text-white">K</span>
                <span class="font-display text-base font-bold tracking-tight text-[#1F2F8A] sm:text-xl">Children's Classes</span>
            </a>

            <nav class="hidden items-center gap-8 font-sans text-sm font-semibold text-[#1D2B33] md:flex">
                <a href="{{ route('home') }}" class="hover:text-[#2E4BC0]">Home</a>
                <a href="{{ route('regions') }}" aria-current="page" class="text-[#2E4BC0]">Regions</a>
                <a href="{{ route('home') }}#activities" class="hover:text-[#2E4BC0]">Activities</a>
                <a href="{{ route('home') }}#about" class="hover:text-[#2E4BC0]">About</a>
            </nav>

            <details class="relative md:hidden">
                <summary class="flex size-10 cursor-pointer list-none items-center justify-center rounded-xl border border-[#EFE4CC] bg-white text-[#1F2F8A]" aria-label="Open navigation">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="size-5">
                        <path d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </summary>
                <nav class="absolute right-0 top-12 z-50 flex w-48 flex-col gap-1 rounded-2xl border border-[#EFE4CC] bg-white p-3 text-sm font-semibold text-[#1D2B33] shadow-xl">
                    <a href="{{ route('home') }}" class="rounded-lg px-3 py-2 hover:bg-[#FFF8EA]">Home</a>
                    <a href="{{ route('regions') }}" aria-current="page" class="rounded-lg bg-[#FFF8EA] px-3 py-2 text-[#2E4BC0]">Regions</a>
                    <a href="{{ route('home') }}#activities" class="rounded-lg px-3 py-2 hover:bg-[#FFF8EA]">Activities</a>
                    <a href="{{ route('home') }}#about" class="rounded-lg px-3 py-2 hover:bg-[#FFF8EA]">About</a>
                </nav>
            </details>

            <a href="{{ route('login') }}" class="rounded-full bg-[#FBA93A] px-6 py-2.5 font-display text-sm font-bold text-white transition-colors hover:bg-[#e89726]">
                Sign In
            </a>
        </div>
    </header>

    <main class="flex-1">
        <section class="relative overflow-hidden bg-gradient-to-r from-[#FBA93A] via-[#FFD28A] to-[#FFF8EA] px-6 py-8">
            <div class="mx-auto max-w-6xl">
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-[#1F2F8A]/75">
                    Indonesia <span class="px-2 text-[#FBA93A]">›</span> {{ $regionName ?? 'Regions' }} <span class="px-2 text-[#FBA93A]">›</span> Communities
                </p>
                <h1 class="mt-2 font-display text-3xl font-bold text-white sm:text-4xl">
                    {{ $regionName ?? 'Explore our regions' }}
                </h1>
                <p class="mt-2 text-sm text-[#1D2B33]/80">
                    Explore children's classes and communities in this region.
                </p>
            </div>
            <span aria-hidden="true" class="absolute right-10 top-4 size-28 rounded-full bg-white/20 sm:right-20 sm:size-36"></span>
        </section>

        <div class="mx-auto max-w-6xl space-y-5 px-4 py-7 sm:px-6 sm:py-8">
            @if($region)
                <section class="rounded-2xl border border-[#EFE4CC] bg-white p-5 shadow-sm sm:p-6">
                    <div class="mb-4">
                        <h2 class="font-display text-2xl font-bold text-[#1F2F8A]">Our Communities in Indonesia</h2>
                        <p class="mt-1 text-sm text-[#65737D]">Pilih wilayah, klaster, lalu lingkungan yang memiliki kelas anak.</p>
                    </div>
                    <form method="GET" action="{{ route('regions') }}" class="grid gap-3 md:grid-cols-3">
                        <label class="space-y-1 text-xs font-bold text-[#1D2B33]">
                            <span>Wilayah</span>
                            <select name="region_id" onchange="this.form.submit()" class="w-full rounded-xl border border-[#EFE4CC] bg-white px-3 py-2.5 text-sm font-normal">
                                @foreach($regions as $availableRegion)
                                    <option value="{{ $availableRegion->id }}" @selected($region->id === $availableRegion->id)>{{ $availableRegion->code ? ($regionNames[$availableRegion->code] ?? $availableRegion->name) : $availableRegion->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="space-y-1 text-xs font-bold text-[#1D2B33]">
                            <span>Klaster</span>
                            <select name="cluster_id" onchange="this.form.submit()" class="w-full rounded-xl border border-[#EFE4CC] bg-white px-3 py-2.5 text-sm font-normal">
                                @foreach($clusters as $cluster)
                                    <option value="{{ $cluster->id }}" @selected($selectedClusterId === $cluster->id)>{{ $cluster->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="space-y-1 text-xs font-bold text-[#1D2B33]">
                            <span>Lingkungan</span>
                            <select name="environment_id" onchange="this.form.submit()" class="w-full rounded-xl border border-[#EFE4CC] bg-white px-3 py-2.5 text-sm font-normal">
                                @foreach($selectedClusterCommunities as $community)
                                    <option value="{{ $community['id'] }}" @selected($selectedEnvironment && $selectedEnvironment['id'] === $community['id'])>{{ $community['name'] }}</option>
                                @endforeach
                            </select>
                        </label>
                    </form>
                </section>
                <section class="overflow-hidden rounded-2xl border border-[#EFE4CC] bg-white shadow-lg">
                    <div class="flex flex-col justify-between gap-5 bg-gradient-to-r from-[#1F2F8A] to-[#2E4BC0] px-5 py-5 text-white sm:flex-row sm:items-center sm:px-7">
                        <div>
                            <h2 class="font-display text-xl font-bold">{{ $regionName }} Region</h2>
                            <p class="mt-1 text-xs text-white/75">{{ $communities->count() }} active communities · schematic map, not to geographic scale</p>
                        </div>
                        <div class="grid grid-cols-3 gap-4 text-center sm:gap-7">
                            <div>
                                <strong class="block font-display text-2xl font-bold text-[#FFD86B] sm:text-3xl">{{ $stats['active_classes'] }}</strong>
                                <span class="text-[10px] text-white/80">Classes</span>
                            </div>
                            <div>
                                <strong class="block font-display text-2xl font-bold text-[#FFD86B] sm:text-3xl">{{ $stats['active_teachers'] }}</strong>
                                <span class="text-[10px] text-white/80">Teachers</span>
                            </div>
                            <div>
                                <strong class="block font-display text-2xl font-bold text-[#FFD86B] sm:text-3xl">{{ $stats['active_students'] }}</strong>
                                <span class="text-[10px] text-white/80">Participants</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid lg:grid-cols-12">
                        <div class="relative flex min-h-[330px] items-center justify-center overflow-hidden bg-[#BFE7F5] p-5 sm:p-8 lg:col-span-8">
                            <span class="absolute left-3 top-3 rounded-full bg-white/80 px-2.5 py-1 text-[9px] text-[#65737D]">Schematic map — not to geographic scale</span>
                            <svg aria-hidden="true" viewBox="0 0 600 340" preserveAspectRatio="none" class="absolute inset-0 size-full">
                                <path d="M0 210 Q150 150 300 195 T600 175 V340 H0Z" fill="#86EFAC" opacity=".7"/>
                                <path d="M0 248 Q180 190 380 235 T600 210 V340 H0Z" fill="#65B83C" opacity=".8"/>
                                <path d="M500 175 540 100 580 175Z" fill="#2E4BC0" opacity=".45"/>
                            </svg>

                            <div class="relative z-10 flex w-full flex-wrap items-center justify-center gap-3">
                                @if($selectedEnvironment)
                                    <a href="#classes-{{ $selectedEnvironment['id'] }}" class="flex max-w-xs flex-col items-center gap-3 rounded-3xl border-2 border-[#FBA93A] bg-white/90 p-5 text-center shadow-lg backdrop-blur-sm">
                                        <span class="flex size-14 items-center justify-center rounded-full bg-[#E1F4E8] text-2xl text-[#2E9E5B]" aria-hidden="true">●</span>
                                        <span>
                                            <span class="block font-display text-lg font-bold text-[#1F2F8A]">{{ $selectedEnvironment['name'] }}</span>
                                            <span class="mt-1 block text-xs text-[#65737D]">{{ $selectedEnvironment['cluster_name'] }}</span>
                                        </span>
                                        <span class="rounded-full bg-[#2E9E5B] px-3 py-1 text-[10px] font-bold text-white">Lingkungan aktif · {{ $selectedEnvironment['classes_count'] }} kelas</span>
                                    </a>
                                @else
                                    <p class="rounded-xl bg-white/85 px-5 py-4 text-center text-sm text-[#65737D]">Pilih klaster dan lingkungan untuk melihat lokasi komunitas.</p>
                                @endif
                                <p class="absolute bottom-14 left-1/2 max-w-56 -translate-x-1/2 rounded-lg bg-white/85 px-3 py-2 text-center text-[10px] text-[#65737D]">Marker bersifat skematis; koordinat geografis lingkungan tidak tersedia.</p>
                            </div>
                            <span aria-hidden="true" class="absolute bottom-4 right-4 flex size-9 items-center justify-center rounded-full border border-[#EFE4CC] bg-white/80 text-xs font-bold text-[#1F2F8A]">N ↑</span>
                        </div>

                        <aside class="space-y-5 border-t border-[#EFE4CC] bg-white p-5 sm:p-6 lg:col-span-4 lg:border-l lg:border-t-0">
                            <div>
                                <h3 class="mb-4 font-display text-sm font-bold text-[#1D2B33]">Legend</h3>
                                <div class="space-y-4">
                                    @forelse($communities as $index => $community)
                                        <div @class(['border-t border-[#EFE4CC] pt-4' => $index > 0])>
                                            <div class="flex items-center justify-between gap-2 text-xs">
                                                <span class="flex min-w-0 items-center gap-2 font-bold text-[#1D2B33]">
                                                    <span class="size-3 shrink-0 rounded-sm" style="background-color: {{ $communityColors[$index % count($communityColors)] }};"></span>
                                                    <span class="truncate">{{ $community['name'] }}</span>
                                                </span>
                                                <span class="rounded-full bg-[#E1F4E8] px-2 py-0.5 text-[9px] font-bold text-[#2E9E5B]">Active</span>
                                            </div>
                                            <p class="mt-1 pl-5 text-[10px] text-[#65737D]">
                                                {{ $community['classes_count'] }} Classes · {{ $community['teachers_count'] }} Teachers · {{ $community['students_count'] }} Participants
                                            </p>
                                        </div>
                                    @empty
                                        <p class="text-xs text-[#65737D]">No active communities yet.</p>
                                    @endforelse
                                </div>
                            </div>
                            @if($comingSoonRegions->isNotEmpty())
                                <div class="border-t border-[#EFE4CC] pt-4">
                                    <h4 class="mb-2 text-[10px] font-bold uppercase tracking-wider text-[#65737D]">Coming Soon</h4>
                                    <ul class="space-y-1.5 text-xs text-[#65737D]">
                                        @foreach($comingSoonRegions as $comingSoonRegion)
                                            <li class="flex items-center gap-2">
                                                <span class="size-2 rounded-sm bg-[#FFD86B]"></span>
                                                {{ $comingSoonRegion['name'] }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </aside>
                    </div>
                </section>

                <section class="rounded-2xl border border-[#EFE4CC] bg-white p-5 shadow-sm sm:p-6">
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <h2 class="font-display text-base font-bold text-[#1D2B33]">Clusters</h2>
                        <span class="rounded-full border border-[#FBA93A] bg-[#FFF2D4] px-2.5 py-0.5 text-[9px] text-[#1D2B33]">Community groups in {{ $regionName }}</span>
                    </div>
                    <div class="grid gap-3 md:grid-cols-2">
                        @forelse($clusters as $cluster)
                            <article class="rounded-2xl border border-[#F1E2C5] bg-[#FFF8EA] p-4">
                                <h3 class="font-display font-bold text-[#1D2B33]">{{ $cluster->name }}</h3>
                                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                    @forelse($communitiesByCluster->get($cluster->id, collect()) as $community)
                                        <a href="#classes-{{ $community['id'] }}" class="rounded-xl border border-[#EFE4CC] bg-white p-3 transition-shadow hover:shadow-sm">
                                            <span class="flex items-center justify-between gap-2">
                                                <span class="truncate font-display text-sm font-bold text-[#1D2B33]">{{ $community['name'] }}</span>
                                                <span class="rounded-full bg-[#E1F4E8] px-2 py-0.5 text-[9px] font-bold text-[#2E9E5B]">Active</span>
                                            </span>
                                            <span class="mt-2 block text-[10px] text-[#65737D]">{{ $community['classes_count'] }} Classes · {{ $community['teachers_count'] }} Teachers · {{ $community['students_count'] }} Participants</span>
                                            <span class="mt-3 inline-flex rounded-full border border-[#EFE4CC] px-3 py-1 text-[10px] font-semibold">View {{ $community['name'] }} classes</span>
                                        </a>
                                    @empty
                                        <p class="text-xs text-[#65737D]">No active communities in this cluster.</p>
                                    @endforelse
                                </div>
                            </article>
                        @empty
                            <p class="rounded-xl border border-dashed border-[#EFE4CC] p-5 text-sm text-[#65737D]">No active clusters are registered in this region yet.</p>
                        @endforelse
                    </div>
                </section>

                <section class="space-y-4">
                    @forelse($communities as $community)
                        <div id="classes-{{ $community['id'] }}" class="scroll-mt-24 overflow-hidden rounded-2xl border border-[#EFE4CC] bg-white shadow-sm">
                            <div class="border-b border-[#EFE4CC] px-5 py-4">
                                <h2 class="font-display text-base font-bold text-[#1D2B33]">Classes in {{ $community['name'] }}</h2>
                                <p class="mt-1 text-xs text-[#65737D]">{{ $community['cluster_name'] }} · {{ $community['classes_count'] }} active classes</p>
                            </div>
                            @php($communityClasses = $classesByEnvironment->get($community['id'], collect()))
                            @if($communityClasses->isNotEmpty())
                                <div class="overflow-x-auto">
                                    <table class="w-full min-w-[520px] text-left text-xs">
                                        <thead class="bg-[#F2E7D0] text-[10px] uppercase tracking-wide text-[#65737D]">
                                            <tr>
                                                <th class="px-4 py-3 font-semibold">Classes</th>
                                                <th class="px-4 py-3 font-semibold">Schedule</th>
                                                <th class="px-4 py-3 font-semibold">Participants</th>
                                                <th class="px-4 py-3 font-semibold">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-[#EFE4CC]">
                                            @foreach($communityClasses as $class)
                                                <tr>
                                                    <td class="px-4 py-3">{{ $class->name }}</td>
                                                    <td class="px-4 py-3 text-[#65737D]">{{ $class->schedule ?? 'Schedule to be confirmed' }}</td>
                                                    <td class="px-4 py-3 text-[#65737D]">{{ $class->active_students_count }} participants</td>
                                                    <td class="px-4 py-3"><span class="rounded-full bg-[#E1F4E8] px-2 py-1 text-[9px] font-bold text-[#2E9E5B]">Active</span></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="px-5 py-4 text-xs text-[#65737D]">No active classes are registered in this community yet.</p>
                            @endif
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-[#EFE4CC] bg-white p-6 text-sm text-[#65737D]">
                            No active communities are available to show classes.
                        </div>
                    @endforelse
                </section>
            @else
                <section class="rounded-2xl border border-dashed border-[#EFE4CC] bg-white p-8 text-center shadow-sm">
                    <h2 class="font-display text-xl font-bold text-[#1F2F8A]">No active region yet</h2>
                    <p class="mt-2 text-sm text-[#65737D]">Region details will appear here when an administrator activates a region.</p>
                </section>
            @endif

            <div class="h-0.5 bg-gradient-to-r from-[#FBA93A] via-[#65B83C] to-[#2E4BC0]"></div>
            <p class="pb-2 text-center text-[10px] text-[#65737D]">Children's Classes · {{ $regionName ?? 'Regions' }} <span class="px-2 text-[#FBA93A]">✦</span> {{ $communities->count() }} active communities</p>
        </div>
    </main>
</x-guest-layout>
