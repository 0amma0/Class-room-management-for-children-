<x-app-layout title="Bantuan Admin — Children's Classes">
    <div class="mx-auto max-w-4xl space-y-5">
        <header>
            <p class="text-sm font-semibold text-muted">Teacher workspace / Support</p>
            <h1 class="mt-1 font-display text-3xl font-bold text-navy">Hubungi Admin</h1>
            <p class="mt-1 text-sm text-muted">Kirim pertanyaan atau minta bantuan. Admin akan membalas melalui percakapan ini.</p>
        </header>
        @if(session('success'))
            <x-ui-alert variant="ok">{{ session('success') }}</x-ui-alert>
        @endif
        @if($errors->any())
            <x-ui-alert variant="danger" title="Pesan belum dapat dikirim">{{ $errors->first() }}</x-ui-alert>
        @endif

        <section class="overflow-hidden rounded-2xl border border-line bg-white shadow-sm">
            @if($conversation)
                <header class="flex items-start justify-between gap-3 border-b border-line px-5 py-4 sm:px-7">
                    <div>
                        <h2 class="font-display text-lg font-bold text-navy">Percakapan dengan Admin</h2>
                        <p class="text-xs text-muted">Menghapus percakapan juga menghapus seluruh pesan di dalamnya.</p>
                    </div>
                    <form method="POST" action="{{ route('guru.support.destroy', $conversation) }}" onsubmit="return confirm('Hapus percakapan dan seluruh pesannya?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-full border border-danger/30 px-3 py-2 text-xs font-bold text-danger hover:bg-danger-soft">Hapus</button>
                    </form>
                </header>
                <div class="max-h-[60vh] space-y-3 overflow-y-auto p-5 sm:p-7" aria-live="polite">
                    @foreach($conversation->messages as $message)
                        <article class="max-w-[90%] rounded-2xl px-4 py-3 {{ $message->sender_type === 'admin' ? 'bg-primary-soft text-navy' : 'ml-auto bg-cream text-ink' }}">
                            <p class="text-xs font-bold">{{ $message->sender_type === 'admin' ? 'Admin' : 'Anda' }}</p>
                            <p class="mt-1 whitespace-pre-wrap break-words text-sm">{{ $message->body }}</p>
                            <time class="mt-2 block text-right text-[10px] text-muted">{{ $message->created_at->format('d M Y H:i') }}</time>
                        </article>
                    @endforeach
                </div>
                <form method="POST" action="{{ route('guru.support.messages.store', $conversation) }}" class="space-y-3 border-t border-line p-5 sm:p-7">
                    @csrf
                    <label for="guru-reply" class="block text-xs font-bold text-navy">Balas percakapan</label>
                    <textarea id="guru-reply" name="body" rows="3" maxlength="5000" required class="w-full rounded-xl border border-line px-3 py-2 text-sm" placeholder="Tulis pesan untuk Admin"></textarea>
                    <button type="submit" class="rounded-full bg-royal px-5 py-2.5 text-sm font-bold text-white hover:bg-royal/90">Kirim pesan</button>
                </form>
            @else
                <form method="POST" action="{{ route('guru.support.store') }}" class="space-y-3 p-5 sm:p-7">
                    @csrf
                    <label for="guru-message" class="block text-xs font-bold text-navy">Pesan untuk Admin</label>
                    <textarea id="guru-message" name="body" rows="5" maxlength="5000" required class="w-full rounded-xl border border-line px-3 py-2 text-sm" placeholder="Ceritakan kendala atau bantuan yang dibutuhkan"></textarea>
                    <button type="submit" class="rounded-full bg-royal px-5 py-2.5 text-sm font-bold text-white hover:bg-royal/90">Kirim ke Admin</button>
                </form>
            @endif
        </section>
    </div>
</x-app-layout>
