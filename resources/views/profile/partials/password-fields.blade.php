<section class="space-y-4 rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6">
    <div>
        <h2 class="font-display text-lg font-bold text-navy">Change password</h2>
        <p class="mt-1 text-sm text-muted">Password disimpan terpisah dari perubahan profil.</p>
    </div>
    <div class="grid gap-4 sm:grid-cols-3">
        <label class="space-y-1.5 text-xs font-bold text-navy"><span>Current password</span><input type="password" name="current_password" autocomplete="current-password" required class="w-full rounded-xl border border-line px-3 py-2.5 text-sm font-normal text-ink">@error('current_password')<span class="block text-xs text-danger">{{ $message }}</span>@enderror</label>
        <label class="space-y-1.5 text-xs font-bold text-navy"><span>New password</span><input type="password" name="password" autocomplete="new-password" required class="w-full rounded-xl border border-line px-3 py-2.5 text-sm font-normal text-ink">@error('password')<span class="block text-xs text-danger">{{ $message }}</span>@enderror</label>
        <label class="space-y-1.5 text-xs font-bold text-navy"><span>Confirm password</span><input type="password" name="password_confirmation" autocomplete="new-password" required class="w-full rounded-xl border border-line px-3 py-2.5 text-sm font-normal text-ink"></label>
    </div>
</section>
