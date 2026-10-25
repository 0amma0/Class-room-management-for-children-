<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

    public function destroyAll(Request $request): RedirectResponse
    {
        $activityIds = Activity::query()
            ->whereIn('scope_type', ['class', 'environment', 'cluster'])
            ->pluck('id');

        if ($activityIds->isEmpty()) {
            return redirect()->route('admin.activities')->with('success', 'Tidak ada aktivitas untuk dihapus.');
        }

        $photoPaths = DB::table('activity_photos')
            ->whereIn('activity_id', $activityIds)
            ->pluck('file_url');

        DB::transaction(function () use ($activityIds, $request): void {
            DB::table('activity_photos')->whereIn('activity_id', $activityIds)->delete();
            Activity::query()->whereIn('id', $activityIds)->delete();

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'deleted',
                'entity_type' => 'activity_review_bulk',
                'meta' => ['deleted_count' => $activityIds->count()],
                'ip' => $request->ip(),
            ]);
        });

        $failedFiles = [];
        foreach ($photoPaths as $path) {
            if (! is_string($path) || ! Str::startsWith($path, 'activity-photos/')) {
                continue;
            }

            $disk = Storage::disk('local');
            if ($disk->exists($path) && ! $disk->delete($path)) {
                $failedFiles[] = $path;
                Log::error('Unable to delete activity review image after bulk activity deletion.', ['path' => $path]);
            }

            $legacyDisk = Storage::disk('public');
            if ($legacyDisk->exists($path) && ! $legacyDisk->delete($path)) {
                $failedFiles[] = $path;
                Log::error('Unable to delete legacy public activity review image after bulk activity deletion.', ['path' => $path]);
            }
        }

        if ($failedFiles !== []) {
            return redirect()->route('admin.activities')
                ->with('warning', count($failedFiles).' file foto tidak berhasil dihapus. Data aktivitas sudah dihapus; periksa log aplikasi.');
        }

        return redirect()->route('admin.activities')->with('success', $activityIds->count().' aktivitas berhasil dihapus.');
    }
}
