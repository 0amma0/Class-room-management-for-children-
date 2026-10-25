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
            @php
                $regions = $classes->map(fn ($class) => $class->environment?->cluster?->region)->filter()->unique('id')->sortBy('name');
                $clusters = $classes->map(fn ($class) => $class->environment?->cluster)->filter()->unique('id')->sortBy('name');
                $environments = $classes->map(fn ($class) => $class->environment)->filter()->unique('id')->sortBy('name');
            @endphp
            <div class="grid gap-3 sm:grid-cols-3" data-class-assignment-filters>
                <label class="space-y-1 text-xs font-semibold text-muted">
                    <span>Region</span>
                    <select data-filter="region" class="w-full rounded-xl border border-line px-3 py-2 text-sm font-normal">
                        <option value="">Semua region</option>
                        @foreach($regions as $region)
                            <option value="{{ $region->id }}">{{ $region->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="space-y-1 text-xs font-semibold text-muted">
                    <span>Klaster</span>
                    <select data-filter="cluster" class="w-full rounded-xl border border-line px-3 py-2 text-sm font-normal">
                        <option value="">Semua klaster</option>
                        @foreach($clusters as $cluster)
                            <option value="{{ $cluster->id }}" data-region-id="{{ $cluster->region_id }}">{{ $cluster->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="space-y-1 text-xs font-semibold text-muted">
                    <span>Lingkungan</span>
                    <select data-filter="environment" class="w-full rounded-xl border border-line px-3 py-2 text-sm font-normal">
                        <option value="">Semua lingkungan</option>
                        @foreach($environments as $environment)
                            <option value="{{ $environment->id }}" data-cluster-id="{{ $environment->cluster_id }}">{{ $environment->name }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                @php
                    $selectedClasses = old('classes', $assignedClassIds);
                    $selectedClasses = is_array($selectedClasses) ? array_map('intval', $selectedClasses) : [];
                    $selectedPrimary = (int) old('primary_class_id', $primaryClassId);
                @endphp
                @foreach($classes as $class)
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg bg-cream/70 p-3 text-sm"
                        data-assignment-class
                        data-region-id="{{ $class->environment?->cluster?->region_id }}"
                        data-cluster-id="{{ $class->environment?->cluster_id }}"
                        data-environment-id="{{ $class->environment_id }}">
                        <label class="flex items-center gap-2 font-semibold text-ink">
                            <input type="checkbox" name="classes[]" value="{{ $class->id }}" @checked(in_array($class->id, $selectedClasses, true)) class="rounded border-line text-orange focus:ring-orange">
                            {{ $class->name }} <span class="text-xs font-normal text-muted">{{ $class->code }} · {{ $class->environment?->name }}</span>
                        </label>
                        <label class="flex items-center gap-2 text-xs text-muted">
                            <input type="radio" name="primary_class_id" value="{{ $class->id }}" @checked($selectedPrimary === $class->id) required class="border-line text-orange focus:ring-orange">
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

@push('scripts')
<script>
    document.querySelectorAll('[data-class-assignment-filters]').forEach((filters) => {
        const form = filters.closest('form');
        const region = filters.querySelector('[data-filter="region"]');
        const cluster = filters.querySelector('[data-filter="cluster"]');
        const environment = filters.querySelector('[data-filter="environment"]');
        const classRows = form.querySelectorAll('[data-assignment-class]');

        const updateFilters = () => {
            const regionId = region.value;

            Array.from(cluster.options).forEach((option) => {
                if (option.value !== '') {
                    option.hidden = regionId !== '' && option.dataset.regionId !== regionId;
                }
            });
            if (cluster.selectedOptions[0]?.hidden) {
                cluster.value = '';
            }

            const clusterId = cluster.value;
            Array.from(environment.options).forEach((option) => {
                if (option.value !== '') {
                    option.hidden = clusterId !== '' && option.dataset.clusterId !== clusterId;
                    if (regionId !== '') {
                        const parent = form.querySelector(`[data-filter="cluster"] option[value="${option.dataset.clusterId}"]`);
                        option.hidden ||= parent?.dataset.regionId !== regionId;
                    }
                }
            });
            if (environment.selectedOptions[0]?.hidden) {
                environment.value = '';
            }

            classRows.forEach((row) => {
                row.hidden = (regionId !== '' && row.dataset.regionId !== regionId)
                    || (cluster.value !== '' && row.dataset.clusterId !== cluster.value)
                    || (environment.value !== '' && row.dataset.environmentId !== environment.value);
            });
        };

        region.addEventListener('change', updateFilters);
        cluster.addEventListener('change', updateFilters);
        environment.addEventListener('change', updateFilters);
        updateFilters();
    });
</script>
@endpush
