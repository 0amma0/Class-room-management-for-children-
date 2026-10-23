<x-app-layout title="Attendance — Children's Classes">
    <div class="mx-auto max-w-7xl space-y-6">
        <header>
            <p class="text-sm font-semibold text-muted">Teacher workspace / Attendance</p>
            <h1 class="mt-1 font-display text-3xl font-bold text-navy">Attendance</h1>
            <p class="mt-1 text-sm text-muted">Record session attendance and absence notes for your assigned classes.</p>
        </header>
        @if(session('success'))<x-ui-alert variant="ok">{{ session('success') }}</x-ui-alert>@endif
        @if($errors->any())
            <x-ui-alert variant="danger" title="Please check the attendance details">
                <ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </x-ui-alert>
        @endif
        <form method="GET" action="{{ route('guru.attendances') }}" class="grid gap-4 rounded-2xl border border-line bg-white p-4 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1fr_auto]">
            <label class="space-y-1 text-sm font-semibold text-ink"><span>Class</span>
                <select name="class_id" required class="w-full rounded-xl border border-line px-3 py-2 font-normal">
                    @foreach($classes as $class)<option value="{{ $class->id }}" @selected((int) $classId === $class->id)>{{ $class->name }}</option>@endforeach
                </select>
            </label>
            <label class="space-y-1 text-sm font-semibold text-ink"><span>Session date</span>
                <input type="date" name="attendance_date" value="{{ $attendanceDate }}" max="{{ now()->toDateString() }}" required class="w-full rounded-xl border border-line px-3 py-2 font-normal">
            </label>
            <label class="space-y-1 text-sm font-semibold text-ink"><span>Session number (1–24)</span>
                <select name="session_number" required class="w-full rounded-xl border border-line px-3 py-2 font-normal">
                    @for($session = 1; $session <= 24; $session++)<option value="{{ $session }}" @selected($sessionNumber === $session)>Session {{ $session }}</option>@endfor
                </select>
            </label>
            <div class="flex items-end"><button class="rounded-full border border-line px-5 py-2.5 text-sm font-bold text-ink hover:bg-cream">Load roster</button></div>
        </form>
        @if($classes->isEmpty())
            <p class="rounded-2xl border border-line bg-white p-6 text-sm text-muted">There are no active assigned classes.</p>
        @elseif($students->isEmpty())
            <p class="rounded-2xl border border-line bg-white p-6 text-sm text-muted">No active participants are assigned to this class.</p>
        @else
            <form method="POST" action="{{ route('guru.attendances.store') }}" class="overflow-hidden rounded-3xl border border-line bg-white shadow-sm">
                @csrf
                <input type="hidden" name="class_id" value="{{ $classId }}">
                <input type="hidden" name="attendance_date" value="{{ $attendanceDate }}">
                <input type="hidden" name="session_number" value="{{ $sessionNumber }}">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[720px] text-left text-sm">
                        <thead class="bg-cream text-xs font-bold uppercase tracking-wide text-muted"><tr><th class="px-5 py-4">Participant</th><th class="px-5 py-4">Attendance</th><th class="px-5 py-4">Absence / session note</th></tr></thead>
                        <tbody class="divide-y divide-line">
                            @foreach($students as $student)
                                @php($record = $records->get($student->id))
                                <tr>
                                    <td class="px-5 py-4 font-semibold text-ink">{{ $student->full_name }}</td>
                                    <td class="px-5 py-4">
                                        <select name="attendance[{{ $student->id }}][status]" required class="rounded-xl border border-line px-3 py-2">
                                            @foreach(['present' => 'Present', 'absent' => 'Absent', 'sick' => 'Sick', 'permission' => 'Permission'] as $value => $label)
                                                <option value="{{ $value }}" @selected(old("attendance.{$student->id}.status", $record?->status ?? 'present') === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-5 py-4"><input name="attendance[{{ $student->id }}][note]" value="{{ old("attendance.{$student->id}.note", $record?->note) }}" maxlength="2000" class="w-full rounded-xl border border-line px-3 py-2" placeholder="Optional"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="flex justify-end border-t border-line p-4"><button class="rounded-full bg-orange px-6 py-3 text-sm font-bold text-white hover:bg-orange/90">Save attendance</button></div>
            </form>
            @if($records->isNotEmpty())
                <div class="space-y-2 border-t border-line p-4">
                    <h2 class="font-display font-bold text-navy">Existing records for this session</h2>
                    @foreach($records as $record)
                        <div class="flex flex-wrap items-center justify-between gap-3 text-sm">
                            <span>{{ $record->student->full_name ?? 'Participant' }} · {{ ucfirst($record->status) }}</span>
                            <form method="POST" action="{{ route('guru.attendances.destroy', $record) }}">@csrf @method('DELETE')<button class="rounded-full border border-danger/30 px-4 py-2 text-xs font-bold text-danger">Delete record</button></form>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</x-app-layout>
