<x-app-layout title="Pengaturan Kontak — Children's Classes">
    <div class="mx-auto max-w-4xl space-y-6">
        <header>
            <p class="text-sm font-semibold text-muted">Admin / Pengaturan</p>
            <h1 class="mt-1 font-display text-3xl font-bold text-navy">Pilihan kontak publik</h1>
            <p class="mt-1 text-sm text-muted">Perubahan langsung ditampilkan pada widget kontak di beranda. Tautan nomor menggunakan aplikasi telepon perangkat.</p>
        </header>
        @if(session('success'))<x-ui-alert variant="ok">{{ session('success') }}</x-ui-alert>@endif
        @if($errors->any())<x-ui-alert variant="danger" title="Periksa kembali data kontak">{{ $errors->first() }}</x-ui-alert>@endif

        <form method="POST" action="{{ route('admin.contacts.update') }}" class="space-y-5">
            @csrf
            @method('PUT')
            @foreach($contacts as $contact)
                <section class="space-y-4 rounded-3xl border border-line bg-white p-5 shadow-sm sm:p-7">
                    <div class="flex items-center gap-3">
                        <x-ui-avatar :type="$contact->avatar_type" size="md" :alt="$contact->name" />
                        <h2 class="font-display text-xl font-bold text-navy">{{ $contact->name }}</h2>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <label class="space-y-1 text-sm font-semibold"><span>Nama</span><input name="contacts[{{ $contact->slug }}][name]" value="{{ old('contacts.'.$contact->slug.'.name', $contact->name) }}" maxlength="100" required class="w-full rounded-xl border border-line px-3 py-2"></label>
                        <label class="space-y-1 text-sm font-semibold"><span>Nomor telepon</span><input name="contacts[{{ $contact->slug }}][phone]" value="{{ old('contacts.'.$contact->slug.'.phone', $contact->phone) }}" maxlength="30" required class="w-full rounded-xl border border-line px-3 py-2"></label>
                        <label class="space-y-1 text-sm font-semibold"><span>Avatar</span><select name="contacts[{{ $contact->slug }}][avatar_type]" class="w-full rounded-xl border border-line px-3 py-2">@foreach(['afro', 'bangs', 'beret', 'braids', 'bun', 'pigtails', 'short'] as $type)<option value="{{ $type }}" @selected(old('contacts.'.$contact->slug.'.avatar_type', $contact->avatar_type) === $type)>{{ ucfirst($type) }}</option>@endforeach</select></label>
                    </div>
                </section>
            @endforeach
            <button type="submit" class="rounded-full bg-orange px-6 py-3 text-sm font-bold text-white hover:bg-orange/90">Simpan kontak</button>
        </form>
    </div>
</x-app-layout>
