@php
    $fieldDefinitions = match ($resource) {
        'regions' => [
            ['name' => 'name', 'label' => 'Nama wilayah', 'type' => 'text'],
            ['name' => 'code', 'label' => 'Kode', 'type' => 'text'],
            ['name' => 'description', 'label' => 'Deskripsi', 'type' => 'textarea'],
            ['name' => 'is_active', 'label' => 'Tampil di sistem', 'type' => 'checkbox'],
            ['name' => 'is_development', 'label' => 'Dalam pengembangan', 'type' => 'checkbox'],
        ],
        'clusters' => [
            ['name' => 'region_id', 'label' => 'Wilayah', 'type' => 'select', 'options' => $parentOptions],
            ['name' => 'name', 'label' => 'Nama klaster', 'type' => 'text'],
            ['name' => 'code', 'label' => 'Kode', 'type' => 'text'],
            ['name' => 'description', 'label' => 'Deskripsi', 'type' => 'textarea'],
            ['name' => 'is_active', 'label' => 'Aktif', 'type' => 'checkbox'],
        ],
        'environments' => [
            ['name' => 'cluster_id', 'label' => 'Klaster', 'type' => 'select', 'options' => $parentOptions],
            ['name' => 'name', 'label' => 'Nama lingkungan', 'type' => 'text'],
            ['name' => 'code', 'label' => 'Kode', 'type' => 'text'],
            ['name' => 'description', 'label' => 'Deskripsi', 'type' => 'textarea'],
            ['name' => 'is_active', 'label' => 'Aktif', 'type' => 'checkbox'],
        ],
        'classes' => [
            ['name' => 'environment_id', 'label' => 'Lingkungan', 'type' => 'select', 'options' => $parentOptions],
            ['name' => 'grade_id', 'label' => 'Tingkat kurikulum', 'type' => 'select', 'options' => $grades, 'nullable' => true],
            ['name' => 'name', 'label' => 'Nama kelas', 'type' => 'text'],
            ['name' => 'code', 'label' => 'Kode kelas', 'type' => 'text'],
            ['name' => 'location', 'label' => 'Lokasi', 'type' => 'text'],
            ['name' => 'schedule', 'label' => 'Jadwal', 'type' => 'text'],
            ['name' => 'photo', 'label' => 'Foto kelas', 'type' => 'image'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'options', 'options' => ['active' => 'Aktif', 'inactive' => 'Nonaktif', 'archived' => 'Arsip']],
        ],
        default => [],
    };
    $isEditing = isset($record);
    $formAction = $isEditing
        ? route('admin.master-data.update', ['resource' => $resource, 'id' => $record->id])
        : route('admin.master-data.store', ['resource' => $resource]);
@endphp

<form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="grid gap-4 rounded-2xl border border-line bg-cream/60 p-4 sm:grid-cols-2">
    @csrf
    @if($isEditing)
        @method('PUT')
    @endif

    @foreach($fieldDefinitions as $field)
        @php
            $value = old($field['name'], $record?->{$field['name']} ?? ($field['name'] === 'is_active' ? true : false));
        @endphp

        <label class="block space-y-1.5 text-sm font-semibold text-ink {{ $field['type'] === 'textarea' ? 'sm:col-span-2' : '' }}">
            <span>{{ $field['label'] }}</span>
            @if($field['type'] === 'textarea')
                <textarea name="{{ $field['name'] }}" rows="2" class="w-full rounded-xl border border-line bg-white px-3 py-2 font-normal">{{ $value }}</textarea>
            @elseif($field['type'] === 'checkbox')
                <input type="hidden" name="{{ $field['name'] }}" value="0">
                <input type="checkbox" name="{{ $field['name'] }}" value="1" @checked((bool) $value) class="ms-2 size-4 rounded border-line text-orange focus:ring-orange">
            @elseif($field['type'] === 'image')
                @if($record?->photo_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($record->photo_path) }}" alt="Foto kelas {{ $record->name }}" class="mb-2 h-20 w-32 rounded-xl object-cover">
                @endif
                <input type="file" name="{{ $field['name'] }}" accept="image/*" class="w-full rounded-xl border border-line bg-white px-3 py-2 font-normal">
            @elseif(in_array($field['type'], ['select', 'options'], true))
                <select name="{{ $field['name'] }}" class="w-full rounded-xl border border-line bg-white px-3 py-2 font-normal">
                    @if($field['type'] === 'select' && ($field['nullable'] ?? false))
                        <option value="">Tidak ditentukan</option>
                    @endif
                    @foreach($field['options'] as $optionValue => $optionLabel)
                        @php
                            $optionId = is_object($optionLabel) ? $optionLabel->id : $optionValue;
                            $optionText = is_object($optionLabel) ? $optionLabel->name : $optionLabel;
                        @endphp
                        <option value="{{ $optionId }}" @selected((string) $value === (string) $optionId)>{{ $optionText }}</option>
                    @endforeach
                </select>
            @else
                <input type="text" name="{{ $field['name'] }}" value="{{ $value }}" class="w-full rounded-xl border border-line bg-white px-3 py-2 font-normal">
            @endif
            @error($field['name'])
                <span class="block text-xs font-medium text-danger">{{ $message }}</span>
            @enderror
        </label>
    @endforeach

    <div class="flex items-center gap-3 sm:col-span-2">
        <button type="submit" class="rounded-full bg-orange px-5 py-2 text-sm font-bold text-white hover:bg-orange/90">
            {{ $isEditing ? 'Simpan perubahan' : 'Tambah data' }}
        </button>
        @if($isEditing)
            <button form="{{ 'deactivate-'.$resource.'-'.$record->id }}" type="submit" class="rounded-full border border-danger/30 px-4 py-2 text-sm font-bold text-danger hover:bg-danger-soft">
                Nonaktifkan
            </button>
            <button form="{{ 'delete-'.$resource.'-'.$record->id }}" type="submit" class="rounded-full border border-danger/30 px-4 py-2 text-sm font-bold text-danger hover:bg-danger-soft">
                Hapus permanen
            </button>
        @endif
    </div>
</form>

@if($isEditing)
    <form id="{{ 'deactivate-'.$resource.'-'.$record->id }}" method="POST" action="{{ route('admin.master-data.deactivate', ['resource' => $resource, 'id' => $record->id]) }}" class="hidden" onsubmit="return confirm('Deactivate this record? Related history will be preserved.')">
        @csrf
        @method('DELETE')
    </form>
    <form id="{{ 'delete-'.$resource.'-'.$record->id }}" method="POST" action="{{ route('admin.master-data.destroy', ['resource' => $resource, 'id' => $record->id]) }}" class="hidden" onsubmit="return confirm('Delete this record permanently? Records with related data will be preserved and cannot be deleted.')">
        @csrf
        @method('DELETE')
    </form>
@endif
