@php
    $assignedClassIds = isset($teacher) ? $teacher->classes->modelKeys() : [];
    $primaryClassId = isset($teacher)
        ? $teacher->classes->first(fn ($class) => (bool) $class->pivot->is_primary)?->id
        : null;
    $isEditing = isset($teacher);
@endphp

<form method="POST" action="{{ $isEditing ? route('admin.teachers.update', $teacher->id) : route('admin.teachers.store') }}" class="grid gap-4 sm:grid-cols-2">
    @csrf
    @if($isEditing)
        @method('PUT')
    @endif

    <label class="space-y-1 text-sm font-semibold text-ink">
        <span>Nama lengkap</span>
        <input name="full_name" value="{{ old('full_name', $teacher->full_name ?? '') }}" required maxlength="255" class="w-full rounded-xl border border-line px-3 py-2 font-normal">
        @error('full_name') <span class="block text-xs text-danger">{{ $message }}</span> @enderror
    </label>
    <label class="space-y-1 text-sm font-semibold text-ink">
        <span>Nama panggilan</span>
        <input name="nickname" value="{{ old('nickname', $teacher->nickname ?? '') }}" maxlength="255" class="w-full rounded-xl border border-line px-3 py-2 font-normal">
    </label>
    <label class="space-y-1 text-sm font-semibold text-ink">
        <span>Nomor telepon</span>
        <input name="phone" value="{{ old('phone', $teacher->phone ?? '') }}" maxlength="255" class="w-full rounded-xl border border-line px-3 py-2 font-normal">
    </label>
    <label class="space-y-1 text-sm font-semibold text-ink">
        <span>Email login</span>
        <input type="email" name="email" value="{{ old('email', $teacher->user->email ?? '') }}" required maxlength="255" class="w-full rounded-xl border border-line px-3 py-2 font-normal">
        @error('email') <span class="block text-xs text-danger">{{ $message }}</span> @enderror
    </label>
    <label class="space-y-1 text-sm font-semibold text-ink">
        <span>Username</span>
        <input name="username" value="{{ old('username', $teacher->user->username ?? '') }}" required maxlength="255" class="w-full rounded-xl border border-line px-3 py-2 font-normal">
        @error('username') <span class="block text-xs text-danger">{{ $message }}</span> @enderror
    </label>
    <label class="space-y-1 text-sm font-semibold text-ink sm:col-span-2">
        <span>Alamat kontak (opsional)</span>
        <textarea name="address" rows="2" maxlength="5000" class="w-full rounded-xl border border-line px-3 py-2 font-normal">{{ old('address', $teacher->address ?? '') }}</textarea>
    </label>

    <fieldset class="space-y-3 rounded-xl border border-line p-4 sm:col-span-2">
        <legend class="px-1 text-sm font-bold text-ink">Penugasan kelas dan guru utama</legend>
        @error('classes') <p class="text-xs text-danger">{{ $message }}</p> @enderror
        @error('primary_class_id') <p class="text-xs text-danger">{{ $message }}</p> @enderror
        @if($classes->isEmpty())
            <p class="text-sm text-muted">Buat kelas aktif terlebih dahulu.</p>
        @else
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach($classes as $class)
                    @php
                        $selectedClasses = old('classes', $assignedClassIds);
                        $selectedPrimary = old('primary_class_id', $primaryClassId);
                    @endphp
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg bg-cream/70 p-3 text-sm">
                        <label class="flex items-center gap-2 font-semibold text-ink">
                            <input type="checkbox" name="classes[]" value="{{ $class->id }}" @checked(in_array($class->id, array_map('intval', $selectedClasses), true)) class="rounded border-line text-orange focus:ring-orange">
                            {{ $class->name }} <span class="text-xs font-normal text-muted">{{ $class->code }}</span>
                        </label>
                        <label class="flex items-center gap-2 text-xs text-muted">
                            <input type="radio" name="primary_class_id" value="{{ $class->id }}" @checked((int) $selectedPrimary === $class->id) required class="border-line text-orange focus:ring-orange">
                            Guru utama
                        </label>
                    </div>
                @endforeach
            </div>
        @endif
    </fieldset>

    <button type="submit" class="justify-self-start rounded-full bg-orange px-5 py-2.5 text-sm font-bold text-white hover:bg-orange/90 sm:col-span-2">
        {{ $isEditing ? 'Simpan perubahan' : 'Buat akun guru' }}
    </button>
</form>
