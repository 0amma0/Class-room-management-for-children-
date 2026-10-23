<x-app-layout title="Teacher Profile — Children's Classes">
    @php
        $selectedAvatar = old('avatar_type', $user->avatar_type ?? ($user->avatar_path ? null : 'bangs'));
    @endphp

    <div class="mx-auto max-w-6xl space-y-5">
        <header>
            <p class="text-xs font-medium text-muted">Account / Teacher Profile</p>
            <h1 class="mt-1 font-display text-3xl font-bold text-navy">Profile Settings</h1>
            <p class="mt-1 text-sm text-muted">Update your teacher account and teaching profile.</p>
        </header>

        @if(session('success'))
            <x-ui-alert variant="ok">{{ session('success') }}</x-ui-alert>
        @endif
        @if($errors->any())
            <x-ui-alert variant="danger" title="Please check the information">
                <ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </x-ui-alert>
        @endif

        <section class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-line bg-white p-5 shadow-sm sm:px-7" aria-label="Teacher profile">
            <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase text-muted">Teacher account</p>
                <h2 class="truncate font-display text-xl font-bold text-navy">{{ $user->name }}</h2>
                <p class="truncate text-sm text-muted">{{ $user->username ?? $user->email }}</p>
            </div>
            <div class="flex flex-col items-start gap-1 sm:items-end">
                <span class="rounded-full bg-sun px-4 py-1.5 text-xs font-bold text-navy">Teacher</span>
                <span class="text-xs text-muted">Role assigned by your organization</span>
            </div>
        </section>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')
            <div class="grid gap-4 lg:grid-cols-[1.25fr_0.9fr]">
                <div class="space-y-4">
                    <section class="space-y-5 rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6">
                        <div>
                            <h2 class="font-display text-lg font-bold text-navy">Teacher account</h2>
                            <p class="mt-1 text-sm text-muted">Manage your login name and contact information.</p>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="space-y-1.5 text-xs font-bold text-navy"><span>Account display name</span><input name="name" value="{{ old('name', $user->name) }}" required maxlength="255" class="w-full rounded-xl border border-line px-3 py-2.5 text-sm font-normal text-ink">@error('name')<span class="block text-xs text-danger">{{ $message }}</span>@enderror</label>
                            <label class="space-y-1.5 text-xs font-bold text-navy"><span>Username</span><input value="{{ $user->username ?? '' }}" readonly class="w-full rounded-xl border border-line bg-cream/60 px-3 py-2.5 text-sm font-normal text-muted"></label>
                            <label class="space-y-1.5 text-xs font-bold text-navy"><span>Email address</span><input type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="255" class="w-full rounded-xl border border-line px-3 py-2.5 text-sm font-normal text-ink">@error('email')<span class="block text-xs text-danger">{{ $message }}</span>@enderror</label>
                            <label class="space-y-1.5 text-xs font-bold text-navy"><span>Account phone</span><input name="phone" value="{{ old('phone', $user->phone) }}" maxlength="30" class="w-full rounded-xl border border-line px-3 py-2.5 text-sm font-normal text-ink">@error('phone')<span class="block text-xs text-danger">{{ $message }}</span>@enderror</label>
                        </div>
                    </section>

                    <section class="space-y-4 rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6">
                        <div>
                            <h2 class="font-display text-lg font-bold text-navy">Teaching profile</h2>
                            <p class="mt-1 text-sm text-muted">These details are linked to your assigned teacher record.</p>
                        </div>
                        @if($user->teacher)
                            <div class="grid gap-4 sm:grid-cols-2">
                                <label class="space-y-1.5 text-xs font-bold text-navy"><span>Teacher code</span><input value="{{ $user->teacher->teacher_code ?? '' }}" readonly class="w-full rounded-xl border border-line bg-cream/60 px-3 py-2.5 text-sm font-normal text-muted"></label>
                                <label class="space-y-1.5 text-xs font-bold text-navy"><span>Full name</span><input name="teacher_full_name" value="{{ old('teacher_full_name', $user->teacher->full_name) }}" maxlength="255" class="w-full rounded-xl border border-line px-3 py-2.5 text-sm font-normal text-ink">@error('teacher_full_name')<span class="block text-xs text-danger">{{ $message }}</span>@enderror</label>
                                <label class="space-y-1.5 text-xs font-bold text-navy"><span>Nickname</span><input name="teacher_nickname" value="{{ old('teacher_nickname', $user->teacher->nickname) }}" maxlength="100" class="w-full rounded-xl border border-line px-3 py-2.5 text-sm font-normal text-ink">@error('teacher_nickname')<span class="block text-xs text-danger">{{ $message }}</span>@enderror</label>
                                <label class="space-y-1.5 text-xs font-bold text-navy"><span>Teacher phone</span><input name="teacher_phone" value="{{ old('teacher_phone', $user->teacher->phone) }}" maxlength="30" class="w-full rounded-xl border border-line px-3 py-2.5 text-sm font-normal text-ink">@error('teacher_phone')<span class="block text-xs text-danger">{{ $message }}</span>@enderror</label>
                                <label class="space-y-1.5 text-xs font-bold text-navy sm:col-span-2"><span>Address</span><textarea name="teacher_address" rows="2" maxlength="1000" class="w-full rounded-xl border border-line px-3 py-2.5 text-sm font-normal text-ink">{{ old('teacher_address', $user->teacher->address) }}</textarea>@error('teacher_address')<span class="block text-xs text-danger">{{ $message }}</span>@enderror</label>
                            </div>
                        @else
                            <p class="rounded-xl bg-cream/60 p-3 text-sm text-muted">This account is not linked to a teacher record. Contact an administrator to link it.</p>
                        @endif
                    </section>
                </div>

                @include('profile.partials.avatar-fields', ['selectedAvatar' => $selectedAvatar])
            </div>

            <div class="flex flex-wrap items-center justify-between gap-4">
                <p class="text-xs text-muted">Your teacher details and avatar update together when you save.</p>
                <div class="flex items-center gap-3">
                    <a href="{{ route('guru.dashboard') }}" class="rounded-full border border-line bg-white px-5 py-2.5 text-sm font-bold text-navy hover:bg-cream">Cancel</a>
                    <button type="submit" class="rounded-full bg-royal px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-royal/90">Save Changes</button>
                </div>
            </div>
        </form>

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4 rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6">
            @csrf
            @include('profile.partials.password-fields')
            <div class="flex justify-end">
                <button type="submit" class="rounded-full bg-navy px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-royal">Simpan Kata Sandi</button>
            </div>
        </form>
    </div>
</x-app-layout>
