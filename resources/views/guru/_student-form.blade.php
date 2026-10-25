@php
    $student = $student ?? null;
    $isEditing = $student !== null;
    $isAdmin = auth()->user()->isAdmin();
    $formAction = $isEditing
        ? route($isAdmin ? 'admin.students.update' : 'guru.students.update', ['studentId' => $student->id])
        : route($isAdmin ? 'admin.students.store' : 'guru.students.store');
@endphp

<form method="POST" action="{{ $formAction }}" class="grid w-full min-w-0 gap-4 sm:grid-cols-2">
    @csrf
    @if($isEditing)
        @method('PUT')
    @endif

    <label class="min-w-0 space-y-1 text-sm font-semibold text-ink">
        <span>Kelas</span>
        <select name="class_id" required class="w-full rounded-xl border border-line bg-white px-3 py-2 font-normal">
            @foreach($classes as $class)
                <option value="{{ $class->id }}" @selected((string) old('class_id', $student?->class_id ?? '') === (string) $class->id)>{{ $class->name }}@if($class->code) ({{ $class->code }})@endif</option>
            @endforeach
        </select>
        @error('class_id') <span class="block text-xs text-danger">{{ $message }}</span> @enderror
    </label>
    <label class="min-w-0 space-y-1 text-sm font-semibold text-ink">
        <span>Kode peserta (otomatis jika kosong)</span>
        <input name="student_code" value="{{ old('student_code', $student?->student_code ?? '') }}" maxlength="255" class="w-full rounded-xl border border-line px-3 py-2 font-normal">
        @error('student_code') <span class="block text-xs text-danger">{{ $message }}</span> @enderror
    </label>
    <label class="min-w-0 space-y-1 text-sm font-semibold text-ink">
        <span>Nama lengkap</span>
        <input name="full_name" value="{{ old('full_name', $student?->full_name ?? '') }}" required maxlength="255" class="w-full rounded-xl border border-line px-3 py-2 font-normal">
        @error('full_name') <span class="block text-xs text-danger">{{ $message }}</span> @enderror
    </label>
    <label class="min-w-0 space-y-1 text-sm font-semibold text-ink">
        <span>Nama panggilan</span>
        <input name="nickname" value="{{ old('nickname', $student?->nickname ?? '') }}" maxlength="255" class="w-full rounded-xl border border-line px-3 py-2 font-normal">
    </label>
    <label class="min-w-0 space-y-1 text-sm font-semibold text-ink">
        <span>Jenis kelamin</span>
        <select name="gender" class="w-full rounded-xl border border-line bg-white px-3 py-2 font-normal">
            <option value="">Tidak disebutkan</option>
            <option value="L" @selected(old('gender', $student->gender ?? '') === 'L')>Laki-laki</option>
            <option value="P" @selected(old('gender', $student->gender ?? '') === 'P')>Perempuan</option>
        </select>
    </label>
    <label class="min-w-0 space-y-1 text-sm font-semibold text-ink">
        <span>Tanggal lahir</span>
        <input type="date" name="birth_date" value="{{ old('birth_date', $student?->birth_date?->format('Y-m-d')) }}" class="w-full rounded-xl border border-line px-3 py-2 font-normal">
        @error('birth_date') <span class="block text-xs text-danger">{{ $message }}</span> @enderror
    </label>
    <label class="min-w-0 space-y-1 text-sm font-semibold text-ink">
        <span>Nama orang tua / wali</span>
        <input name="parent_name" value="{{ old('parent_name', $student?->parent_name ?? '') }}" maxlength="255" class="w-full rounded-xl border border-line px-3 py-2 font-normal">
    </label>
    <label class="min-w-0 space-y-1 text-sm font-semibold text-ink">
        <span>Telepon orang tua / wali</span>
        <input name="parent_phone" value="{{ old('parent_phone', $student?->parent_phone ?? '') }}" maxlength="255" class="w-full rounded-xl border border-line px-3 py-2 font-normal">
    </label>
    <label class="min-w-0 space-y-1 text-sm font-semibold text-ink sm:col-span-2">
        <span>Alamat</span>
        <textarea name="address" rows="2" maxlength="5000" class="w-full rounded-xl border border-line px-3 py-2 font-normal">{{ old('address', $student?->address ?? '') }}</textarea>
    </label>

    @if($isEditing)
        <label class="min-w-0 space-y-1 text-sm font-semibold text-ink">
            <span>Status</span>
            <select name="status" required class="w-full rounded-xl border border-line bg-white px-3 py-2 font-normal">
                <option value="active" @selected(old('status', $student->status) === 'active')>Aktif</option>
                <option value="inactive" @selected(old('status', $student->status) === 'inactive')>Nonaktif</option>
            </select>
        </label>
    @endif

    <button type="submit" class="justify-self-start rounded-full bg-orange px-5 py-2.5 text-sm font-bold text-white hover:bg-orange/90 sm:col-span-2">
        {{ $isEditing ? 'Simpan perubahan' : 'Tambah peserta' }}
    </button>
</form>
