<x-app-layout title="Refleksi Guru — Children's Classes">
    <article class="mx-auto max-w-4xl space-y-6">
        <a href="{{ route(auth()->user()->isAdmin() ? 'admin.class-progress' : 'guru.teacher-module') }}" class="text-sm font-bold text-royal hover:underline">← Kembali</a>
        <section class="space-y-5 rounded-3xl border border-line bg-white p-6 shadow-sm sm:p-8">
            <header>
                <p class="text-sm font-semibold text-muted">Refleksi pembelajaran · {{ $reflection->reflection_date->format('d M Y') }}</p>
                <h1 class="mt-1 font-display text-3xl font-bold text-navy">{{ $reflection->teacher?->full_name ?? 'Guru' }}</h1>
                <p class="mt-2 text-sm text-muted">
                    {{ $reflection->class?->name }}
                    @if($reflection->lessonPlan?->theme) · Lesson plan: {{ $reflection->lessonPlan->theme }}@endif
                </p>
            </header>
            <dl class="grid gap-4 sm:grid-cols-2">
                @foreach([
                    'objectives_met' => 'Pencapaian tujuan',
                    'plan_alignment' => 'Kesesuaian dengan rencana',
                    'what_went_well' => 'Hal yang berjalan baik',
                    'challenges' => 'Tantangan',
                    'improvement' => 'Perbaikan berikutnya',
                    'note' => 'Catatan tambahan',
                ] as $field => $label)
                    <div class="space-y-1 rounded-2xl bg-cream/60 p-4">
                        <dt class="text-xs font-bold uppercase tracking-wide text-muted">{{ $label }}</dt>
                        <dd class="whitespace-pre-line text-sm leading-relaxed text-ink">{{ $reflection->{$field} ?: 'Belum ada catatan.' }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>
    </article>
</x-app-layout>
