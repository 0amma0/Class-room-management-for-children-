<x-guest-layout title="Sign In — Children's Classes">
    <div class="min-h-screen bg-[#1F2F8A] relative overflow-hidden flex flex-col justify-center items-center px-6 py-12 selection:bg-[#FFD86B] selection:text-[#1D2B33]">
        <!-- Decorative Floating Avatars matching 03-login.png -->
        <div class="hidden lg:block absolute inset-0 pointer-events-none max-w-5xl mx-auto">
            <!-- Top Center -->
            <div class="absolute top-12 left-1/2 -translate-x-1/2">
                <x-ui-avatar type="beret" size="lg" class="shadow-lg animate-bounce" style="animation-duration: 6s;" />
            </div>
            <!-- Top Left -->
            <div class="absolute top-28 left-20">
                <x-ui-avatar type="bangs" size="lg" class="shadow-lg animate-bounce" style="animation-duration: 5s;" />
            </div>
            <!-- Top Right -->
            <div class="absolute top-28 right-20">
                <x-ui-avatar type="beret" size="lg" class="shadow-lg animate-bounce" style="animation-duration: 5.5s;" />
            </div>
            <!-- Mid Left -->
            <div class="absolute top-1/2 -translate-y-12 left-10">
                <x-ui-avatar type="short" size="lg" class="shadow-lg animate-bounce" style="animation-duration: 6.5s;" />
            </div>
            <!-- Mid Right -->
            <div class="absolute top-1/2 -translate-y-12 right-10">
                <x-ui-avatar type="short" size="lg" class="shadow-lg animate-bounce" style="animation-duration: 4.8s;" />
            </div>
            <!-- Bottom Left -->
            <div class="absolute bottom-32 left-24 z-10">
                <x-ui-avatar type="afro" size="lg" class="shadow-lg animate-bounce" style="animation-duration: 5.2s;" />
            </div>
            <!-- Bottom Right -->
            <div class="absolute bottom-32 right-24 z-10">
                <x-ui-avatar type="bun" size="lg" class="shadow-lg animate-bounce" style="animation-duration: 6.2s;" />
            </div>
        </div>

        <!-- Center Card -->
        <div class="w-full max-w-md bg-white rounded-[32px] p-8 sm:p-10 shadow-2xl relative z-20 border border-white/20">
            <!-- Logo Header -->
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-full bg-[#FBA93A] text-white flex items-center justify-center font-display font-bold text-lg shadow-sm">
                    K
                </div>
                <span class="font-display font-bold text-lg text-[#1D2B33]">Children's Classes</span>
            </div>

            <!-- Title & Subtitle -->
            <div class="mb-6">
                <h1 class="font-display font-bold text-3xl text-[#1F2F8A]">Sign In</h1>
                <p class="text-xs sm:text-sm text-[#65737D] mt-1 font-sans">
                    Use the account provided by your admin.
                </p>
            </div>

            @if($errors->has('login'))
                <div class="mb-5">
                    <x-ui-alert variant="danger">
                        {{ $errors->first('login') }}
                    </x-ui-alert>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="login" class="block font-sans font-semibold text-xs sm:text-sm text-[#1D2B33] mb-1.5">
                        Username or Email
                    </label>
                    <input
                        id="login"
                        name="login"
                        type="text"
                        value="{{ old('login') }}"
                        placeholder="guru.a"
                        required
                        autofocus
                        class="w-full rounded-xl border border-[#EFE4CC] bg-[#FFF8EA] px-4 py-3 text-sm text-[#1D2B33] placeholder-[#65737D]/60 focus:border-[#FBA93A] focus:outline-none focus:ring-2 focus:ring-[#FBA93A]/20 transition-all font-sans"
                    >
                </div>

                <div>
                    <label for="password" class="block font-sans font-semibold text-xs sm:text-sm text-[#1D2B33] mb-1.5">
                        Password
                    </label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        placeholder="••••••••"
                        required
                        class="w-full rounded-xl border border-[#EFE4CC] bg-[#FFF8EA] px-4 py-3 text-sm text-[#1D2B33] placeholder-[#65737D]/60 focus:border-[#FBA93A] focus:outline-none focus:ring-2 focus:ring-[#FBA93A]/20 transition-all font-sans"
                    >
                    @error('password')
                        <p class="text-xs text-[#D64545] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-3">
                    <button
                        type="submit"
                        class="w-full font-display font-bold text-base bg-[#FBA93A] hover:bg-[#e89726] text-white rounded-full py-3.5 shadow-md shadow-[#FBA93A]/30 transition-all cursor-pointer hover:scale-101 active:scale-99"
                    >
                        Sign In
                    </button>
                </div>
            </form>

            <div class="mt-8 text-center">
                <p class="text-xs text-[#65737D] font-sans">
                    Forgot your password? Contact your admin.
                </p>
                <div class="mt-4 pt-4 border-t border-[#EFE4CC]/60">
                    <a href="{{ route('home') }}" class="text-xs font-display font-semibold text-[#2E4BC0] hover:underline">
                        ← Back to Homepage
                    </a>
                </div>
            </div>
        </div>

        <!-- Sky-blue Hills & Clouds at bottom matching 03-login.png -->
        <div class="absolute bottom-0 left-0 right-0 w-full overflow-hidden leading-none pointer-events-none z-0">
            <svg class="relative block w-full h-32 md:h-48 text-[#8ED6EC]" viewBox="0 0 1200 240" preserveAspectRatio="none">
                <path d="M0,120 C180,40 380,20 540,110 C700,200 900,60 1200,100 L1200,240 L0,240 Z" fill="currentColor"></path>
            </svg>
        </div>
    </div>
</x-guest-layout>
