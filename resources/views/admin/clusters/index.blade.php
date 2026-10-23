<x-app-layout title="Kelola Klaster — Kelas Anak">
    <x-slot name="header">
        Kelola Klaster
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        @if(session('success'))<x-ui-alert variant="ok">{{ session('success') }}</x-ui-alert>@endif
        @if($errors->any())<x-ui-alert variant="danger" title="Tindakan gagal"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></x-ui-alert>@endif
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-display font-bold text-xl text-[#1D2B33]">Daftar Klaster</h2>
                <p class="text-xs text-[#65737D]">Pengelompokan lingkungan dalam satu wilayah.</p>
            </div>
            <x-ui-badge variant="sun">Data Klaster</x-ui-badge>
        </div>

        <details class="rounded-2xl border border-line bg-white p-5 shadow-sm">
            <summary class="cursor-pointer font-bold text-ink">Tambah klaster</summary>
            <div class="mt-4">
                @include('admin.partials.master-data-form', ['resource' => 'clusters', 'record' => null, 'parentOptions' => $regions, 'grades' => collect()])
            </div>
        </details>

        <div class="bg-white rounded-[20px] border-2 border-[#EFE4CC] p-6 shadow-sm">
            <x-ui-table :headers="['Nama Klaster', 'Wilayah', 'Lingkungan', 'Status', 'Aksi']">
                @forelse($clusters as $cluster)
                    <tr class="hover:bg-[#FFF8EA]/50 transition-colors">
                        <td class="px-4 py-3 font-semibold text-[#1D2B33]">{{ $cluster->name }}</td>
                        <td class="px-4 py-3 text-sm text-[#65737D]">{{ $cluster->region?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-[#1D2B33]">{{ $cluster->environments_count }} Lingkungan</td>
                        <td class="px-4 py-3">
                            @if($cluster->is_active)
                                <x-ui-badge variant="ok" size="sm">Aktif</x-ui-badge>
                            @else
                                <x-ui-badge variant="muted" size="sm">Nonaktif</x-ui-badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-[#2E4BC0] font-semibold">
                            <details>
                                <summary class="cursor-pointer">Kelola</summary>
                                <div class="mt-3 min-w-80 text-left font-normal">
                                    @include('admin.partials.master-data-form', ['resource' => 'clusters', 'record' => $cluster, 'parentOptions' => $regions, 'grades' => collect()])
                                </div>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-[#65737D]">
                            Belum ada data klaster.
                        </td>
                    </tr>
                @endforelse
            </x-ui-table>

            <div class="mt-4">
                {{ $clusters->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
