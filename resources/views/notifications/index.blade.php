<x-app-layout title="Notifikasi — Children's Classes">
    <div class="mx-auto max-w-5xl space-y-6">
        <header>
            <p class="text-sm font-semibold text-muted">Account / Notifications</p>
            <h1 class="mt-1 font-display text-3xl font-bold text-navy">Notifikasi</h1>
            <p class="mt-1 text-sm text-muted">Pesan bantuan, permintaan unduh, dan refleksi pembelajaran terbaru.</p>
        </header>

        @if(session('success'))<x-ui-alert variant="ok">{{ session('success') }}</x-ui-alert>@endif

        <section class="overflow-hidden rounded-3xl border border-line bg-white shadow-sm">
            @forelse($notificationGroups as $date => $group)
                <div>
                    <h2 class="sticky top-0 border-b border-line bg-cream px-5 py-3 font-display text-sm font-bold text-navy">{{ $date }}</h2>
                    @foreach($group as $notification)
                        <div class="flex items-start justify-between gap-4 border-b border-line p-5 last:border-b-0 {{ $notification->read_at ? '' : 'bg-primary-soft/40' }}">
                            <a href="{{ route('notifications.open', $notification) }}" class="min-w-0 flex-1 rounded-lg transition hover:text-royal focus:outline-none focus:ring-2 focus:ring-royal">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="font-display font-bold text-navy">{{ $notification->title }}</h3>
                                        <p class="mt-1 text-sm text-muted">{{ $notification->message }}</p>
                                        <p class="mt-2 text-xs text-muted">{{ $notification->created_at->format('H:i') }}</p>
                                    </div>
                                    @if(!$notification->read_at)<span class="shrink-0 rounded-full bg-orange px-3 py-1 text-[10px] font-bold text-white">Baru</span>@endif
                                </div>
                            </a>
                            <form method="POST" action="{{ route('notifications.destroy', $notification) }}" onsubmit="return confirm('Hapus notifikasi ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="shrink-0 rounded-lg border border-danger/30 px-3 py-2 text-xs font-bold text-danger hover:bg-danger-soft">Hapus</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @empty
                <p class="p-6 text-sm text-muted">Belum ada notifikasi.</p>
            @endforelse
            @if($notifications->hasPages())
                <div class="border-t border-line px-5 py-4">{{ $notifications->links() }}</div>
            @endif
        </section>
    </div>
</x-app-layout>
