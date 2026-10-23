<details class="fixed bottom-4 right-4 z-40 w-[min(23rem,calc(100vw-2rem))]">
    <summary class="ml-auto flex w-fit cursor-pointer list-none items-center gap-2 rounded-full bg-[#1F2F8A] px-5 py-3 text-sm font-bold text-white shadow-xl hover:bg-[#2E4BC0]">
        <span aria-hidden="true">✉</span>
        Chat Admin
    </summary>
    <section class="mb-3 overflow-hidden rounded-2xl border border-[#EFE4CC] bg-white shadow-2xl">
        <div class="bg-[#1F2F8A] px-5 py-4 text-white">
            <h2 class="font-display text-lg font-bold">Ada yang bisa kami bantu?</h2>
            <p class="mt-1 text-xs text-white/80">Pilih cara menghubungi kami.</p>
        </div>
        <div class="space-y-2 border-b border-[#EFE4CC] p-4">
            @foreach($publicContacts as $contact)
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contact->phone) }}" class="flex items-center gap-3 rounded-xl border border-[#EFE4CC] p-3 transition hover:bg-[#FFF8EA]">
                    <x-ui-avatar :type="$contact->avatar_type" size="sm" :alt="$contact->name" />
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-bold text-[#1F2F8A]">{{ $contact->name }}</span>
                        <span class="block text-xs text-[#65737D]">{{ $contact->phone }}</span>
                    </span>
                    <span class="text-xs font-bold text-[#2E4BC0]">Telepon</span>
                </a>
            @endforeach
        </div>
        <form method="POST" action="{{ route('support.public.store') }}" class="space-y-3 p-4">
            @csrf
            <label class="block space-y-1 text-xs font-bold text-[#1D2B33]">
                <span>Nama (opsional)</span>
                <input name="name" maxlength="100" class="w-full rounded-xl border border-[#EFE4CC] px-3 py-2 text-sm font-normal">
            </label>
            <label class="block space-y-1 text-xs font-bold text-[#1D2B33]">
                <span>Pesan</span>
                <textarea name="body" rows="3" maxlength="5000" required class="w-full rounded-xl border border-[#EFE4CC] px-3 py-2 text-sm font-normal" placeholder="Tulis pertanyaan atau kendala Anda"></textarea>
            </label>
            @error('body')
                <p class="text-xs text-[#D64545]">{{ $message }}</p>
            @enderror
            <p class="text-xs leading-relaxed text-[#65737D]">Chat Admin: setelah mengirim, simpan tautan percakapan pribadi untuk membaca balasan.</p>
            <button type="submit" class="w-full rounded-full bg-[#FBA93A] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#e89726]">Kirim pesan</button>
        </form>
    </section>
</details>
