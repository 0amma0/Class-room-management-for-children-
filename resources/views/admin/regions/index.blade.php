<x-app-layout title="Kelola Wilayah — Kelas Anak">
    <x-slot name="header">
        Kelola Wilayah
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        @if(session('success'))<x-ui-alert variant="ok">{{ session('success') }}</x-ui-alert>@endif
        @if($errors->any())<x-ui-alert variant="danger" title="Tindakan gagal"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></x-ui-alert>@endif
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-display font-bold text-xl text-[#1D2B33]">Daftar Wilayah</h2>
                <p class="text-xs text-[#65737D]">Daftar provinsi operasional dan wilayah pengembangan.</p>
            </div>
            <x-ui-badge variant="sun">Data Wilayah</x-ui-badge>
        </div>

        <details class="rounded-2xl border border-line bg-white p-5 shadow-sm">
            <summary class="cursor-pointer font-bold text-ink">Tambah wilayah</summary>
            <div class="mt-4">
                @include('admin.partials.master-data-form', ['resource' => 'regions', 'record' => null, 'parentOptions' => collect(), 'grades' => collect()])
            </div>
        </details>

        <div class="bg-white rounded-[20px] border-2 border-[#EFE4CC] p-6 shadow-sm">
            <x-ui-table :headers="['Nama Wilayah', 'Kode', 'Status', 'Klaster', 'Aksi']">
                @forelse($regions as $region)
                    <tr class="hover:bg-[#FFF8EA]/50 transition-colors">
                        <td class="px-4 py-3 font-semibold text-[#1D2B33]">{{ $region->name }}</td>
                        <td class="px-4 py-3 text-sm text-[#65737D] font-mono">{{ $region->code ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @if($region->is_active)
                                <x-ui-badge variant="ok" size="sm">Aktif</x-ui-badge>
                            @else
                                <x-ui-badge variant="muted" size="sm">Pengembangan</x-ui-badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-[#1D2B33]">{{ $region->clusters_count }} Klaster</td>
                        <td class="px-4 py-3 text-xs text-[#2E4BC0] font-semibold">
                            <details>
                                <summary class="cursor-pointer">Kelola</summary>
                                <div class="mt-3 min-w-80 text-left font-normal">
                                    @include('admin.partials.master-data-form', ['resource' => 'regions', 'record' => $region, 'parentOptions' => collect(), 'grades' => collect()])
                                </div>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-[#65737D]">
                            Belum ada data wilayah.
                        </td>
                    </tr>
                @endforelse
            </x-ui-table>

            <div class="mt-4">
                {{ $regions->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
