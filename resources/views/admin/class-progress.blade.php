<x-app-layout title="Kelas — Children's Classes">
    <div class="mx-auto max-w-7xl space-y-6">
        <header>
            <p class="text-sm font-semibold text-muted">{{ $isAdmin ? 'Administrator workspace' : 'Teacher workspace' }} / Lesson plan</p>
            <h1 class="mt-1 font-display text-3xl font-bold text-navy">Kelas</h1>
            <p class="mt-1 text-sm text-muted">Pantau checklist 24 lesson, lesson plan, dan refleksi guru.</p>
        </header>
        @if(session('success'))<x-ui-alert variant="ok">{{ session('success') }}</x-ui-alert>@endif
        @if($errors->any())<x-ui-alert variant="danger" title="Checklist belum dapat disimpan"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></x-ui-alert>@endif

        <section class="rounded-3xl border border-line bg-white p-5 shadow-sm sm:p-6">
            @if($classes->isEmpty())
                <p class="text-sm text-muted">{{ $isAdmin ? 'Belum ada kelas pada wilayah yang dipilih.' : 'Belum ada kelas yang dapat dikelola.' }}</p>
            @else
                <form method="GET" action="{{ route($isAdmin ? 'admin.class-progress' : 'guru.class-progress') }}" class="flex flex-wrap items-end gap-3">
                    @if($isAdmin)
                        <label class="min-w-48 flex-1 space-y-1 text-sm font-semibold"><span>Filter wilayah</span><select name="region_id" onchange="this.form.submit()" class="w-full rounded-xl border border-line px-3 py-2">
                            @foreach($regions as $region)<option value="{{ $region->id }}" @selected($selectedRegion?->id === $region->id)>{{ $region->name }}</option>@endforeach
                        </select></label>
                    @endif
                    <label class="min-w-56 flex-1 space-y-1 text-sm font-semibold"><span>Pilih kelas</span><select name="class_id" onchange="this.form.submit()" class="w-full rounded-xl border border-line px-3 py-2">
                        @foreach($classes as $class)<option value="{{ $class->id }}" @selected($selectedClass?->id === $class->id)>{{ $class->name }}</option>@endforeach
                    </select></label>
                </form>
            @endif
        </section>

        @if($selectedClass)
            @if($isAdmin)
                <section class="space-y-4 rounded-3xl border border-line bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold text-muted">{{ $selectedClass->environment?->cluster?->region?->name }} · {{ $selectedClass->environment?->name }}</p>
                            <h2 class="mt-1 font-display text-xl font-bold text-navy">{{ $selectedClass->name }}</h2>
                            <p class="mt-1 text-sm text-muted">Guru: {{ $selectedClass->teachers->pluck('full_name')->join(', ') ?: 'Belum ditugaskan' }}</p>
                        </div>
                        <div class="rounded-2xl bg-primary-soft px-5 py-3 text-center">
                            <strong class="block font-display text-2xl font-bold text-navy">{{ $completedLessonCount }} / {{ $lessons->count() }}</strong>
                            <span class="text-xs font-semibold text-muted">lesson diperbarui</span>
                        </div>
                    </div>
                    <p class="border-t border-line pt-3 text-xs text-muted">
                        Pembaruan terakhir:
                        {{ $lastProgressUpdate ? \Illuminate\Support\Carbon::parse($lastProgressUpdate)->format('d M Y H:i') : 'Belum ada pembaruan' }}
                    </p>
                </section>
            @else
            <section class="space-y-4 rounded-3xl border border-line bg-white p-5 shadow-sm sm:p-6">
                <div>
                    <h2 class="font-display text-xl font-bold text-navy">Lesson — {{ $selectedClass->name }}</h2>
                    <p class="mt-1 text-sm text-muted">Centang lesson setelah kegiatan pembelajaran terlaksana.</p>
                </div>
                <form method="POST" action="{{ route($isAdmin ? 'admin.class-progress.update' : 'guru.class-progress.update') }}" class="space-y-5">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
                    <label class="block max-w-xs space-y-1 text-xs font-semibold text-muted"><span>Tanggal penyelesaian untuk lesson baru</span><input type="date" name="completed_at" value="{{ old('completed_at', now()->toDateString()) }}" max="{{ now()->toDateString() }}" class="w-full rounded-xl border border-line px-3 py-2 text-sm text-ink"></label>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($lessons as $lesson)
                            @php($lessonProgress = $progress->get($lesson->id))
                            <label class="flex items-start gap-3 rounded-2xl border border-line p-4">
                                <input type="checkbox" name="completed_lessons[]" value="{{ $lesson->id }}" @checked(old('completed_lessons') ? in_array($lesson->id, old('completed_lessons')) : $lessonProgress?->status === 'selesai') class="mt-1 size-4 rounded border-line text-royal focus:ring-royal">
                                <span><span class="block font-bold text-navy">{{ $lesson->title }}</span>
                                    <span class="mt-1 block text-xs text-muted">{{ $lessonProgress?->status === 'selesai' ? 'Selesai'.($lessonProgress->completed_at ? ' · '.$lessonProgress->completed_at->format('d M Y') : '') : 'Belum dilaksanakan' }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <button class="rounded-full bg-orange px-6 py-3 text-sm font-bold text-white hover:bg-orange/90">Simpan checklist lesson</button>
                </form>
            </section>

            <section class="space-y-3 rounded-3xl border border-line bg-white p-5 shadow-sm sm:p-6">
                <h2 class="font-display text-xl font-bold text-navy">Lesson plan terbaru</h2>
                @forelse($lessonPlans as $plan)
                    <article class="rounded-2xl bg-cream/60 p-4">
                        <p class="font-bold text-navy">{{ $plan->theme ?: 'Lesson plan' }} <span class="font-normal text-muted">· {{ $plan->execution_date?->format('d M Y') ?: 'Tanggal belum ditentukan' }}</span></p>
                        <p class="mt-1 text-sm text-muted">{{ $plan->objectives ?: 'Belum ada tujuan pembelajaran.' }}</p>
                        <p class="mt-1 text-xs text-muted">Dibuat oleh {{ $plan->creator?->name ?? 'Guru' }}</p>
                    </article>
                @empty
                    <p class="text-sm text-muted">Belum ada lesson plan untuk kelas ini.</p>
                @endforelse
            </section>
            @endif
        @endif

        <section class="space-y-4">
            <div>
                <h2 class="font-display text-2xl font-bold text-navy">Refleksi Guru</h2>
                <p class="mt-1 text-sm text-muted">{{ $isAdmin ? 'Refleksi guru dan kelas pada wilayah yang dipilih.' : 'Refleksi yang dibagikan guru dapat dibaca bersama oleh admin dan seluruh guru.' }}</p>
            </div>
            <div class="grid gap-3 md:grid-cols-2">
                @forelse($reflections as $reflection)
                    <a href="{{ route('reflections.show', $reflection) }}" class="rounded-2xl border border-line bg-white p-5 shadow-sm transition hover:shadow-md">
                        <p class="text-xs font-semibold text-muted">{{ $reflection->reflection_date->format('d M Y') }} · {{ $reflection->class?->name }}</p>
                        <h3 class="mt-2 font-display font-bold text-navy">{{ $reflection->teacher?->full_name ?? 'Guru' }}</h3>
                        <p class="mt-1 text-sm text-muted">{{ \Illuminate\Support\Str::limit($reflection->what_went_well ?: $reflection->note ?: $reflection->challenges ?: 'Refleksi pembelajaran', 140) }}</p>
                        <span class="mt-3 inline-flex text-sm font-bold text-royal">Baca refleksi →</span>
                    </a>
                @empty
                    <p class="rounded-2xl border border-line bg-white p-5 text-sm text-muted">Belum ada refleksi guru.</p>
                @endforelse
            </div>
            @if($reflections->hasPages()){{ $reflections->links() }}@endif
        </section>
    </div>
</x-app-layout>
