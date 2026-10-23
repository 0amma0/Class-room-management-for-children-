<x-app-layout title="Rencana Kegiatan — Children's Classes">
    <div class="mx-auto max-w-7xl space-y-6">
        <header>
            <p class="text-sm font-semibold text-muted">Ruang Guru / Perencanaan</p>
            <h1 class="mt-1 font-display text-3xl font-bold text-navy">Lesson Plan</h1>
            <p class="mt-1 text-sm text-muted">Buat rencana kelas anak atau kegiatan lainnya. Pengingat email dijalankan setiap hari pukul 08.00 WIB sesuai konfigurasi scheduler dan email aplikasi.</p>
        </header>
        @if(session('success'))<x-ui-alert variant="ok">{{ session('success') }}</x-ui-alert>@endif
        @if($errors->any())
            <x-ui-alert variant="danger" title="Periksa kembali rencana kegiatan">
                <ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </x-ui-alert>
        @endif

        <form method="POST" action="{{ route('guru.lesson-plans.store') }}" class="space-y-6 rounded-3xl border border-line bg-white p-6 shadow-sm sm:p-8">
            @csrf
            <input type="hidden" name="plan_type" value="class">
            <div>
                <h2 class="font-display text-xl font-bold text-navy">Rencana kelas anak</h2>
                <p class="text-sm text-muted">Pilih kelas yang ditugaskan kepada Anda.</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <label class="space-y-1 text-sm font-semibold"><span>Kelas</span><select name="class_id" required class="w-full rounded-xl border border-line px-3 py-2">@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->name }} ({{ $class->students_count }} peserta)</option>@endforeach</select></label>
                <label class="space-y-1 text-sm font-semibold"><span>Tanggal kegiatan</span><input type="date" name="execution_date" value="{{ old('execution_date', now()->toDateString()) }}" required class="w-full rounded-xl border border-line px-3 py-2"></label>
                <label class="space-y-1 text-sm font-semibold"><span>Level</span><input name="level" value="{{ old('level') }}" required maxlength="100" class="w-full rounded-xl border border-line px-3 py-2"></label>
                <label class="space-y-1 text-sm font-semibold"><span>Tema</span><input name="theme" value="{{ old('theme') }}" required maxlength="255" class="w-full rounded-xl border border-line px-3 py-2"></label>
            </div>
            <label class="block space-y-1 text-sm font-semibold"><span>Tujuan pembelajaran</span><textarea name="objectives" rows="2" required maxlength="5000" class="w-full rounded-xl border border-line px-3 py-2">{{ old('objectives') }}</textarea></label>
            <label class="block max-w-sm space-y-1 text-sm font-semibold"><span>Kirim pengingat email</span><select name="reminder_days" required class="w-full rounded-xl border border-line px-3 py-2"><option value="1">1 hari sebelum kegiatan</option><option value="2" @selected(old('reminder_days') === '2')>2 hari sebelum kegiatan</option></select></label>

            <section class="space-y-3">
                <div><h3 class="font-display text-lg font-bold text-navy">Susunan kegiatan</h3><p class="text-sm text-muted">Gunakan bagian yang tercantum di buku pegangan Guru.</p></div>
                <div class="overflow-x-auto rounded-2xl border border-line">
                    <table class="w-full min-w-[700px] text-left text-sm">
                        <thead class="bg-cream text-xs font-bold uppercase tracking-wide text-muted"><tr><th class="px-4 py-3">Bagian</th><th class="px-4 py-3">Waktu</th><th class="px-4 py-3">Kegiatan / catatan</th></tr></thead>
                        <tbody class="divide-y divide-line">
                            @foreach($programSections as $section)
                                <tr><th class="whitespace-nowrap px-4 py-3 font-semibold">{{ $section }}</th><td class="px-4 py-2"><input name="program_times[{{ $section }}]" value="{{ old('program_times.'.$section) }}" maxlength="50" class="w-full rounded-lg border border-line px-3 py-2" aria-label="Waktu {{ $section }}"></td><td class="px-4 py-2"><input name="program[{{ $section }}]" value="{{ old('program.'.$section) }}" maxlength="1000" class="w-full rounded-lg border border-line px-3 py-2" aria-label="Kegiatan {{ $section }}"></td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
            <section class="space-y-3">
                <div><h3 class="font-display text-lg font-bold text-navy">Alat dan peruntukan</h3></div>
                <div class="grid gap-3 sm:grid-cols-2">
                    @for($index = 0; $index < 3; $index++)
                        <div class="grid gap-2 rounded-2xl bg-cream/60 p-3">
                            <label class="space-y-1 text-xs font-semibold"><span>Alat</span><input name="materials[{{ $index }}][tool]" value="{{ old("materials.$index.tool") }}" maxlength="255" class="w-full rounded-lg border border-line bg-white px-3 py-2"></label>
                            <label class="space-y-1 text-xs font-semibold"><span>Peruntukan</span><input name="materials[{{ $index }}][purpose]" value="{{ old("materials.$index.purpose") }}" maxlength="500" class="w-full rounded-lg border border-line bg-white px-3 py-2"></label>
                        </div>
                    @endfor
                </div>
            </section>
            <label class="block space-y-1 text-sm font-semibold"><span>Catatan susulan peserta</span><textarea name="catch_up_notes" rows="2" maxlength="5000" class="w-full rounded-xl border border-line px-3 py-2">{{ old('catch_up_notes') }}</textarea></label>
            <button @disabled($classes->isEmpty()) class="rounded-full bg-orange px-6 py-3 text-sm font-bold text-white hover:bg-orange/90 disabled:cursor-not-allowed disabled:opacity-50">Simpan rencana kelas</button>
        </form>

        <form method="POST" action="{{ route('guru.lesson-plans.store') }}" class="grid gap-4 rounded-3xl border border-line bg-white p-6 shadow-sm sm:grid-cols-2 lg:grid-cols-4 sm:p-8">
            @csrf
            <input type="hidden" name="plan_type" value="other">
            <div class="sm:col-span-2 lg:col-span-4"><h2 class="font-display text-xl font-bold text-navy">Rencana kegiatan lainnya</h2><p class="text-sm text-muted">Contoh: rapat, pelatihan, atau kegiatan di luar kelas anak.</p></div>
            <label class="space-y-1 text-sm font-semibold"><span>Tanggal kegiatan</span><input type="date" name="execution_date" required class="w-full rounded-xl border border-line px-3 py-2"></label>
            <label class="space-y-1 text-sm font-semibold"><span>Nama kegiatan</span><input name="theme" required maxlength="255" class="w-full rounded-xl border border-line px-3 py-2"></label>
            <label class="space-y-1 text-sm font-semibold"><span>Pengingat email</span><select name="reminder_days" required class="w-full rounded-xl border border-line px-3 py-2"><option value="1">1 hari sebelumnya</option><option value="2">2 hari sebelumnya</option></select></label>
            <label class="space-y-1 text-sm font-semibold"><span>Tujuan / catatan</span><textarea name="objectives" required maxlength="5000" rows="2" class="w-full rounded-xl border border-line px-3 py-2"></textarea></label>
            <div class="sm:col-span-2 lg:col-span-4"><button class="rounded-full bg-royal px-6 py-3 text-sm font-bold text-white hover:bg-royal/90">Simpan kegiatan lain</button></div>
        </form>

        <section class="overflow-hidden rounded-3xl border border-line bg-white shadow-sm">
            @if($plans->isEmpty())
                <p class="p-6 text-sm text-muted">Belum ada rencana kegiatan.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-left text-sm">
                        <thead class="bg-cream text-xs font-bold uppercase tracking-wide text-muted"><tr><th class="px-5 py-4">Tanggal</th><th class="px-5 py-4">Jenis / kelas</th><th class="px-5 py-4">Tema dan tujuan</th><th class="px-5 py-4">Pengingat</th><th class="px-5 py-4">Kelola</th></tr></thead>
                        <tbody class="divide-y divide-line">
                            @foreach($plans as $plan)
                                <tr>
                                    <td class="px-5 py-4">{{ $plan->execution_date?->format('d M Y') }}</td>
                                    <td class="px-5 py-4">{{ $plan->plan_type === 'class' ? ($plan->class?->name ?? 'Kelas') : 'Kegiatan lainnya' }}@if($plan->level)<span class="block text-xs text-muted">{{ $plan->level }}</span>@endif</td>
                                    <td class="px-5 py-4"><strong>{{ $plan->theme }}</strong><span class="block max-w-md text-xs text-muted">{{ $plan->objectives }}</span><span class="mt-1 block text-xs text-muted">{{ $plan->student_count }} peserta · {{ ucfirst(str_replace('_', ' ', $plan->status)) }}</span></td>
                                    <td class="px-5 py-4">
                                        {{ $plan->reminder_days }} hari sebelumnya
                                        @if($plan->reminder_sent_at)<span class="block text-xs text-green-700">Email terkirim</span>@endif
                                    </td>
                                    <td class="px-5 py-4">
                                        @if($plan->execution_date)<a href="{{ $plan->googleCalendarUrl() }}" target="_blank" rel="noopener noreferrer" class="font-bold text-royal hover:underline">Google Calendar</a>@endif
                                        <details class="mt-2">
                                            <summary class="cursor-pointer font-bold text-royal">Kelola</summary>
                                            <div class="min-w-64 space-y-3 py-3">
                                                <form method="POST" action="{{ route('guru.lesson-plans.update', $plan) }}" class="space-y-2">
                                                    @csrf @method('PUT')
                                                    <input type="hidden" name="plan_type" value="{{ $plan->plan_type }}">
                                                    <input type="hidden" name="class_id" value="{{ $plan->class_id }}">
                                                    <label class="block text-xs font-semibold">Tanggal<input type="date" name="execution_date" value="{{ $plan->execution_date?->toDateString() }}" required class="mt-1 w-full rounded-lg border border-line px-2 py-1.5"></label>
                                                    @if($plan->plan_type === 'class')<label class="block text-xs font-semibold">Level<input name="level" value="{{ $plan->level }}" required maxlength="100" class="mt-1 w-full rounded-lg border border-line px-2 py-1.5"></label>@endif
                                                    <label class="block text-xs font-semibold">{{ $plan->plan_type === 'class' ? 'Tema' : 'Nama kegiatan' }}<input name="theme" value="{{ $plan->theme }}" required maxlength="255" class="mt-1 w-full rounded-lg border border-line px-2 py-1.5"></label>
                                                    <label class="block text-xs font-semibold">Tujuan / catatan<textarea name="objectives" rows="2" required maxlength="5000" class="mt-1 w-full rounded-lg border border-line px-2 py-1.5">{{ $plan->objectives }}</textarea></label>
                                                    <label class="block text-xs font-semibold">Pengingat<select name="reminder_days" class="mt-1 w-full rounded-lg border border-line px-2 py-1.5"><option value="1" @selected($plan->reminder_days === 1)>1 hari sebelumnya</option><option value="2" @selected($plan->reminder_days === 2)>2 hari sebelumnya</option></select></label>
                                                    <label class="block text-xs font-semibold">Catatan susulan<textarea name="catch_up_notes" rows="2" maxlength="5000" class="mt-1 w-full rounded-lg border border-line px-2 py-1.5">{{ $plan->catch_up_notes }}</textarea></label>
                                                    <button class="rounded-full bg-orange px-4 py-2 text-xs font-bold text-white">Simpan perubahan</button>
                                                </form>
                                                <form method="POST" action="{{ route('guru.lesson-plans.destroy', $plan) }}">@csrf @method('DELETE')<button class="rounded-full border border-danger/30 px-4 py-2 text-xs font-bold text-danger">Hapus rencana</button></form>
                                            </div>
                                        </details>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-line px-5 py-4">{{ $plans->links() }}</div>
            @endif
        </section>
    </div>
</x-app-layout>
