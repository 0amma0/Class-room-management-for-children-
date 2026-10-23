<x-app-layout title="Activity Review — Children's Classes">
    <div class="mx-auto max-w-7xl space-y-6">
        <header><p class="text-sm font-semibold text-muted">Administration / Content</p><h1 class="mt-1 font-display text-3xl font-bold text-navy">Activity review</h1><p class="mt-1 text-sm text-muted">Review teacher submissions before they appear on public pages.</p></header>
        @if(session('success'))<x-ui-alert variant="ok">{{ session('success') }}</x-ui-alert>@endif
        <section class="overflow-hidden rounded-3xl border border-line bg-white shadow-sm">
            @if($activities->isEmpty())
                <p class="p-6 text-sm text-muted">No activity submissions are available.</p>
            @else
                <div class="overflow-x-auto"><table class="w-full min-w-[900px] text-left text-sm"><thead class="bg-cream text-xs font-bold uppercase tracking-wide text-muted"><tr><th class="px-5 py-4">Activity</th><th class="px-5 py-4">Scope</th><th class="px-5 py-4">Submitted by</th><th class="px-5 py-4">Date</th><th class="px-5 py-4">Status</th><th class="px-5 py-4">Review</th></tr></thead><tbody class="divide-y divide-line">
                    @foreach($activities as $activity)
                        @php($scopeName = $activity->class?->name ?? $activity->environment?->name ?? $activity->cluster?->name ?? '—')
                        <tr><td class="px-5 py-4"><strong>{{ $activity->title }}</strong><span class="block max-w-md text-xs text-muted">{{ $activity->description }}</span>@foreach($activity->photos as $photo)<span class="mt-2 flex items-center gap-2 text-xs text-muted"><img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($photo->file_url) }}" alt="Submitted activity image" class="size-12 rounded-md object-cover">{{ $photo->is_approved ? 'Approved image' : 'Image pending review' }}</span>@endforeach</td><td class="px-5 py-4">{{ ucfirst($activity->scope_type) }}<span class="block text-xs text-muted">{{ $scopeName }}</span></td><td class="px-5 py-4">{{ $activity->creator?->name ?? '—' }}</td><td class="px-5 py-4">{{ $activity->activity_date?->format('d M Y') ?? '—' }}</td><td class="px-5 py-4">{{ str_replace('_', ' ', ucfirst($activity->status)) }}</td><td class="px-5 py-4">
                            @if(in_array($activity->status, ['pending_review', 'draft'], true))
                                <div class="flex gap-2">
                                    <form method="POST" action="{{ route('admin.activities.update', $activity) }}">@csrf @method('PUT')<input type="hidden" name="status" value="published"><button class="rounded-full bg-ok px-4 py-2 text-xs font-bold text-white">Publish</button></form>
                                    <form method="POST" action="{{ route('admin.activities.update', $activity) }}">@csrf @method('PUT')<input type="hidden" name="status" value="archived"><button class="rounded-full border border-danger/30 px-4 py-2 text-xs font-bold text-danger">Archive</button></form>
                                </div>
                            @else
                                <span class="text-xs text-muted">Already reviewed</span>
                            @endif
                        </td></tr>
                    @endforeach
                </tbody></table></div>
                <div class="border-t border-line px-5 py-4">{{ $activities->links() }}</div>
            @endif
        </section>
    </div>
</x-app-layout>
