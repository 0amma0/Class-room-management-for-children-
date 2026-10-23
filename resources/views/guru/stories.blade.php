<x-app-layout title="Cerita & Ilustrasi — Children's Classes">
    <div class="mx-auto max-w-6xl space-y-6">
        <header>
            <p class="text-sm font-semibold text-muted">Ruang Guru / Materi presentasi</p>
            <h1 class="mt-1 font-display text-3xl font-bold text-navy">Cerita & Ilustrasi</h1>
            <p class="mt-1 text-sm text-muted">Susun cerita menjadi panel storyboard bergambar secara lokal. Cerita tidak dikirim ke layanan AI eksternal.</p>
        </header>
        @if(session('success'))<x-ui-alert variant="ok">{{ session('success') }}</x-ui-alert>@endif
        @if($errors->any())<x-ui-alert variant="danger" title="Cerita atau ilustrasi belum berhasil diproses"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></x-ui-alert>@endif

        <section class="rounded-3xl border border-line bg-white p-5 shadow-sm sm:p-7">
            <div class="mb-4">
                <h2 class="font-display text-xl font-bold text-navy">{{ $story ? 'Sunting cerita' : 'Buat cerita baru' }}</h2>
                <p class="mt-1 text-sm text-muted">Jangan masukkan nama lengkap, informasi pribadi, atau data sensitif anak.</p>
            </div>
            <form method="POST" action="{{ $story ? route('guru.stories.update', $story) : route('guru.stories.store') }}" class="space-y-4">
                @csrf
                @if($story)@method('PUT')@endif
                <label class="block space-y-1 text-sm font-semibold"><span>Judul cerita</span><input name="title" value="{{ old('title', $story?->title) }}" maxlength="255" required class="w-full rounded-xl border border-line px-3 py-2"></label>
                <label class="block space-y-1 text-sm font-semibold"><span>Teks cerita</span><textarea name="story_text" rows="12" maxlength="20000" required class="w-full rounded-xl border border-line px-3 py-2">{{ old('story_text', $story?->body) }}</textarea></label>
                <button class="rounded-full bg-orange px-6 py-3 text-sm font-bold text-white hover:bg-orange/90">{{ $story ? 'Simpan perubahan' : 'Simpan cerita' }}</button>
            </form>
            @if($story)
                <div class="mt-5 border-t border-line pt-5">
                    <p class="mb-3 text-sm text-muted">Storyboard dibuat dari kalimat-kalimat cerita dengan ilustrasi vektor sederhana. Tidak perlu API key; hasilnya ilustrasi, bukan foto realistis.</p>
                    <form method="POST" action="{{ route('guru.stories.generate', $story) }}" onsubmit="return confirm('Buat storyboard lokal dari cerita ini?')">
                        @csrf
                        <button class="rounded-full bg-royal px-6 py-3 text-sm font-bold text-white hover:bg-royal/90">Buat storyboard</button>
                    </form>
                </div>
            @endif
        </section>

        @if($story?->illustration_path)
            <section class="space-y-4 rounded-3xl border border-line bg-white p-5 shadow-sm sm:p-7">
                <div>
                    <h2 class="font-display text-xl font-bold text-navy">Ilustrasi: {{ $story->title }}</h2>
                    <p class="mt-1 text-sm text-muted">{{ $story->illustration_model }} · dibuat {{ $story->illustration_generated_at?->format('d M Y H:i') }}</p>
                </div>
                <img src="{{ route('guru.stories.image', $story) }}" alt="Ilustrasi cerita {{ $story->title }}" class="max-h-[36rem] w-full rounded-2xl border border-line bg-cream object-contain">
                <a href="{{ route('guru.stories.image.download', $story) }}" class="inline-flex rounded-full bg-royal px-5 py-2.5 text-sm font-bold text-white hover:bg-royal/90">Unduh gambar untuk presentasi / PPT</a>
            </section>
        @endif

        <section class="overflow-hidden rounded-3xl border border-line bg-white shadow-sm">
            <h2 class="border-b border-line px-5 py-4 font-display text-lg font-bold text-navy">Cerita tersimpan</h2>
            @forelse($stories as $savedStory)
                <a href="{{ route('guru.stories', ['story_id' => $savedStory->id]) }}" @class(['block border-b border-line px-5 py-4 transition hover:bg-cream', 'bg-primary-soft/50' => $story?->id === $savedStory->id])>
                    <span class="block font-bold text-navy">{{ $savedStory->title }}</span>
                    <span class="mt-1 block line-clamp-2 text-sm text-muted">{{ $savedStory->body }}</span>
                    <span class="mt-2 block text-xs text-muted">{{ $savedStory->illustration_path ? 'Ilustrasi tersedia' : 'Belum ada ilustrasi' }} · diperbarui {{ $savedStory->updated_at->format('d M Y H:i') }}</span>
                </a>
            @empty
                <p class="p-5 text-sm text-muted">Belum ada naskah yang disimpan.</p>
            @endforelse
            @if($stories->hasPages())<div class="border-t border-line px-5 py-4">{{ $stories->links() }}</div>@endif
        </section>
    </div>
</x-app-layout>
