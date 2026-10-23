<x-app-layout title="Refleksi Guru — Children's Classes">
    <div class="mx-auto max-w-7xl space-y-6">
        <header class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-muted">Teacher handbook / Refleksi Guru</p>
                <h1 class="mt-1 font-display text-3xl font-bold text-navy">Refleksi Guru</h1>
                <p class="mt-1 text-sm text-muted">Catat sesi Anda dan baca refleksi bersama dari guru lain.</p>
            </div>
            <a href="{{ route('guru.teacher-module.pdf') }}" target="_blank" rel="noopener" class="rounded-full bg-orange px-5 py-3 text-sm font-bold text-white hover:bg-orange/90">Buka PDF pegangan guru</a>
        </header>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([['Presensi', 'guru.attendances', 'Catat kehadiran peserta setiap sesi.'], ['Perkembangan', 'guru.developments', 'Tulis pengamatan perkembangan peserta.'], ['Lesson plan', 'guru.lesson-plans', 'Rencanakan waktu, kegiatan, materi, dan tujuan.'], ['Kelas & checklist', 'guru.class-progress', 'Tandai lesson yang telah dilaksanakan di kelas Anda.'], ['Kunjungan orang tua', 'guru.parent-visits', 'Dokumentasikan kunjungan dan tindak lanjut.']] as [$title, $routeName, $description])
                <a href="{{ route($routeName) }}" class="rounded-2xl border border-line bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"><h2 class="font-display text-lg font-bold text-navy">{{ $title }}</h2><p class="mt-2 text-sm text-muted">{{ $description }}</p></a>
            @endforeach
        </div>

        @if(session('success'))<x-ui-alert variant="ok">{{ session('success') }}</x-ui-alert>@endif
        @if($errors->any())<x-ui-alert variant="danger" title="Periksa data refleksi"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></x-ui-alert>@endif

        <form method="POST" action="{{ route('guru.teacher-module.reflections.store') }}" class="grid gap-4 rounded-3xl border border-line bg-white p-6 shadow-sm sm:grid-cols-2">
            @csrf
            <div class="sm:col-span-2"><h2 class="font-display text-xl font-bold text-navy">Tulis refleksi sesi</h2><p class="mt-1 text-sm text-muted">Refleksi akan dibagikan kepada admin dan guru aktif lainnya.</p></div>
            <label class="space-y-1 text-sm font-semibold"><span>Kelas</span><select name="class_id" required class="w-full rounded-xl border border-line px-3 py-2">@foreach($classes as $class)<option value="{{ $class->id }}" @selected(old('class_id') == $class->id)>{{ $class->name }}</option>@endforeach</select></label>
            <label class="space-y-1 text-sm font-semibold"><span>Lesson plan terkait</span><select name="lesson_plan_id" class="w-full rounded-xl border border-line px-3 py-2"><option value="">Tidak terkait lesson plan</option>@foreach($plans as $plan)<option value="{{ $plan->id }}" @selected(old('lesson_plan_id') == $plan->id)>{{ $plan->execution_date?->format('d M Y') }} — {{ $plan->class_id }} — {{ $plan->theme }}</option>@endforeach</select></label>
            <label class="space-y-1 text-sm font-semibold"><span>Tanggal refleksi</span><input type="date" name="reflection_date" value="{{ old('reflection_date', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required class="w-full rounded-xl border border-line px-3 py-2"></label>
            @foreach(['objectives_met' => 'Apakah tujuan sesi tercapai?', 'plan_alignment' => 'Kesesuaian dengan rencana', 'what_went_well' => 'Hal yang berjalan baik', 'challenges' => 'Tantangan', 'improvement' => 'Hal yang dapat diperbaiki', 'note' => 'Catatan tambahan'] as $name => $label)
                <label class="space-y-1 text-sm font-semibold"><span>{{ $label }}</span><textarea name="{{ $name }}" rows="2" maxlength="5000" class="w-full rounded-xl border border-line px-3 py-2">{{ old($name) }}</textarea></label>
            @endforeach
            <div class="sm:col-span-2"><button class="rounded-full bg-orange px-6 py-3 text-sm font-bold text-white hover:bg-orange/90">Simpan refleksi</button></div>
        </form>

        <section class="overflow-hidden rounded-3xl border border-line bg-white shadow-sm">
            <div class="border-b border-line p-5 sm:px-6">
                <h2 class="font-display text-xl font-bold text-navy">Refleksi bersama</h2>
                <p class="mt-1 text-sm text-muted">Guru dapat membaca refleksi satu sama lain. Hanya refleksi milik Anda yang dapat diedit.</p>
            </div>
            @if($reflections->isEmpty())
                <p class="p-6 text-sm text-muted">Belum ada refleksi yang dicatat.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-left text-sm">
                        <thead class="bg-cream text-xs font-bold uppercase tracking-wide text-muted"><tr><th class="px-5 py-4">Guru / kelas</th><th class="px-5 py-4">Tujuan / rencana</th><th class="px-5 py-4">Tantangan / perbaikan</th><th class="px-5 py-4">Detail / tindakan</th></tr></thead>
                        <tbody class="divide-y divide-line">
                            @foreach($reflections as $reflection)
                                <tr>
                                    <td class="px-5 py-4">
                                        <strong>{{ $reflection->teacher?->full_name ?? 'Guru' }}</strong>
                                        <span class="block text-xs text-muted">{{ $reflection->class?->name }} · {{ $reflection->reflection_date->format('d M Y') }}</span>
                                    </td>
                                    <td class="px-5 py-4"><strong>Tujuan:</strong> {{ $reflection->objectives_met ?: '—' }}<span class="mt-1 block"><strong>Rencana:</strong> {{ $reflection->plan_alignment ?: '—' }}</span></td>
                                    <td class="px-5 py-4"><strong>Tantangan:</strong> {{ $reflection->challenges ?: '—' }}<span class="mt-1 block"><strong>Perbaikan:</strong> {{ $reflection->improvement ?: '—' }}</span></td>
                                    <td class="px-5 py-4">
                                        <a href="{{ route('reflections.show', $reflection) }}" class="font-bold text-royal hover:underline">Baca refleksi</a>
                                        @if($reflection->teacher_id === auth()->user()->teacher_id)
                                            <details class="mt-2">
                                                <summary class="cursor-pointer font-bold text-muted">Kelola refleksi saya</summary>
                                                <div class="min-w-64 space-y-3 py-3">
                                                    <form method="POST" action="{{ route('guru.teacher-module.reflections.update', $reflection) }}" class="space-y-2">
                                                        @csrf @method('PUT')
                                                        <input type="hidden" name="class_id" value="{{ $reflection->class_id }}">
                                                        <input type="hidden" name="lesson_plan_id" value="{{ $reflection->lesson_plan_id }}">
                                                        <label class="block text-xs font-semibold">Tanggal<input type="date" name="reflection_date" value="{{ $reflection->reflection_date->toDateString() }}" max="{{ now()->toDateString() }}" required class="mt-1 w-full rounded-lg border border-line px-2 py-1.5"></label>
                                                        @foreach(['objectives_met' => 'Tujuan tercapai', 'plan_alignment' => 'Kesesuaian rencana', 'what_went_well' => 'Hal baik', 'challenges' => 'Tantangan', 'improvement' => 'Perbaikan', 'note' => 'Catatan'] as $name => $label)
                                                            <label class="block text-xs font-semibold">{{ $label }}<textarea name="{{ $name }}" rows="2" maxlength="5000" class="mt-1 w-full rounded-lg border border-line px-2 py-1.5">{{ $reflection->{$name} }}</textarea></label>
                                                        @endforeach
                                                        <button class="rounded-full bg-orange px-4 py-2 text-xs font-bold text-white">Simpan perubahan</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('guru.teacher-module.reflections.destroy', $reflection) }}">@csrf @method('DELETE')<button class="rounded-full border border-danger/30 px-4 py-2 text-xs font-bold text-danger">Hapus refleksi</button></form>
                                                </div>
                                            </details>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-line px-5 py-4">{{ $reflections->links() }}</div>
            @endif
        </section>
    </div>
</x-app-layout>
