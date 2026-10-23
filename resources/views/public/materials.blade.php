<x-guest-layout title="Draft Lagu & Materi Pelajaran — Children's Classes">
    <main class="mx-auto max-w-6xl space-y-6 px-4 py-10 sm:px-6">
        <header class="space-y-2">
            <p class="text-sm font-semibold text-royal">Materi untuk kelas anak</p>
            <h1 class="font-display text-3xl font-bold text-navy">Draft Lagu & Materi Pelajaran</h1>
            <p class="max-w-3xl text-sm leading-relaxed text-muted">
                Ajukan izin unduh untuk lagu, dokumen, dan gambar. Admin akan meninjau permintaan Anda terlebih dahulu.
                Setelah mengajukan, simpan tautan status untuk membuka materi jika disetujui.
            </p>
        </header>

        @if(session('success'))
            <x-ui-alert variant="ok">{{ session('success') }}</x-ui-alert>
        @endif
        @if($errors->any())
            <x-ui-alert variant="danger" title="Permintaan belum dapat dikirim">
                <ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </x-ui-alert>
        @endif

        <section class="grid gap-4 md:grid-cols-2">
            @forelse($materials as $material)
                <article class="space-y-4 rounded-3xl border border-line bg-white p-5 shadow-sm sm:p-6">
                    <div>
                        <span class="rounded-full bg-primary-soft px-3 py-1 text-xs font-bold uppercase text-royal">{{ $material->type }}</span>
                        <h2 class="mt-3 font-display text-xl font-bold text-navy">{{ $material->title }}</h2>
                        @if($material->description)
                            <p class="mt-2 text-sm leading-relaxed text-muted">{{ $material->description }}</p>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('learning-materials.requests.store', $material) }}" class="grid gap-3 border-t border-line pt-4">
                        @csrf
                        <label class="space-y-1 text-xs font-semibold text-navy"><span>Nama</span><input name="requester_name" value="{{ old('requester_name') }}" required maxlength="255" autocomplete="name" class="w-full rounded-xl border border-line px-3 py-2 text-sm font-normal"></label>
                        <label class="space-y-1 text-xs font-semibold text-navy"><span>Email untuk identifikasi permintaan</span><input type="email" name="requester_email" value="{{ old('requester_email') }}" required maxlength="255" autocomplete="email" class="w-full rounded-xl border border-line px-3 py-2 text-sm font-normal"></label>
                        <button class="justify-self-start rounded-full bg-orange px-5 py-2.5 text-sm font-bold text-white hover:bg-orange/90">Ajukan izin unduh</button>
                    </form>
                </article>
            @empty
                <p class="rounded-2xl border border-line bg-white p-6 text-sm text-muted">Belum ada materi yang tersedia.</p>
            @endforelse
        </section>

        <a href="{{ route('home') }}" class="inline-flex text-sm font-bold text-royal hover:underline">Kembali ke halaman utama</a>
    </main>
</x-guest-layout>
