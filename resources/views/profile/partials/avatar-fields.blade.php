@php
    $avatarTypes = [
        'bangs' => 'Bangs', 'pigtails' => 'Pigtails', 'afro' => 'Afro',
        'short' => 'Short', 'bun' => 'Bun', 'braids' => 'Braids', 'short-blue' => 'Short blue',
    ];
@endphp

<section class="space-y-4 rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6">
    <div class="flex items-center gap-3">
        @if($user->avatar_path)
            <img id="profile-avatar-preview" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($user->avatar_path) }}" alt="Profile avatar" class="size-14 rounded-full border-2 border-orange object-cover">
        @else
            <img id="profile-avatar-preview" src="{{ asset('images/Avatar/'.match($selectedAvatar) { 'pigtails' => 'Pigtails-01.png', 'afro' => 'Afro-01.png', 'short' => 'Short-01.png', 'bun' => 'Bun-01.png', 'braids' => 'Braids-01.png', 'short-blue' => 'Short-02.png', default => 'Bangs-01.png' }) }}" alt="Profile avatar" class="size-14 rounded-full border-2 border-orange object-cover">
        @endif
        <div>
            <h2 class="font-display text-lg font-bold text-navy">Choose your avatar</h2>
            <p class="text-sm text-muted">Pick a friendly face for your profile across the app.</p>
        </div>
    </div>
    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
        @foreach($avatarTypes as $type => $label)
            <label class="group relative flex cursor-pointer flex-col items-center gap-2 rounded-xl border border-line p-2 text-center transition-colors has-[:checked]:border-royal has-[:checked]:bg-royal/5">
                <input type="radio" name="avatar_type" value="{{ $type }}" @checked($selectedAvatar === $type) class="sr-only">
                <x-ui-avatar :type="$type" size="lg" :alt="$label.' avatar'" />
                <span class="text-[10px] font-semibold text-muted group-has-[:checked]:text-royal">{{ $label }}</span>
                <span class="absolute right-1 top-1 hidden size-4 items-center justify-center rounded-full bg-royal text-[9px] font-bold text-white group-has-[:checked]:inline-flex" aria-hidden="true">✓</span>
            </label>
        @endforeach
    </div>
    <label class="block space-y-1.5 border-t border-line pt-4 text-xs font-bold text-navy">
        <span>Or upload your own photo</span>
        <input id="profile-avatar-upload" type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="block w-full rounded-xl border border-line bg-white px-3 py-2 text-sm font-normal text-ink">
        <span class="block text-xs font-normal text-muted">JPG, PNG, or WebP up to 2 MB.</span>
        @error('avatar')<span class="block text-xs text-danger">{{ $message }}</span>@enderror
    </label>
</section>

@pushOnce('scripts')
    <script>
        document.querySelectorAll('input[name="avatar_type"]').forEach((input) => {
            input.addEventListener('change', () => {
                const fileInput = document.getElementById('profile-avatar-upload');
                const preview = document.getElementById('profile-avatar-preview');
                const avatarFiles = {
                    bangs: 'Bangs-01.png', pigtails: 'Pigtails-01.png', afro: 'Afro-01.png',
                    short: 'Short-01.png', bun: 'Bun-01.png', braids: 'Braids-01.png', 'short-blue': 'Short-02.png'
                };
                if (fileInput) {
                    fileInput.value = '';
                }
                if (preview) {
                    preview.src = `${@json(asset('images/Avatar'))}/${avatarFiles[input.value]}`;
                }
            });
        });
        const profileAvatarUpload = document.getElementById('profile-avatar-upload');
        profileAvatarUpload?.addEventListener('change', (event) => {
            const [file] = event.target.files;
            const preview = document.getElementById('profile-avatar-preview');
            if (file && preview) {
                preview.src = URL.createObjectURL(file);
            }
        });
    </script>
@endPushOnce
