<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityPhoto;
use App\Support\GuruClassAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class ActivityController extends Controller
{
    public function index(Request $request, GuruClassAccess $access): View
    {
        $classes = $access->classesFor($request->user())->orderBy('name')->get(['id', 'name']);
        $activities = Activity::query()
            ->where('scope_type', 'class')
            ->whereIn('class_id', $classes->modelKeys())
            ->with(['class:id,name', 'photos'])
            ->orderByDesc('activity_date')
            ->orderByDesc('id')
            ->paginate(10);

        return view('guru.activities', compact('classes', 'activities'));
    }

    public function store(Request $request, GuruClassAccess $access): RedirectResponse
    {
        $data = $request->validate([
            'class_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'activity_date' => ['nullable', 'date'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        $access->classesFor($request->user())->whereKey($data['class_id'])->firstOrFail();

        $imagePath = $this->storeImage($request);
        unset($data['image']);

        try {
            $activity = DB::transaction(function () use ($request, $data, $imagePath): Activity {
                $activity = Activity::create([
                    ...$data,
                    'scope_type' => 'class',
                    'status' => 'pending_review',
                    'created_by' => $request->user()->id,
                ]);
                $this->attachImage($activity, $imagePath);

                return $activity;
            });
        } catch (Throwable $exception) {
            if ($imagePath !== null) {
                Storage::disk('public')->delete($imagePath);
            }

            throw $exception;
        }

        return redirect()->route('guru.activities')->with('success', 'Kegiatan disimpan dan menunggu tinjauan Admin.');
    }

    public function update(Request $request, Activity $activity, GuruClassAccess $access): RedirectResponse
    {
        $this->ownedActivitiesFor($request, $access)->findOrFail($activity->id);
        abort_unless($activity->status !== 'published', 403);
        $data = $request->validate([
            'class_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'activity_date' => ['nullable', 'date'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        $access->classesFor($request->user())->whereKey($data['class_id'])->firstOrFail();
        $imagePath = $this->storeImage($request);
        unset($data['image']);

        try {
            DB::transaction(function () use ($activity, $data, $imagePath): void {
                $activity->update([...$data, 'status' => 'pending_review']);
                $this->attachImage($activity, $imagePath);
            });
        } catch (Throwable $exception) {
            if ($imagePath !== null) {
                Storage::disk('public')->delete($imagePath);
            }

            throw $exception;
        }

        return redirect()->route('guru.activities')->with('success', 'Kegiatan diperbarui dan dikirim kembali untuk tinjauan Admin.');
    }

    public function destroy(Request $request, Activity $activity, GuruClassAccess $access): RedirectResponse
    {
        $this->ownedActivitiesFor($request, $access)->findOrFail($activity->id)->update(['status' => 'archived']);

        return redirect()->route('guru.activities')->with('success', 'Kegiatan berhasil diarsipkan.');
    }

    /**
     * @return Builder<Activity>
     */
    private function ownedActivitiesFor(Request $request, GuruClassAccess $access): Builder
    {
        return Activity::query()
            ->where('scope_type', 'class')
            ->where('created_by', $request->user()->id)
            ->whereIn('class_id', $access->classesFor($request->user())->select('classes.id'));
    }

    private function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $path = $request->file('image')->store('activity-photos', 'public');
        if (! is_string($path) || ! Storage::disk('public')->exists($path)) {
            throw ValidationException::withMessages(['image' => 'Gambar kegiatan gagal disimpan. Silakan coba lagi.']);
        }

        return $path;
    }

    private function attachImage(Activity $activity, ?string $imagePath): void
    {
        if ($imagePath === null) {
            return;
        }

        ActivityPhoto::create([
            'activity_id' => $activity->id,
            'file_url' => $imagePath,
            'is_approved' => false,
        ]);
    }
}
