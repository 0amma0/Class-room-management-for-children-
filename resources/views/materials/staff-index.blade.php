<x-app-layout title="Draft Lagu & Materi — Children's Classes">
    <div class="mx-auto max-w-7xl space-y-6">
        <header>
            <p class="text-sm font-semibold text-muted">{{ $isAdmin ? 'Administrator workspace' : 'Teacher workspace' }} / Materi</p>
            <h1 class="mt-1 font-display text-3xl font-bold text-navy">Draft Lagu & Materi Pelajaran</h1>
            <p class="mt-1 text-sm text-muted">Dokumen, lagu, gambar, dan materi yang tersedia untuk pembelajaran.</p>
        </header>

        @if(session('success'))
            <x-ui-alert variant="ok">{{ session('success') }}</x-ui-alert>
        @endif
        @if($errors->any())
            <x-ui-alert variant="danger" title="Tindakan belum berhasil">
                <ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </x-ui-alert>
        @endif

        @if($isAdmin)
            <details class="rounded-2xl border border-line bg-white p-5 shadow-sm">
                <summary class="cursor-pointer font-bold text-navy">Tambah materi lagu, gambar, atau dokumen</summary>
                <form method="POST" action="{{ route('admin.materials.store') }}" enctype="multipart/form-data" class="mt-4 grid gap-4 sm:grid-cols-2">
                    @csrf
                    <label class="space-y-1 text-xs font-semibold text-navy"><span>Nama materi</span><input name="title" value="{{ old('title') }}" required maxlength="255" class="w-full rounded-xl border border-line px-3 py-2 text-sm font-normal"></label>
                    <label class="space-y-1 text-xs font-semibold text-navy"><span>Jenis</span><select name="type" required class="w-full rounded-xl border border-line px-3 py-2 text-sm font-normal"><option value="lesson">Lesson</option><option value="song">Lagu</option><option value="image">Gambar</option><option value="document">Dokumen</option></select></label>
                    <label class="space-y-1 text-xs font-semibold text-navy sm:col-span-2"><span>Keterangan</span><textarea name="description" rows="2" maxlength="2000" class="w-full rounded-xl border border-line px-3 py-2 text-sm font-normal">{{ old('description') }}</textarea></label>
                    <label class="space-y-1 text-xs font-semibold text-navy"><span>Unggah file</span><input type="file" name="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp,.mp3,.m4a,.wav" class="w-full rounded-xl border border-line px-3 py-2 text-sm font-normal"><span class="block text-xs font-normal text-muted">Maksimal 15 MB.</span></label>
                    <label class="space-y-1 text-xs font-semibold text-navy"><span>Atau tautan HTTPS/HTTP</span><input type="url" name="external_url" value="{{ old('external_url') }}" maxlength="2000" class="w-full rounded-xl border border-line px-3 py-2 text-sm font-normal"></label>
                    <button class="justify-self-start rounded-full bg-orange px-5 py-2.5 text-sm font-bold text-white hover:bg-orange/90">Simpan materi</button>
                </form>
            </details>
        @endif

        <section class="grid gap-4 md:grid-cols-2">
            @forelse($materials as $material)
                <article class="flex flex-col justify-between gap-4 rounded-3xl border border-line bg-white p-5 shadow-sm sm:p-6">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-primary-soft px-3 py-1 text-xs font-bold uppercase text-royal">{{ $material->type }}</span>
                            @if($material->is_system)<span class="rounded-full bg-sun px-3 py-1 text-xs font-bold text-navy">Materi utama</span>@endif
                        </div>
                        <h2 class="mt-3 font-display text-xl font-bold text-navy">{{ $material->title }}</h2>
                        @if($material->description)<p class="mt-2 text-sm leading-relaxed text-muted">{{ $material->description }}</p>@endif
                    </div>
                    <a href="{{ route('learning-materials.staff-download', $material) }}" class="inline-flex w-fit rounded-full bg-orange px-5 py-2.5 text-sm font-bold text-white hover:bg-orange/90">
                        {{ filled($material->external_url) ? 'Buka materi' : 'Unduh materi' }}
                    </a>
                </article>
            @empty
                <p class="rounded-2xl border border-line bg-white p-6 text-sm text-muted">Belum ada materi yang tersedia.</p>
            @endforelse
        </section>

        @if($isAdmin)
            <section id="download-requests" class="space-y-4">
                <div>
                    <h2 class="font-display text-2xl font-bold text-navy">Permintaan izin unduh</h2>
                    <p class="mt-1 text-sm text-muted">Tinjau permintaan publik sebelum materi dapat diunduh.</p>
                </div>
                <div class="overflow-hidden rounded-3xl border border-line bg-white shadow-sm">
                    @if($requests->isEmpty())
                        <p class="p-6 text-sm text-muted">Belum ada permintaan unduh.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[850px] text-left text-sm">
                                <thead class="bg-cream text-xs font-bold uppercase tracking-wide text-muted"><tr><th class="px-5 py-4">Pemohon</th><th class="px-5 py-4">Materi</th><th class="px-5 py-4">Tanggal</th><th class="px-5 py-4">Status</th><th class="px-5 py-4">Tindakan</th></tr></thead>
                                <tbody class="divide-y divide-line">
                                    @foreach($requests as $downloadRequest)
                                        <tr id="request-{{ $downloadRequest->id }}">
                                            <td class="px-5 py-4">{{ $downloadRequest->requester_name }}<span class="block text-xs text-muted">{{ $downloadRequest->requester_email }}</span></td>
                                            <td class="px-5 py-4">{{ $downloadRequest->learningMaterial->title }}</td>
                                            <td class="px-5 py-4">{{ $downloadRequest->created_at->format('d M Y H:i') }}</td>
                                            <td class="px-5 py-4">{{ ucfirst($downloadRequest->status) }}</td>
                                            <td class="px-5 py-4">
                                                @if($downloadRequest->status === 'pending')
                                                    <div class="flex flex-wrap gap-2">
                                                        <form method="POST" action="{{ route('admin.materials.requests.decide', $downloadRequest) }}">@csrf<input type="hidden" name="decision" value="approved"><button class="rounded-full bg-ok px-3 py-2 text-xs font-bold text-white">Setujui</button></form>
                                                        <form method="POST" action="{{ route('admin.materials.requests.decide', $downloadRequest) }}">@csrf<input type="hidden" name="decision" value="rejected"><button class="rounded-full border border-danger/30 px-3 py-2 text-xs font-bold text-danger">Tolak</button></form>
                                                    </div>
                                                @else
                                                    <span class="mr-2 text-xs text-muted">{{ $downloadRequest->review_note ?: 'Sudah diproses' }}</span>
                                                @endif
                                                <form method="POST" action="{{ route('admin.materials.requests.destroy', $downloadRequest) }}" class="mt-2" onsubmit="return confirm('Hapus permintaan unduh ini?')">@csrf @method('DELETE')<button class="rounded-full border border-danger/30 px-3 py-2 text-xs font-bold text-danger">Delete</button></form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="border-t border-line px-5 py-4">{{ $requests->links() }}</div>
                    @endif
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
