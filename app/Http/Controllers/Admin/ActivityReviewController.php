<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ActivityReviewController extends Controller
{
    public function index(): View
    {
        $activities = Activity::query()
            ->whereIn('scope_type', ['class', 'environment', 'cluster'])
            ->with(['class:id,name', 'environment:id,name', 'cluster:id,name', 'creator:id,name', 'photos'])
            ->orderByRaw("CASE WHEN status = 'pending_review' THEN 0 ELSE 1 END")
            ->orderByDesc('activity_date')
            ->paginate(15);

        return view('admin.activities.index', compact('activities'));
    }

    public function update(Request $request, Activity $activity): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['published', 'archived'])],
        ]);

        $reviewedAt = now();
        $activity->update([
            'status' => $data['status'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => $reviewedAt,
        ]);
        $activity->photos()->update([
            'is_approved' => $data['status'] === 'published',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => $reviewedAt,
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => $data['status'] === 'published' ? 'approved' : 'archived',
            'entity_type' => 'activity',
            'entity_id' => $activity->id,
            'meta' => ['status' => $data['status']],
            'ip' => $request->ip(),
        ]);

        return redirect()->route('admin.activities')->with(
            'success',
            $data['status'] === 'published' ? 'Kegiatan berhasil diterbitkan.' : 'Kegiatan berhasil diarsipkan.',
        );
    }
}
