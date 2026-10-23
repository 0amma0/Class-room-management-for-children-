<x-app-layout title="Database Backups — Children's Classes">
    <div class="mx-auto max-w-7xl space-y-6">
        <header class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-muted">Admin / System</p>
                <h1 class="mt-1 font-display text-3xl font-bold text-navy">Database backups</h1>
                <p class="mt-1 text-sm text-muted">SQL backups include table data, triggers, and stored routines and stay in private application storage.</p>
            </div>
            <form method="POST" action="{{ route('admin.backups.store') }}">
                @csrf
                <button type="submit" class="rounded-full bg-orange px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-orange/90">
                    Create backup now
                </button>
            </form>
        </header>

        @if(session('success'))
            <x-ui-alert variant="ok">{{ session('success') }}</x-ui-alert>
        @endif

        @error('backup')
            <x-ui-alert variant="danger">{{ $message }}</x-ui-alert>
        @enderror

        <section class="space-y-4 rounded-3xl border border-line bg-white p-5 shadow-sm sm:p-7">
            <div>
                <h2 class="font-display text-xl font-bold text-ink">Ekspor Excel</h2>
                <p class="mt-1 text-sm text-muted">Pilih jenis data dan batasi cakupan berdasarkan hierarki wilayah. Ekspor Admin tidak menyertakan kata sandi.</p>
            </div>
            <form method="GET" action="{{ route('admin.backups.export') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <label class="space-y-1 text-sm font-semibold"><span>Jenis data</span><select name="data_type" class="w-full rounded-xl border border-line px-3 py-2"><option value="all">Semua data (kelas, peserta, Admin)</option><option value="classes">Data kelas</option><option value="students">Data peserta</option><option value="admins">Data Admin</option></select></label>
                <label class="space-y-1 text-sm font-semibold"><span>Wilayah</span><select name="region_id" class="w-full rounded-xl border border-line px-3 py-2"><option value="">Semua wilayah</option>@foreach($regions as $region)<option value="{{ $region->id }}">{{ $region->name }}</option>@endforeach</select></label>
                <label class="space-y-1 text-sm font-semibold"><span>Klaster</span><select name="cluster_id" class="w-full rounded-xl border border-line px-3 py-2"><option value="">Semua klaster</option>@foreach($clusters as $cluster)<option value="{{ $cluster->id }}">{{ $cluster->name }}</option>@endforeach</select></label>
                <label class="space-y-1 text-sm font-semibold"><span>Lingkungan</span><select name="environment_id" class="w-full rounded-xl border border-line px-3 py-2"><option value="">Semua lingkungan</option>@foreach($environments as $environment)<option value="{{ $environment->id }}">{{ $environment->name }}</option>@endforeach</select></label>
                <label class="space-y-1 text-sm font-semibold"><span>Kelas</span><select name="class_id" class="w-full rounded-xl border border-line px-3 py-2"><option value="">Semua kelas</option>@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->name }}</option>@endforeach</select></label>
                <div class="flex items-end"><button type="submit" class="w-full rounded-full bg-royal px-5 py-2.5 text-sm font-bold text-white hover:bg-royal/90">Unduh Excel</button></div>
            </form>
        </section>

        <section class="rounded-3xl border border-line bg-white p-5 shadow-sm sm:p-7">
            <h2 class="font-display text-xl font-bold text-ink">Restore a backup</h2>
            <p class="mt-2 text-sm leading-relaxed text-muted">
                SQL export uses the configured database connection and does not require <code>mysqldump</code>. Restore remains CLI-only because it replaces live database contents; the command asks for confirmation and creates a recovery backup before restoring.
            </p>
            <code class="mt-4 block overflow-x-auto rounded-xl bg-cream p-4 text-sm text-ink">php artisan app:db-restore &lt;filename.sql&gt;</code>
            @if(app()->isProduction())
                <p class="mt-2 text-xs font-semibold text-danger">Production restore also requires the explicit --force option.</p>
            @endif
        </section>

        <section class="overflow-hidden rounded-3xl border border-line bg-white shadow-sm" aria-label="Stored backups">
            <div class="border-b border-line px-5 py-4">
                <h2 class="font-display text-lg font-bold text-ink">Available backups</h2>
            </div>
            @if($backups->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-muted">No backups have been created yet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-160 text-left text-sm">
                        <thead class="bg-cream text-xs font-bold uppercase tracking-wide text-muted">
                            <tr>
                                <th scope="col" class="px-5 py-4">File</th>
                                <th scope="col" class="px-5 py-4">Created</th>
                                <th scope="col" class="px-5 py-4">Size</th>
                                <th scope="col" class="px-5 py-4">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @foreach($backups as $backup)
                                <tr>
                                    <td class="px-5 py-4 font-mono text-xs text-ink">{{ $backup['filename'] }}</td>
                                    <td class="px-5 py-4 text-muted">{{ \Illuminate\Support\Carbon::createFromTimestamp($backup['created_at'])->timezone('Asia/Jakarta')->format('d M Y H:i') }}</td>
                                    <td class="px-5 py-4 text-muted">{{ number_format($backup['size'] / 1024, 1) }} KB</td>
                                    <td class="px-5 py-4">
                                        <a href="{{ route('admin.backups.download', ['filename' => $backup['filename']]) }}" class="font-bold text-royal hover:underline">Download</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
