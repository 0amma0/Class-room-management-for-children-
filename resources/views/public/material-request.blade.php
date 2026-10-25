<x-guest-layout title="Status Permintaan Materi — Children's Classes">
    <main class="mx-auto max-w-2xl space-y-5 px-4 py-12 sm:px-6">
        <a href="{{ route('learning-materials.index') }}" class="text-sm font-bold text-royal hover:underline">← Kembali ke materi</a>
        <section class="space-y-4 rounded-3xl border border-line bg-white p-6 shadow-sm sm:p-8">
            <div>
                <p class="text-sm font-semibold text-muted">Status permintaan akses</p>
                <h1 class="mt-1 font-display text-2xl font-bold text-navy">{{ $downloadRequest->learningMaterial->title }}</h1>
                <p class="mt-2 text-sm text-muted">Pemohon: {{ $downloadRequest->requester_name }}</p>
            </div>
            @if(session('success'))
                <x-ui-alert variant="ok">{{ session('success') }}</x-ui-alert>
            @endif
            @if($downloadRequest->status === 'pending')
                <x-ui-alert variant="warning">Permintaan izin {{ $downloadRequest->requested_action === 'read' ? 'membaca' : 'mengunduh' }} sedang menunggu persetujuan admin. Simpan tautan halaman ini untuk mengecek kembali.</x-ui-alert>
            @elseif($downloadRequest->status === 'approved')
                <x-ui-alert variant="ok">Permintaan disetujui. Anda dapat mengakses materi melalui tombol berikut.</x-ui-alert>
                @if($downloadRequest->requested_action === 'read')
                    <a href="{{ route('learning-materials.requests.read', request()->route('token')) }}" target="_blank" rel="noopener" class="inline-flex rounded-full bg-orange px-5 py-3 text-sm font-bold text-white hover:bg-orange/90">Baca materi</a>
                @else
                    <a href="{{ route('learning-materials.requests.download', request()->route('token')) }}" class="inline-flex rounded-full bg-orange px-5 py-3 text-sm font-bold text-white hover:bg-orange/90">Unduh materi</a>
                @endif
            @else
                <x-ui-alert variant="danger">Permintaan tidak disetujui oleh admin.</x-ui-alert>
                @if($downloadRequest->review_note)
                    <p class="text-sm text-muted">Catatan admin: {{ $downloadRequest->review_note }}</p>
                @endif
            @endif
        </section>
    </main>
</x-guest-layout>
