<x-guest-layout title="Ganti Kata Sandi — Kelas Anak">
    <div class="min-h-screen flex flex-col justify-center items-center px-6 py-12">
        <div class="w-full max-w-md bg-white rounded-[24px] border-2 border-[#EFE4CC] p-8 shadow-lg">
            <div class="mb-6 text-center">
                <span class="inline-block p-3 rounded-full bg-[#FFD86B]/30 text-2xl mb-3">🔑</span>
                <h2 class="font-display font-bold text-2xl text-[#1D2B33]">Ganti Kata Sandi</h2>
                <p class="text-sm text-[#65737D] mt-1 font-sans">
                    Demi keamanan, Anda wajib mengganti kata sandi sementara sebelum melanjutkan.
                </p>
            </div>

            @if(session('status'))
                <div class="mb-5">
                    <x-ui-alert variant="ok">
                        {{ session('status') }}
                    </x-ui-alert>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf

                <div>
                    <x-ui-label for="current_password" required>Kata Sandi Saat Ini</x-ui-label>
                    <x-ui-input
                        id="current_password"
                        name="current_password"
                        type="password"
                        placeholder="••••••••"
                        required
                    />
                    @error('current_password')
                        <p class="text-xs text-[#D64545] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <x-ui-label for="password" required>Kata Sandi Baru</x-ui-label>
                    <x-ui-input
                        id="password"
                        name="password"
                        type="password"
                        placeholder="Minimal 8 karakter huruf & angka"
                        required
                    />
                    @error('password')
                        <p class="text-xs text-[#D64545] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <x-ui-label for="password_confirmation" required>Konfirmasi Kata Sandi Baru</x-ui-label>
                    <x-ui-input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        placeholder="Ulangi kata sandi baru"
                        required
                    />
                </div>

                <div class="pt-3">
                    <x-ui-button type="submit" variant="primary" size="lg" class="w-full">
                        Simpan Kata Sandi
                    </x-ui-button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
