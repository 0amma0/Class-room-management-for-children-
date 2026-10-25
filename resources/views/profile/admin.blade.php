<x-app-layout title="Admin Profile — Children's Classes">
    @php
        $selectedAvatar = old('avatar_type', $user->avatar_type ?? ($user->avatar_path ? null : 'bangs'));
    @endphp

    <div class="mx-auto max-w-6xl space-y-5">
        <header>
            <p class="text-xs font-medium text-muted">Account / Admin Profile</p>
            <h1 class="mt-1 font-display text-3xl font-bold text-navy">Profile Settings</h1>
            <p class="mt-1 text-sm text-muted">Update your administrator account details.</p>
        </header>

        @if(session('success'))
            <x-ui-alert variant="ok">{{ session('success') }}</x-ui-alert>
        @endif
        @if($errors->any())
            <x-ui-alert variant="danger" title="Please check the information">
                <ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </x-ui-alert>
        @endif

        <section class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-line bg-white p-5 shadow-sm sm:px-7" aria-label="Admin profile">
            <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase text-muted">Administrator account</p>
                <h2 class="truncate font-display text-xl font-bold text-navy">{{ $user->name }}</h2>
                <p class="truncate text-sm text-muted">{{ $user->username ?? $user->email }}</p>
            </div>
            <div class="flex flex-col items-start gap-1 sm:items-end">
                <span class="rounded-full bg-sun px-4 py-1.5 text-xs font-bold text-navy">Administrator</span>
                <span class="text-xs text-muted">Role assigned by your organization</span>
            </div>
        </section>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')
            <div class="grid gap-4 lg:grid-cols-[1.25fr_0.9fr]">
                <section class="space-y-5 rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6">
                    <div>
                        <h2 class="font-display text-lg font-bold text-navy">Admin account details</h2>
                        <p class="mt-1 text-sm text-muted">Manage the username and contact details used to access your administrator account.</p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="space-y-1.5 text-xs font-bold text-navy"><span>Display name</span><input name="name" value="{{ old('name', $user->name) }}" required maxlength="255" class="w-full rounded-xl border border-line px-3 py-2.5 text-sm font-normal text-ink">@error('name')<span class="block text-xs text-danger">{{ $message }}</span>@enderror</label>
                        <label class="space-y-1.5 text-xs font-bold text-navy"><span>Username</span><input name="username" value="{{ old('username', $user->username) }}" required maxlength="255" autocomplete="username" class="w-full rounded-xl border border-line px-3 py-2.5 text-sm font-normal text-ink">@error('username')<span class="block text-xs text-danger">{{ $message }}</span>@enderror</label>
                        <label class="space-y-1.5 text-xs font-bold text-navy"><span>Email address</span><input type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="255" class="w-full rounded-xl border border-line px-3 py-2.5 text-sm font-normal text-ink">@error('email')<span class="block text-xs text-danger">{{ $message }}</span>@enderror</label>
                        <label class="space-y-1.5 text-xs font-bold text-navy"><span>Phone number (optional)</span><input name="phone" value="{{ old('phone', $user->phone) }}" maxlength="30" class="w-full rounded-xl border border-line px-3 py-2.5 text-sm font-normal text-ink">@error('phone')<span class="block text-xs text-danger">{{ $message }}</span>@enderror</label>
                    </div>
                    <p class="border-t border-line pt-4 text-xs text-muted">Administrator permissions are managed by your organization.</p>
                </section>

                @include('profile.partials.avatar-fields', ['selectedAvatar' => $selectedAvatar])
            </div>

            <div class="flex flex-wrap items-center justify-between gap-4">
                <p class="text-xs text-muted">Your account details and avatar update together when you save.</p>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.dashboard') }}" class="rounded-full border border-line bg-white px-5 py-2.5 text-sm font-bold text-navy hover:bg-cream">Cancel</a>
                    <button type="submit" class="rounded-full bg-royal px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-royal/90">Save Changes</button>
                </div>
            </div>
        </form>

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4 rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6">
            @csrf
            @include('profile.partials.password-fields')
            <p class="text-xs text-muted">Masukkan kata sandi saat ini. Kata sandi Admin baru harus memiliki minimal 12 karakter, huruf besar-kecil, angka, dan simbol.</p>
            <div class="flex justify-end">
                <button type="submit" class="rounded-full bg-navy px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-royal">Simpan Kata Sandi</button>
            </div>
        </form>
    </div>
</x-app-layout>
