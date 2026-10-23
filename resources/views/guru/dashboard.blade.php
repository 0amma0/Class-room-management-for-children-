<x-app-layout title="Teacher Dashboard — Children's Classes">
    <div class="mx-auto max-w-7xl space-y-7">
        <section class="relative overflow-hidden rounded-[28px] bg-sky p-6 shadow-sm sm:p-8 lg:p-10">
            <div class="pointer-events-none absolute -right-10 -top-12 size-56 rounded-full bg-white/20"></div>
            <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center">
                <x-ui-avatar type="bangs" size="xl" class="border-4 border-white shadow-md" :alt="auth()->user()->name" />
                <div class="max-w-2xl">
                    <span class="inline-flex rounded-full bg-white/70 px-3 py-1 text-xs font-bold text-navy">Teacher workspace</span>
                    <h1 class="mt-3 font-display text-3xl font-bold text-navy sm:text-4xl">
                        Hello, {{ auth()->user()->name }}
                    </h1>
                    <p class="mt-2 text-sm leading-relaxed text-ink/75 sm:text-base">
                        Your space to support children's learning, attendance, and development.
                    </p>
                </div>
            </div>
        </section>

        <section class="grid gap-5 xl:grid-cols-2">
            <div class="rounded-3xl border border-line bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="font-display text-xl font-bold text-navy">Classes I Teach</h2>
                        <p class="mt-1 text-sm text-muted">Your active class assignments.</p>
                    </div>
                    <a href="{{ route('guru.classes') }}" class="text-sm font-bold text-royal hover:underline">See all <span aria-hidden="true">›</span></a>
                </div>

                @forelse($classes as $class)
                    <div class="flex items-center justify-between gap-4 border-t border-line py-4 first:border-0 first:pt-0 last:pb-0">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl {{ $loop->odd ? 'bg-orange text-white' : 'bg-royal text-white' }} font-display font-bold">
                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($class->name, 0, 1)) }}
                            </span>
                            <div class="min-w-0">
                                <p class="truncate font-display font-bold text-navy">{{ $class->name }}</p>
                                <p class="truncate text-xs text-muted">{{ $class->environment?->name ?? 'Community' }}{{ $class->schedule ? ' · '.$class->schedule : '' }}</p>
                            </div>
                        </div>
                        <span class="shrink-0 rounded-full bg-primary-soft px-3 py-1 text-xs font-bold text-royal">
                            {{ $class->students_count }} participants
                        </span>
                    </div>
                @empty
                    <div class="rounded-2xl bg-cream px-4 py-6 text-center">
                        <p class="font-semibold text-navy">No active class assignments</p>
                        <p class="mt-1 text-sm text-muted">Your assigned classes will appear here.</p>
                    </div>
                @endforelse
            </div>

            <div class="rounded-3xl border border-line bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="font-display text-xl font-bold text-navy">Published Activities</h2>
                        <p class="mt-1 text-sm text-muted">Latest updates from your classes.</p>
                    </div>
                    <a href="{{ route('guru.activities') }}" class="text-sm font-bold text-royal hover:underline">See all <span aria-hidden="true">›</span></a>
                </div>

                @forelse($activities as $activity)
                    <div class="flex items-center gap-4 border-t border-line py-4 first:border-0 first:pt-0 last:pb-0">
                        <time datetime="{{ $activity->activity_date->toDateString() }}" class="flex size-12 shrink-0 flex-col items-center justify-center rounded-xl bg-accent-soft text-center text-navy">
                            <span class="text-[10px] font-bold uppercase">{{ $activity->activity_date->format('M') }}</span>
                            <span class="font-display text-lg font-bold leading-none">{{ $activity->activity_date->format('j') }}</span>
                        </time>
                        <div class="min-w-0">
                            <p class="truncate font-display font-bold text-navy">{{ $activity->title }}</p>
                            <p class="text-xs text-muted">{{ $activity->class?->name }}</p>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl bg-cream px-4 py-6 text-center">
                        <p class="font-semibold text-navy">No published activities yet</p>
                        <p class="mt-1 text-sm text-muted">Published class activities will appear here.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section aria-labelledby="modules-title">
            <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 id="modules-title" class="font-display text-2xl font-bold text-navy">Your teaching tools</h2>
                    <p class="mt-1 text-sm text-muted">Choose a module to continue.</p>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <a href="{{ route('guru.students') }}" class="group rounded-2xl border border-line bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="flex size-11 items-center justify-center rounded-xl bg-primary-soft text-royal">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-5"><circle cx="9" cy="8" r="3"></circle><path d="M3 20v-1a6 6 0 0 1 12 0v1zM16 8h5M18.5 5.5v5"></path></svg>
                    </span>
                    <h3 class="mt-4 font-display text-lg font-bold text-navy">Participants</h3>
                    <p class="mt-1 text-sm leading-relaxed text-muted">View the children assigned to your classes.</p>
                    <span class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-royal group-hover:gap-3">Open module <span aria-hidden="true">→</span></span>
                </a>

                <a href="{{ route('guru.attendances') }}" class="group rounded-2xl border border-line bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="flex size-11 items-center justify-center rounded-xl bg-ok-soft text-ok">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-5"><rect x="4" y="4" width="16" height="17" rx="2"></rect><path d="M8 2v4M16 2v4M4 9h16M8 13h2M14 13h2M8 17h2"></path></svg>
                    </span>
                    <h3 class="mt-4 font-display text-lg font-bold text-navy">Attendance</h3>
                    <p class="mt-1 text-sm leading-relaxed text-muted">Keep track of class attendance.</p>
                    <span class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-royal group-hover:gap-3">Open module <span aria-hidden="true">→</span></span>
                </a>

                <a href="{{ route('guru.developments') }}" class="group rounded-2xl border border-line bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="flex size-11 items-center justify-center rounded-xl bg-accent-soft text-orange">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-5"><path d="m3 17 6-6 4 4 8-9"></path><path d="M15 6h6v6"></path></svg>
                    </span>
                    <h3 class="mt-4 font-display text-lg font-bold text-navy">Development</h3>
                    <p class="mt-1 text-sm leading-relaxed text-muted">Record observations about children's progress.</p>
                    <span class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-royal group-hover:gap-3">Open module <span aria-hidden="true">→</span></span>
                </a>

                <a href="{{ route('guru.activities') }}" class="group rounded-2xl border border-line bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="flex size-11 items-center justify-center rounded-xl bg-danger-soft text-pink">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-5"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9z"></path></svg>
                    </span>
                    <h3 class="mt-4 font-display text-lg font-bold text-navy">Activities</h3>
                    <p class="mt-1 text-sm leading-relaxed text-muted">Share class activities with your community.</p>
                    <span class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-royal group-hover:gap-3">Open module <span aria-hidden="true">→</span></span>
                </a>

                <a href="{{ route('guru.classes') }}" class="group rounded-2xl border border-line bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="flex size-11 items-center justify-center rounded-xl bg-ok-soft text-ok">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-5"><path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v17H6.5A2.5 2.5 0 0 0 4 21z"></path><path d="M4 4.5V21M8 6h8M8 10h8"></path></svg>
                    </span>
                    <h3 class="mt-4 font-display text-lg font-bold text-navy">Classes & Lessons</h3>
                    <p class="mt-1 text-sm leading-relaxed text-muted">Open your assigned classes and learning materials.</p>
                    <span class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-royal group-hover:gap-3">Open module <span aria-hidden="true">→</span></span>
                </a>

                <a href="{{ route('guru.materials') }}" class="group rounded-2xl border border-line bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="flex size-11 items-center justify-center rounded-xl bg-primary-soft text-royal">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-5"><path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v17H6.5A2.5 2.5 0 0 0 4 21z"></path><path d="M4 4.5V21M8 6h8M8 10h8M8 14h6"></path></svg>
                    </span>
                    <h3 class="mt-4 font-display text-lg font-bold text-navy">Draft Lagu & Materi</h3>
                    <p class="mt-1 text-sm leading-relaxed text-muted">Buka dokumen lesson, lagu, gambar, dan materi ajar.</p>
                    <span class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-royal group-hover:gap-3">Buka materi <span aria-hidden="true">→</span></span>
                </a>
            </div>
        </section>
    </div>
</x-app-layout>
