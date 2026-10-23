<x-app-layout title="Activities — Children's Classes">
    <div class="mx-auto max-w-7xl space-y-6">
        <header class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-muted">Teacher workspace / Activities</p>
                <h1 class="mt-1 font-display text-3xl font-bold text-navy">Activities</h1>
                <p class="mt-1 text-sm text-muted">Activity records from your assigned classes.</p>
            </div>
        </header>

        <div class="flex items-start gap-3 rounded-2xl border border-orange/50 bg-accent-soft p-4 text-sm text-ink/85">
            <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-orange text-sm font-bold text-white" aria-hidden="true">i</span>
            <p>Activities require administrator review before publication. Children's photos appear publicly only after approval.</p>
        </div>

        @if(session('success'))<x-ui-alert variant="ok">{{ session('success') }}</x-ui-alert>@endif
        @if($errors->any())<x-ui-alert variant="danger" title="Please check the activity"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></x-ui-alert>@endif
        <form method="POST" action="{{ route('guru.activities.store') }}" enctype="multipart/form-data" class="grid gap-4 rounded-3xl border border-line bg-white p-6 shadow-sm sm:grid-cols-2">
            @csrf
            <label class="space-y-1 text-sm font-semibold"><span>Class</span><select name="class_id" required class="w-full rounded-xl border border-line px-3 py-2">@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->name }}</option>@endforeach</select></label>
            <label class="space-y-1 text-sm font-semibold"><span>Activity date</span><input type="date" name="activity_date" value="{{ old('activity_date') }}" class="w-full rounded-xl border border-line px-3 py-2"></label>
            <label class="space-y-1 text-sm font-semibold sm:col-span-2"><span>Title</span><input name="title" value="{{ old('title') }}" required maxlength="255" class="w-full rounded-xl border border-line px-3 py-2"></label>
            <label class="space-y-1 text-sm font-semibold sm:col-span-2"><span>Description</span><textarea name="description" rows="3" maxlength="10000" class="w-full rounded-xl border border-line px-3 py-2">{{ old('description') }}</textarea></label>
            <label class="space-y-1 text-sm font-semibold sm:col-span-2"><span>Activity image</span><input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="w-full rounded-xl border border-line px-3 py-2 text-sm"><span class="block text-xs font-normal text-muted">JPG, PNG, or WebP up to 5 MB. Admin approval is required before publication.</span>@error('image')<span class="block text-xs text-danger">{{ $message }}</span>@enderror</label>
            <div class="sm:col-span-2"><button class="rounded-full bg-orange px-6 py-3 text-sm font-bold text-white hover:bg-orange/90">Submit for Admin review</button></div>
        </form>

        <section class="overflow-hidden rounded-3xl border border-line bg-white shadow-sm" aria-label="Class activities">
            @if($activities->isEmpty())
                <div class="flex min-h-72 flex-col items-center justify-center px-6 py-12 text-center">
                    <span class="flex size-14 items-center justify-center rounded-2xl bg-accent-soft text-orange">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-7"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9z"></path></svg>
                    </span>
                    <h2 class="mt-4 font-display text-xl font-bold text-navy">No activities yet</h2>
                    <p class="mt-1 max-w-md text-sm text-muted">Activity records for your assigned classes will appear here.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-175 text-left">
                        <thead class="bg-cream text-xs font-bold uppercase tracking-wide text-muted">
                            <tr>
                                <th scope="col" class="px-5 py-4">Title</th>
                                <th scope="col" class="px-5 py-4">Date</th>
                                <th scope="col" class="px-5 py-4">Class</th>
                                <th scope="col" class="px-5 py-4">Status</th>
                                <th scope="col" class="px-5 py-4">Manage</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line text-sm">
                            @foreach($activities as $activity)
                                <tr class="transition-colors hover:bg-cream/60">
                                    <td class="px-5 py-4 font-semibold text-ink">
                                        {{ $activity->title }}
                                        @foreach($activity->photos as $photo)
                                            <span class="mt-2 flex items-center gap-2 text-xs font-normal text-muted"><img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($photo->file_url) }}" alt="Activity image" class="size-10 rounded-md object-cover">{{ $photo->is_approved ? 'Image approved' : 'Image awaiting Admin review' }}</span>
                                        @endforeach
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-muted">{{ $activity->activity_date?->format('M j, Y') ?? 'Date not set' }}</td>
                                    <td class="px-5 py-4 text-muted">{{ $activity->class?->name ?? '—' }}</td>
                                    <td class="px-5 py-4">
                                        @php
                                            $statusClasses = match($activity->status) {
                                                'published' => 'bg-ok-soft text-ok',
                                                'pending_review' => 'bg-accent-soft text-orange',
                                                'draft' => 'bg-[#F0E6CF] text-muted',
                                                default => 'bg-danger-soft text-danger',
                                            };
                                        @endphp
                                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $statusClasses }}">{{ str_replace('_', ' ', ucfirst($activity->status)) }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        @if((int) $activity->created_by === auth()->id())
                                            <details>
                                                <summary class="cursor-pointer font-bold text-royal">Manage</summary>
                                                <div class="min-w-64 space-y-3 py-3">
                                                    @if($activity->status !== 'published')
                                                        <form method="POST" action="{{ route('guru.activities.update', $activity) }}" class="space-y-2">
                                                            @csrf @method('PUT')
                                                            <label class="block text-xs font-semibold">Class<select name="class_id" required class="mt-1 w-full rounded-lg border border-line px-2 py-1.5">@foreach($classes as $class)<option value="{{ $class->id }}" @selected($activity->class_id === $class->id)>{{ $class->name }}</option>@endforeach</select></label>
                                                            <label class="block text-xs font-semibold">Title<input name="title" value="{{ $activity->title }}" required maxlength="255" class="mt-1 w-full rounded-lg border border-line px-2 py-1.5"></label>
                                                            <label class="block text-xs font-semibold">Date<input type="date" name="activity_date" value="{{ $activity->activity_date?->toDateString() }}" class="mt-1 w-full rounded-lg border border-line px-2 py-1.5"></label>
                                                            <label class="block text-xs font-semibold">Description<textarea name="description" rows="2" maxlength="10000" class="mt-1 w-full rounded-lg border border-line px-2 py-1.5">{{ $activity->description }}</textarea></label>
                                                            <label class="block text-xs font-semibold">Add another image<input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="mt-1 w-full rounded-lg border border-line px-2 py-1.5 text-xs"><span class="mt-1 block font-normal text-muted">Up to 5 MB. The updated activity returns to review.</span></label>
                                                            <button class="rounded-full bg-orange px-4 py-2 text-xs font-bold text-white">Save and resubmit</button>
                                                        </form>
                                                    @endif
                                                    <form method="POST" action="{{ route('guru.activities.destroy', $activity) }}">@csrf @method('DELETE')<button class="rounded-full border border-danger/30 px-4 py-2 text-xs font-bold text-danger">Archive</button></form>
                                                </div>
                                            </details>
                                        @else
                                            <span class="text-xs text-muted">Read only</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-line px-5 py-4">
                    {{ $activities->links() }}
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
