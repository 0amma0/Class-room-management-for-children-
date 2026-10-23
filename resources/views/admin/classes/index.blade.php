<x-app-layout title="Kelola Kelas — Kelas Anak">
    <x-slot name="header">
        Kelola Kelas
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        @if(session('success'))<x-ui-alert variant="ok">{{ session('success') }}</x-ui-alert>@endif
        @if($errors->any())<x-ui-alert variant="danger" title="Tindakan gagal"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></x-ui-alert>@endif
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-display font-bold text-xl text-[#1D2B33]">Daftar Kelas</h2>
                <p class="text-xs text-[#65737D]">Seluruh kelas anak aktif dan terdaftar.</p>
            </div>
            <x-ui-badge variant="sun">Data Kelas</x-ui-badge>
        </div>

        <details class="rounded-2xl border border-line bg-white p-5 shadow-sm">
            <summary class="cursor-pointer font-bold text-ink">Tambah kelas</summary>
            <div class="mt-4">
                @include('admin.partials.master-data-form', ['resource' => 'classes', 'record' => null, 'parentOptions' => $environments, 'grades' => $grades])
            </div>
        </details>

        <div class="bg-white rounded-[20px] border-2 border-[#EFE4CC] p-6 shadow-sm">
            <x-ui-table :headers="['Nama Kelas', 'Foto', 'Kode', 'Lingkungan', 'Lokasi', 'Jadwal', 'Peserta', 'Status', 'Aksi']">
                @forelse($classes as $class)
                    <tr class="hover:bg-[#FFF8EA]/50 transition-colors">
                        <td class="px-4 py-3 font-semibold text-[#1D2B33]">{{ $class->name }}</td>
                        <td class="px-4 py-3">
                            @if($class->photo_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($class->photo_path) }}" alt="Foto kelas {{ $class->name }}" class="h-12 w-16 rounded-lg object-cover">
                            @else
                                <span class="text-xs text-[#65737D]">Belum ada foto</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs font-mono text-[#65737D]">{{ $class->code ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-[#1D2B33]">{{ $class->environment?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-[#65737D]">{{ $class->location ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-[#65737D]">{{ $class->schedule ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-[#1F2F8A]">{{ $class->students_count }} anak</td>
                        <td class="px-4 py-3">
                            @if($class->status === 'active')
                                <x-ui-badge variant="ok" size="sm">Aktif</x-ui-badge>
                            @else
                                <x-ui-badge variant="muted" size="sm">{{ ucfirst($class->status) }}</x-ui-badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-[#2E4BC0] font-semibold">
                            <details>
                                <summary class="cursor-pointer">Kelola</summary>
                                <div class="mt-3 min-w-80 text-left font-normal">
                                    @include('admin.partials.master-data-form', ['resource' => 'classes', 'record' => $class, 'parentOptions' => $environments, 'grades' => $grades])
                                </div>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-sm text-[#65737D]">
                            Belum ada data kelas.
                        </td>
                    </tr>
                @endforelse
            </x-ui-table>

            <div class="mt-4">
                {{ $classes->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
