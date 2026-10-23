<x-app-layout title="Development — Children's Classes">
    <div class="mx-auto max-w-7xl space-y-6">
        <header><p class="text-sm font-semibold text-muted">Teacher workspace / Development</p><h1 class="mt-1 font-display text-3xl font-bold text-navy">Progress observations</h1><p class="mt-1 text-sm text-muted">Private development notes for participants in your assigned classes.</p></header>
        @if(session('success'))<x-ui-alert variant="ok">{{ session('success') }}</x-ui-alert>@endif
        @if($errors->any())<x-ui-alert variant="danger" title="Please check the observation"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></x-ui-alert>@endif
        <form method="POST" action="{{ route('guru.developments.store') }}" class="grid gap-4 rounded-3xl border border-line bg-white p-6 shadow-sm sm:grid-cols-2 lg:grid-cols-3">
            @csrf
            <label class="space-y-1 text-sm font-semibold"><span>Class</span><select name="class_id" id="development-class" required class="w-full rounded-xl border border-line px-3 py-2">@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->name }}</option>@endforeach</select></label>
            <label class="space-y-1 text-sm font-semibold"><span>Participant</span><select name="student_id" id="development-student" required class="w-full rounded-xl border border-line px-3 py-2">@foreach($studentsByClass as $classId => $group)@foreach($group as $student)<option value="{{ $student->id }}" data-class-id="{{ $classId }}">{{ $student->full_name }}</option>@endforeach @endforeach</select></label>
            <label class="space-y-1 text-sm font-semibold"><span>Date</span><input type="date" name="development_date" max="{{ now()->toDateString() }}" value="{{ now()->toDateString() }}" required class="w-full rounded-xl border border-line px-3 py-2"></label>
            <label class="space-y-1 text-sm font-semibold"><span>Assessment</span><select name="assessment" required class="w-full rounded-xl border border-line px-3 py-2"><option value="Perlu dukungan">Perlu dukungan</option><option value="Cukup">Cukup</option><option value="Baik">Baik</option><option value="Sangat baik">Sangat baik</option></select></label>
            <label class="space-y-1 text-sm font-semibold sm:col-span-2"><span>Observation / follow-up note</span><textarea name="note" rows="2" maxlength="5000" class="w-full rounded-xl border border-line px-3 py-2"></textarea></label>
            <div class="sm:col-span-2 lg:col-span-3"><button class="rounded-full bg-orange px-6 py-3 text-sm font-bold text-white hover:bg-orange/90">Save observation</button></div>
        </form>
        <section class="overflow-hidden rounded-3xl border border-line bg-white shadow-sm">
            @if($developments->isEmpty())<p class="p-6 text-sm text-muted">No observations have been recorded.</p>@else
                <div class="overflow-x-auto"><table class="w-full min-w-[820px] text-left text-sm"><thead class="bg-cream text-xs font-bold uppercase tracking-wide text-muted"><tr><th class="px-5 py-4">Date</th><th class="px-5 py-4">Participant / class</th><th class="px-5 py-4">Assessment</th><th class="px-5 py-4">Observation</th><th class="px-5 py-4">Actions</th></tr></thead><tbody class="divide-y divide-line">@foreach($developments as $record)<tr><td class="px-5 py-4">{{ $record->development_date->format('d M Y') }}</td><td class="px-5 py-4 font-semibold">{{ $record->student->full_name }}<span class="block text-xs font-normal text-muted">{{ $record->student->class?->name }}</span></td><td class="px-5 py-4">{{ $record->assessment }}</td><td class="max-w-lg px-5 py-4 text-muted">{{ $record->note ?: '—' }}</td><td class="px-5 py-4"><details><summary class="cursor-pointer font-bold text-royal">Manage</summary><div class="min-w-64 space-y-3 py-3"><form method="POST" action="{{ route('guru.developments.update', $record) }}" class="space-y-2">@csrf @method('PUT')<input type="hidden" name="class_id" value="{{ $record->student->class_id }}"><input type="hidden" name="student_id" value="{{ $record->student_id }}"><label class="block text-xs font-semibold">Date<input type="date" name="development_date" value="{{ $record->development_date->toDateString() }}" max="{{ now()->toDateString() }}" required class="mt-1 w-full rounded-lg border border-line px-2 py-1.5"></label><label class="block text-xs font-semibold">Assessment<select name="assessment" class="mt-1 w-full rounded-lg border border-line px-2 py-1.5">@foreach(['Perlu dukungan', 'Cukup', 'Baik', 'Sangat baik'] as $assessment)<option @selected($record->assessment === $assessment)>{{ $assessment }}</option>@endforeach</select></label><label class="block text-xs font-semibold">Note<textarea name="note" rows="2" maxlength="5000" class="mt-1 w-full rounded-lg border border-line px-2 py-1.5">{{ $record->note }}</textarea></label><button class="rounded-full bg-orange px-4 py-2 text-xs font-bold text-white">Save</button></form><form method="POST" action="{{ route('guru.developments.destroy', $record) }}">@csrf @method('DELETE')<button class="rounded-full border border-danger/30 px-4 py-2 text-xs font-bold text-danger">Delete</button></form></div></details></td></tr>@endforeach</tbody></table></div>
                <div class="border-t border-line px-5 py-4">{{ $developments->links() }}</div>
            @endif
        </section>
    </div>
    <script>
        const developmentClass = document.getElementById('development-class');
        const developmentStudent = document.getElementById('development-student');
        const filterDevelopmentStudents = () => {
            const options = [...developmentStudent.options];
            options.forEach((option) => {
                option.hidden = option.dataset.classId !== developmentClass.value;
            });
            const available = options.find((option) => !option.hidden);
            if (available) {
                developmentStudent.value = available.value;
            }
        };
        developmentClass.addEventListener('change', filterDevelopmentStudents);
        filterDevelopmentStudents();
    </script>
</x-app-layout>
