<x-app-layout title="Inbox Bantuan — Children's Classes">
    <div class="mx-auto max-w-7xl space-y-5">
        <header>
            <p class="text-sm font-semibold text-muted">Administrator workspace / Support</p>
            <h1 class="mt-1 font-display text-3xl font-bold text-navy">Inbox Bantuan</h1>
            <p class="mt-1 text-sm text-muted">Percakapan dari pengunjung publik dan Guru.</p>
        </header>
        @if(session('success'))
            <x-ui-alert variant="ok">{{ session('success') }}</x-ui-alert>
        @endif
        @if($errors->any())
            <x-ui-alert variant="danger" title="Pesan belum dapat dikirim">{{ $errors->first() }}</x-ui-alert>
        @endif

        <div class="grid gap-5 lg:grid-cols-[minmax(16rem,0.8fr)_minmax(0,1.6fr)]">
            <section class="overflow-hidden rounded-2xl border border-line bg-white shadow-sm">
                <h2 class="border-b border-line px-4 py-3 font-display font-bold text-navy">Percakapan</h2>
                <div class="max-h-[70vh] overflow-y-auto">
                    @forelse($conversations as $item)
                        <div @class(['border-b border-line px-4 py-3 transition hover:bg-cream', 'bg-primary-soft/60' => $selectedConversation?->id === $item->id])>
                            <a href="{{ route('admin.support', ['conversation_id' => $item->id]) }}" class="block">
                                <span class="flex items-center justify-between gap-2">
                                    <span class="truncate font-bold text-navy">{{ $item->source === 'guru' ? ($item->user?->name ?? 'Guru') : ($item->guest_name ?: 'Pengunjung') }}</span>
                                    <span class="rounded-full bg-sun px-2 py-0.5 text-[10px] font-bold text-navy">{{ $item->source === 'guru' ? 'Guru' : 'Publik' }}</span>
                                </span>
                                <span class="mt-1 block text-xs text-muted">{{ $item->last_message_at?->format('d M Y H:i') }}</span>
                            </a>
                            <form method="POST" action="{{ route('admin.support.destroy', $item) }}" class="mt-2" onsubmit="return confirm('Hapus percakapan dan seluruh pesannya?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-bold text-danger hover:underline">Hapus percakapan</button>
                            </form>
                        </div>
                    @empty
                        <p class="p-4 text-sm text-muted">Belum ada percakapan bantuan.</p>
                    @endforelse
                </div>
                @if($conversations->hasPages())
                    <div class="border-t border-line px-4 py-3">{{ $conversations->links() }}</div>
                @endif
            </section>

            <section class="overflow-hidden rounded-2xl border border-line bg-white shadow-sm">
                @if($selectedConversation)
                    <header class="border-b border-line px-5 py-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h2 class="font-display text-lg font-bold text-navy">{{ $selectedConversation->source === 'guru' ? ($selectedConversation->user?->name ?? 'Guru') : ($selectedConversation->guest_name ?: 'Pengunjung publik') }}</h2>
                                <p class="text-xs text-muted">Sumber: {{ $selectedConversation->source === 'guru' ? 'Guru' : 'Pengunjung publik' }} · Status: {{ $selectedConversation->status }}</p>
                            </div>
                            <form method="POST" action="{{ route('admin.support.destroy', $selectedConversation) }}" onsubmit="return confirm('Hapus percakapan dan seluruh pesannya?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-full border border-danger/30 px-3 py-2 text-xs font-bold text-danger hover:bg-danger-soft">Hapus</button>
                            </form>
                        </div>
                    </header>
                    <div class="max-h-[52vh] space-y-3 overflow-y-auto p-5" aria-live="polite">
                        @foreach($selectedConversation->messages as $message)
                            <article class="max-w-[90%] rounded-2xl px-4 py-3 {{ $message->sender_type === 'admin' ? 'ml-auto bg-primary-soft text-navy' : 'bg-cream text-ink' }}">
                                <p class="text-xs font-bold">{{ $message->sender_type === 'admin' ? 'Admin' : ($message->senderUser?->name ?? $selectedConversation->guest_name ?? 'Pengunjung') }}</p>
                                <p class="mt-1 whitespace-pre-wrap break-words text-sm">{{ $message->body }}</p>
                                <time class="mt-2 block text-right text-[10px] text-muted">{{ $message->created_at->format('d M Y H:i') }}</time>
                            </article>
                        @endforeach
                    </div>
                    <form method="POST" action="{{ route('admin.support.messages.store', $selectedConversation) }}" class="space-y-3 border-t border-line p-5">
                        @csrf
                        <label for="admin-reply" class="block text-xs font-bold text-navy">Balas percakapan</label>
                        <textarea id="admin-reply" name="body" rows="3" maxlength="5000" required class="w-full rounded-xl border border-line px-3 py-2 text-sm" placeholder="Tulis balasan untuk pengguna"></textarea>
                        <button type="submit" class="rounded-full bg-royal px-5 py-2.5 text-sm font-bold text-white hover:bg-royal/90">Kirim balasan</button>
                    </form>
                @else
                    <div class="flex min-h-80 items-center justify-center p-6 text-center text-sm text-muted">Pilih percakapan di samping untuk membaca dan membalas pesan.</div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
