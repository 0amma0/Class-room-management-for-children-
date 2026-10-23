<x-guest-layout title="Percakapan Bantuan — Children's Classes">
    <main class="mx-auto w-full max-w-3xl flex-1 px-4 py-10 sm:px-6">
        <a href="{{ route('home') }}" class="text-sm font-bold text-[#2E4BC0]">← Kembali ke beranda</a>
        <section class="mt-4 overflow-hidden rounded-3xl border border-[#EFE4CC] bg-white shadow-lg">
            <header class="bg-[#1F2F8A] px-5 py-5 text-white sm:px-7">
                <h1 class="font-display text-2xl font-bold">Percakapan dengan Admin</h1>
                <p class="mt-1 text-sm text-white/80">Simpan tautan rahasia ini untuk kembali melihat balasan. Siapa pun yang memiliki tautan dapat membaca percakapan.</p>
                <code class="mt-3 block break-all rounded-lg bg-white/10 p-2 text-xs">{{ route('support.public.show', $token) }}</code>
            </header>
            @if(session('success'))
                <p role="status" class="border-b border-[#EFE4CC] bg-[#E1F4E8] px-5 py-3 text-sm font-semibold text-[#2E9E5B]">{{ session('success') }}</p>
            @endif
            @if($errors->any())
                <p role="alert" class="border-b border-[#EFE4CC] bg-[#FCE6E6] px-5 py-3 text-sm text-[#D64545]">{{ $errors->first() }}</p>
            @endif
            <div class="space-y-3 p-5 sm:p-7" aria-live="polite">
                @foreach($conversation->messages as $message)
                    <article class="max-w-[90%] rounded-2xl px-4 py-3 {{ $message->sender_type === 'admin' ? 'ml-auto bg-[#E3EAFB] text-[#1F2F8A]' : 'bg-[#FFF8EA] text-[#1D2B33]' }}">
                        <p class="text-xs font-bold">{{ $message->sender_type === 'admin' ? 'Admin' : ($conversation->guest_name ?: 'Anda') }}</p>
                        <p class="mt-1 whitespace-pre-wrap break-words text-sm">{{ $message->body }}</p>
                        <time class="mt-2 block text-right text-[10px] opacity-70">{{ $message->created_at->format('d M Y H:i') }}</time>
                    </article>
                @endforeach
            </div>
            <form method="POST" action="{{ route('support.public.messages.store', $token) }}" class="space-y-3 border-t border-[#EFE4CC] p-5 sm:p-7">
                @csrf
                <label for="public-reply" class="block text-xs font-bold text-[#1D2B33]">Balas percakapan</label>
                <textarea id="public-reply" name="body" rows="3" maxlength="5000" required class="w-full rounded-xl border border-[#EFE4CC] px-3 py-2 text-sm" placeholder="Tulis balasan Anda"></textarea>
                <button type="submit" class="rounded-full bg-[#FBA93A] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#e89726]">Kirim balasan</button>
            </form>
        </section>
    </main>
</x-guest-layout>
