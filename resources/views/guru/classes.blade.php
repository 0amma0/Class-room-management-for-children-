<x-app-layout title="My Classes — Children's Classes">
    <div class="mx-auto max-w-7xl space-y-6">
        <header class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-muted">Teacher workspace / Classes</p>
                <h1 class="mt-1 font-display text-3xl font-bold text-navy">Classes I Teach</h1>
                <p class="mt-1 text-sm text-muted">Your active class assignments and learning groups.</p>
            </div>
            <span class="rounded-full bg-accent-soft px-4 py-2 text-sm font-bold text-orange">{{ $classes->count() }} active {{ \Illuminate\Support\Str::plural('class', $classes->count()) }}</span>
        </header>

        @if($classes->isEmpty())
            <section class="flex min-h-80 flex-col items-center justify-center rounded-3xl border border-line bg-white px-6 py-12 text-center shadow-sm">
                <span class="flex size-14 items-center justify-center rounded-2xl bg-primary-soft text-royal">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-7"><path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v17H6.5A2.5 2.5 0 0 0 4 21z"></path><path d="M4 4.5V21M8 6h8M8 10h8"></path></svg>
                </span>
                <h2 class="mt-4 font-display text-xl font-bold text-navy">No active classes assigned</h2>
                <p class="mt-1 max-w-md text-sm text-muted">Classes assigned to your teacher account will appear here.</p>
            </section>
        @else
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach($classes as $class)
                    <article class="overflow-hidden rounded-3xl border border-line bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="relative flex h-36 items-center justify-center overflow-hidden bg-sky/35">
                            <span class="absolute -right-5 -top-8 size-36 rounded-full bg-white/30"></span>
                            <span class="relative flex size-20 items-center justify-center rounded-3xl bg-orange font-display text-3xl font-bold text-white shadow-md">
                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($class->name, 0, 1)) }}
                            </span>
                        </div>
                        <div class="space-y-4 p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold text-muted">{{ $class->environment?->name ?? 'Community' }}</p>
                                    <h2 class="mt-1 font-display text-xl font-bold text-navy">{{ $class->name }}</h2>
                                </div>
                                <span class="rounded-full bg-ok-soft px-3 py-1 text-xs font-bold text-ok">Active</span>
                            </div>

                            <dl class="space-y-2 border-t border-line pt-4 text-sm">
                                <div class="flex justify-between gap-3">
                                    <dt class="text-muted">Schedule</dt>
                                    <dd class="text-right font-semibold text-ink">{{ $class->schedule ?? 'Not set' }}</dd>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <dt class="text-muted">Location</dt>
                                    <dd class="text-right font-semibold text-ink">{{ $class->location ?? 'Not set' }}</dd>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <dt class="text-muted">Participants</dt>
                                    <dd class="text-right font-semibold text-navy">{{ $class->students_count }}</dd>
                                </div>
                            </dl>

                            <a href="{{ route('guru.students') }}" class="inline-flex w-full items-center justify-center rounded-full bg-orange px-4 py-2.5 text-sm font-bold text-white transition hover:bg-orange/90">
                                View participants
                            </a>
                            <a href="{{ route('guru.class-progress', ['class_id' => $class->id]) }}" class="inline-flex w-full items-center justify-center rounded-full border border-line px-4 py-2.5 text-sm font-bold text-navy transition hover:bg-cream">
                                View lesson checklist
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>