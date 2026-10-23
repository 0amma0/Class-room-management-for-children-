<x-app-layout title="Galeri Kelas — Children's Classes">
    <div class="mx-auto max-w-7xl space-y-6">
        <header>
            <p class="text-sm font-semibold text-muted">Ruang Guru / Dokumentasi</p>
            <h1 class="mt-1 font-display text-3xl font-bold text-navy">Galeri kelas</h1>
            <p class="mt-1 text-sm text-muted">Foto dan karya disimpan privat, serta hanya dapat dilihat oleh Guru yang memiliki akses ke kelas tersebut.</p>
        </header>
        @if(session('success'))<x-ui-alert variant="ok">{{ session('success') }}</x-ui-alert>@endif
        @if($errors->any())<x-ui-alert variant="danger" title="Foto belum dapat disimpan">{{ $errors->first() }}</x-ui-alert>@endif

        <form method="POST" action="{{ route('guru.gallery.store') }}" enctype="multipart/form-data" class="grid gap-4 rounded-3xl border border-line bg-white p-5 shadow-sm sm:grid-cols-2 sm:p-7 lg:grid-cols-3">
            @csrf
            <label class="space-y-1 text-sm font-semibold"><span>Kelas</span><select name="class_id" required class="w-full rounded-xl border border-line px-3 py-2">@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->name }}</option>@endforeach</select></label>
            <label class="space-y-1 text-sm font-semibold"><span>Tanggal kegiatan</span><input type="date" name="photo_date" value="{{ old('photo_date', now()->toDateString()) }}" required class="w-full rounded-xl border border-line px-3 py-2"></label>
            <label class="space-y-1 text-sm font-semibold"><span>Sesi kegiatan</span><input name="session_label" value="{{ old('session_label') }}" maxlength="100" class="w-full rounded-xl border border-line px-3 py-2" placeholder="Contoh: Sesi cerita"></label>
            <label class="space-y-1 text-sm font-semibold sm:col-span-2"><span>Foto kegiatan atau karya anak</span><input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required class="w-full rounded-xl border border-line px-3 py-2 text-sm"></label>
            <label class="space-y-1 text-sm font-semibold"><span>Keterangan</span><input name="caption" value="{{ old('caption') }}" maxlength="255" class="w-full rounded-xl border border-line px-3 py-2"></label>
            <div class="flex items-end"><button type="submit" @disabled($classes->isEmpty()) class="w-full rounded-full bg-orange px-5 py-2.5 text-sm font-bold text-white hover:bg-orange/90 disabled:cursor-not-allowed disabled:opacity-50">Simpan foto</button></div>
        </form>

        <form method="GET" action="{{ route('guru.gallery') }}" class="flex flex-wrap items-end gap-3 rounded-2xl border border-line bg-white p-4">
            <label class="min-w-52 space-y-1 text-sm font-semibold"><span>Filter kelas</span><select name="class_id" class="w-full rounded-xl border border-line px-3 py-2"><option value="">Semua kelas</option>@foreach($classes as $class)<option value="{{ $class->id }}" @selected((int) ($filters['class_id'] ?? 0) === $class->id)>{{ $class->name }}</option>@endforeach</select></label>
            <label class="space-y-1 text-sm font-semibold"><span>Tanggal</span><input type="date" name="photo_date" value="{{ $filters['photo_date'] ?? '' }}" class="rounded-xl border border-line px-3 py-2"></label>
            <button class="rounded-full bg-royal px-5 py-2.5 text-sm font-bold text-white">Terapkan</button>
        </form>

        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($photos as $photo)
                <article class="overflow-hidden rounded-2xl border border-line bg-white shadow-sm">
                    <img src="{{ route('guru.gallery.file', $photo) }}" alt="{{ $photo->caption ?: 'Dokumentasi kegiatan kelas' }}" class="aspect-[4/3] w-full object-cover" loading="lazy">
                    <div class="space-y-1 p-4">
                        <h2 class="font-display font-bold text-navy">{{ $photo->class->name }}</h2>
                        <p class="text-xs text-muted">{{ $photo->photo_date->format('d M Y') }}@if($photo->session_label) · {{ $photo->session_label }}@endif</p>
                        @if($photo->caption)<p class="text-sm text-ink">{{ $photo->caption }}</p>@endif
                    </div>
                </article>
            @empty
                <p class="rounded-2xl border border-dashed border-line bg-white p-8 text-sm text-muted sm:col-span-2 lg:col-span-3">Belum ada dokumentasi untuk kelas yang Anda ampu.</p>
            @endforelse
        </section>
        @if($photos->hasPages())<div>{{ $photos->links() }}</div>@endif
    </div>
</x-app-layout>
