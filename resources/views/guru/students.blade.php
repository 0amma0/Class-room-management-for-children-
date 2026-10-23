<x-app-layout title="Participants — Children's Classes">
    @php
        $isAdmin = auth()->user()->isAdmin();
        $storeRoute = $isAdmin ? 'admin.students.store' : 'guru.students.store';
        $listLabel = $isAdmin ? 'all classes' : 'your assigned classes';
    @endphp

    <div class="mx-auto max-w-7xl space-y-6">
        <header class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-muted">{{ $isAdmin ? 'Administration' : 'Teacher workspace' }} / Participants</p>
                <h1 class="mt-1 font-display text-3xl font-bold text-navy">Participants</h1>
                <p class="mt-1 text-sm text-muted">{{ $students->total() }} participant records in {{ $listLabel }}</p>
            </div>
            <details class="group">
                <summary class="cursor-pointer list-none rounded-full bg-orange px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-orange/90">
                    Add participant
                </summary>
                <div class="fixed inset-0 z-50 overflow-y-auto bg-ink/50 p-4 sm:p-8">
                    <section class="mx-auto mt-6 max-w-3xl space-y-5 rounded-3xl bg-white p-6 text-ink shadow-xl sm:p-8" aria-label="Add participant">
                        <div class="flex items-center justify-between gap-4">
                            <h2 class="font-display text-xl font-bold text-navy">Add participant</h2>
                            <button type="button" onclick="this.closest('details').open = false" class="rounded-lg px-3 py-2 text-sm font-bold text-muted hover:bg-cream">Close</button>
                        </div>
                        @if($classes->isEmpty())
                            <p class="rounded-xl bg-orange/10 p-4 text-sm text-ink">There are no active classes available for your account yet.</p>
                        @else
                            @include('guru._student-form', ['classes' => $classes])
                        @endif
                    </section>
                </div>
            </details>
        </header>

        @if(session('success'))
            <x-ui-alert variant="ok">{{ session('success') }}</x-ui-alert>
        @endif

        @if($errors->any())
            <x-ui-alert variant="danger" title="Please check the participant details">
                <ul class="list-inside list-disc">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui-alert>
        @endif

        <div class="flex items-start gap-3 rounded-2xl border border-orange/30 bg-accent-soft p-4 text-sm text-ink/85">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mt-0.5 size-5 shrink-0 text-orange"><path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11z"></path><path d="m9 12 2 2 4-4"></path></svg>
            <p>Private data: participant details are visible only to administrators and teachers assigned to the participant's class. Deactivation preserves historical records.</p>
        </div>

        @if($isAdmin)
            <section class="grid gap-4 rounded-2xl border border-line bg-white p-5 shadow-sm lg:grid-cols-[auto_1fr] lg:items-center">
                <div><h2 class="font-display text-lg font-bold text-navy">Excel data exchange</h2><p class="mt-1 text-sm text-muted">Export the region-to-class summary and participant list, or import participant and hierarchy data.</p></div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('admin.students.export') }}" class="rounded-full bg-royal px-5 py-2.5 text-sm font-bold text-white hover:bg-royal/90">Export Excel report</a>
                    <a href="{{ route('admin.students.template') }}" class="rounded-full border border-line px-5 py-2.5 text-sm font-bold text-ink hover:bg-cream">Download import template</a>
                    <form method="POST" action="{{ route('admin.students.import') }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
                        @csrf
                        <input type="file" name="file" accept=".xlsx" required class="max-w-full rounded-xl border border-line bg-white px-3 py-2 text-sm">
                        <button class="rounded-full bg-orange px-5 py-2.5 text-sm font-bold text-white hover:bg-orange/90">Import Excel</button>
                    </form>
                </div>
            </section>
        @endif

        @if($errors->has('file'))
            <x-ui-alert variant="danger" title="Excel import failed">{{ $errors->first('file') }}</x-ui-alert>
        @endif

        <form method="GET" action="{{ url()->current() }}" class="grid gap-4 rounded-2xl border border-line bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-4">
            <label class="space-y-1 text-sm font-semibold text-ink">
                <span>Region</span>
                <select name="region_id" class="w-full rounded-xl border border-line bg-white px-3 py-2 font-normal">
                    <option value="">All regions</option>
                    @foreach($regions as $region)
                        <option value="{{ $region->id }}" @selected((string) ($filters['region_id'] ?? '') === (string) $region->id)>{{ $region->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="space-y-1 text-sm font-semibold text-ink">
                <span>Cluster</span>
                <select name="cluster_id" class="w-full rounded-xl border border-line bg-white px-3 py-2 font-normal">
                    <option value="">All clusters</option>
                    @foreach($clusters as $cluster)
                        <option value="{{ $cluster->id }}" @selected((string) ($filters['cluster_id'] ?? '') === (string) $cluster->id)>{{ $cluster->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="space-y-1 text-sm font-semibold text-ink">
                <span>Environment</span>
                <select name="environment_id" class="w-full rounded-xl border border-line bg-white px-3 py-2 font-normal">
                    <option value="">All environments</option>
                    @foreach($environments as $environment)
                        <option value="{{ $environment->id }}" @selected((string) ($filters['environment_id'] ?? '') === (string) $environment->id)>{{ $environment->name }}</option>
                    @endforeach
                </select>
            </label>
            <div class="flex items-end gap-2">
                <button type="submit" class="rounded-full bg-orange px-5 py-2.5 text-sm font-bold text-white hover:bg-orange/90">Filter</button>
                <a href="{{ url()->current() }}" class="rounded-full border border-line px-5 py-2.5 text-sm font-bold text-ink hover:bg-cream">Reset</a>
            </div>
        </form>

        <section class="overflow-hidden rounded-3xl border border-line bg-white shadow-sm" aria-label="Participant list">
            @if($students->isEmpty())
                <div class="flex min-h-72 flex-col items-center justify-center px-6 py-12 text-center">
                    <div class="flex -space-x-3">
                        <x-ui-avatar type="short" size="lg" class="border-4 border-white" />
                        <x-ui-avatar type="afro" size="lg" class="border-4 border-white" />
                        <x-ui-avatar type="pigtails" size="lg" class="border-4 border-white" />
                    </div>
                    <h2 class="mt-5 font-display text-xl font-bold text-navy">No participants to show</h2>
                    <p class="mt-1 max-w-md text-sm text-muted">Add a participant to an active class to get started.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[920px] text-left">
                        <thead class="bg-cream text-xs font-bold uppercase tracking-wide text-muted">
                            <tr>
                                <th scope="col" class="px-5 py-4">Code</th>
                                <th scope="col" class="px-5 py-4">Name</th>
                                <th scope="col" class="px-5 py-4">Class</th>
                                <th scope="col" class="px-5 py-4">Gender</th>
                                <th scope="col" class="px-5 py-4">Parent / Guardian</th>
                                <th scope="col" class="px-5 py-4">Status</th>
                                <th scope="col" class="px-5 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line text-sm">
                            @php
                                $studentAvatars = ['short', 'afro', 'pigtails', 'bun', 'braids'];
                            @endphp
                            @foreach($students as $student)
                                @php
                                    $genderLabel = match($student->gender) {
                                        'L' => 'Male',
                                        'P' => 'Female',
                                        default => '—',
                                    };
                                    $updateRoute = $isAdmin ? 'admin.students.update' : 'guru.students.update';
                                    $deactivateRoute = $isAdmin ? 'admin.students.deactivate' : 'guru.students.deactivate';
                                @endphp
                                <tr class="transition-colors hover:bg-cream/60">
                                    <td class="whitespace-nowrap px-5 py-4 font-mono text-xs text-muted">{{ $student->student_code }}</td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <x-ui-avatar :type="$studentAvatars[$loop->index % count($studentAvatars)]" size="sm" class="border-2 border-white shadow-sm" />
                                            <div>
                                                <span class="font-semibold text-ink">{{ $student->full_name }}</span>
                                                @if($student->nickname)
                                                    <span class="block text-xs text-muted">{{ $student->nickname }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-muted">{{ $student->class?->name ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-muted">{{ $genderLabel }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-muted">{{ $student->parent_name ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        @if($student->status === 'active')
                                            <span class="rounded-full bg-ok-soft px-3 py-1 text-xs font-bold text-ok">Active</span>
                                        @else
                                            <span class="rounded-full bg-danger/10 px-3 py-1 text-xs font-bold text-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right">
                                        <details class="text-left">
                                            <summary class="cursor-pointer font-semibold text-royal hover:underline">Edit</summary>
                                            <div class="fixed inset-0 z-50 overflow-y-auto bg-ink/50 p-4 sm:p-8">
                                                <section class="mx-auto mt-6 max-w-3xl space-y-5 rounded-3xl bg-white p-6 text-ink shadow-xl sm:p-8" aria-label="Edit participant">
                                                    <div class="flex items-center justify-between gap-4">
                                                        <h2 class="font-display text-xl font-bold text-navy">Edit {{ $student->full_name }}</h2>
                                                        <button type="button" onclick="this.closest('details').open = false" class="rounded-lg px-3 py-2 text-sm font-bold text-muted hover:bg-cream">Close</button>
                                                    </div>
                                                    @include('guru._student-form', ['student' => $student, 'classes' => $classes])
                                                </section>
                                            </div>
                                        </details>
                                        @if($student->status === 'active')
                                            <form method="POST" action="{{ route($deactivateRoute, ['studentId' => $student->id]) }}" class="mt-2" onsubmit="return confirm('Deactivate this participant? Historical records will be preserved.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs font-semibold text-danger hover:underline">Deactivate</button>
                                            </form>
                                        @endif
                                        @if($isAdmin)
                                            <form method="POST" action="{{ route('admin.students.destroy', ['studentId' => $student->id]) }}" class="mt-2" onsubmit="return confirm('Delete this participant permanently? Participants with history cannot be deleted.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs font-semibold text-danger hover:underline">Delete permanently</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-line px-5 py-4">
                    {{ $students->links() }}
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
