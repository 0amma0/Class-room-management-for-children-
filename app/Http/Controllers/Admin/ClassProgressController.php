<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassCurriculumProgress;
use App\Models\Classes;
use App\Models\CurriculumLesson;
use App\Models\Grade;
use App\Models\LessonPlan;
use App\Models\Region;
use App\Models\TeacherReflection;
use App\Support\GuruClassAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ClassProgressController extends Controller
{
    public function index(Request $request, GuruClassAccess $access): View
    {
        $isAdmin = $request->user()->isAdmin();
        $regions = $isAdmin ? Region::query()->orderBy('name')->get(['id', 'name']) : collect();
        $requestedRegionId = $request->integer('region_id');
        $selectedRegion = $isAdmin
            ? ($requestedRegionId > 0
                ? Region::query()->findOrFail($requestedRegionId)
                : Region::query()->whereHas('clusters.environments.classes')->orderBy('name')->first())
            : null;
        $classesQuery = $isAdmin
            ? Classes::query()->when($selectedRegion, function ($query) use ($selectedRegion): void {
                $query->whereHas('environment.cluster', function ($clusterQuery) use ($selectedRegion): void {
                    $clusterQuery->where('region_id', $selectedRegion->id);
                });
            })
            : $access->classesFor($request->user());
        $classes = $classesQuery->orderBy('name')->get(['id', 'name']);
        $requestedClassId = $request->integer('class_id');
        $selectedClass = $requestedClassId > 0
            ? $classes->firstWhere('id', $requestedClassId)
            : $classes->first();
        abort_if($requestedClassId > 0 && $selectedClass === null, 404);
        $programGrade = Grade::query()->where('name', 'Program 24 Lesson')->firstOrFail();
        $lessons = CurriculumLesson::query()
            ->where('grade_id', $programGrade->id)
            ->orderBy('lesson_number')
            ->get(['id', 'lesson_number', 'title']);
        $progress = $selectedClass
            ? ClassCurriculumProgress::query()
                ->where('class_id', $selectedClass->id)
                ->whereIn('curriculum_lesson_id', $lessons->modelKeys())
                ->get()
                ->keyBy('curriculum_lesson_id')
            : collect();
        $completedLessonCount = $progress->where('status', 'selesai')->count();
        $lastProgressUpdate = $progress->max('updated_at');
        if ($isAdmin && $selectedClass !== null) {
            $selectedClass->load(['environment.cluster.region', 'teachers:id,full_name']);
        }
        $lessonPlans = $selectedClass && ! $isAdmin
            ? LessonPlan::query()
                ->where('class_id', $selectedClass->id)
                ->with('creator:id,name')
                ->latest('execution_date')
                ->limit(10)
                ->get()
            : collect();
        $reflectionsQuery = TeacherReflection::query()
            ->with(['teacher:id,full_name', 'class:id,name', 'lessonPlan:id,theme'])
            ->latest('reflection_date')
            ->latest('id');
        if ($isAdmin && $selectedRegion !== null) {
            $reflectionsQuery->whereHas('class.environment.cluster', function ($query) use ($selectedRegion): void {
                $query->where('region_id', $selectedRegion->id);
            });
        }
        $reflections = $reflectionsQuery->paginate(10);

        return view('admin.class-progress', compact(
            'classes',
            'selectedClass',
            'regions',
            'selectedRegion',
            'lessons',
            'progress',
            'completedLessonCount',
            'lastProgressUpdate',
            'lessonPlans',
            'reflections',
            'isAdmin',
        ));
    }

    public function update(Request $request, GuruClassAccess $access): RedirectResponse
    {
        $data = $request->validate([
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'completed_lessons' => ['nullable', 'array'],
            'completed_lessons.*' => ['integer', 'distinct', 'exists:curriculum_lessons,id'],
            'completed_at' => ['nullable', 'date', 'before_or_equal:today'],
        ]);
        if (! $request->user()->isAdmin()) {
            $access->classesFor($request->user())->whereKey($data['class_id'])->firstOrFail();
        }

        $programGrade = Grade::query()->where('name', 'Program 24 Lesson')->firstOrFail();
        $lessonIds = CurriculumLesson::query()
            ->where('grade_id', $programGrade->id)
            ->pluck('id');
        $completedIds = collect($data['completed_lessons'] ?? [])->map(fn ($id): int => (int) $id);

        if ($completedIds->diff($lessonIds)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'completed_lessons' => 'Lesson yang dipilih tidak termasuk dalam program 24 lesson.',
            ]);
        }

        DB::transaction(function () use ($data, $lessonIds, $completedIds): void {
            $existingProgress = ClassCurriculumProgress::query()
                ->where('class_id', $data['class_id'])
                ->whereIn('curriculum_lesson_id', $lessonIds)
                ->get()
                ->keyBy('curriculum_lesson_id');

            foreach ($lessonIds as $lessonId) {
                $completed = $completedIds->contains($lessonId);
                $previousCompletedAt = $existingProgress->get($lessonId)?->completed_at;
                ClassCurriculumProgress::query()->updateOrCreate(
                    [
                        'class_id' => $data['class_id'],
                        'curriculum_lesson_id' => $lessonId,
                    ],
                    [
                        'status' => $completed ? 'selesai' : 'belum',
                        'completed_at' => $completed
                            ? ($previousCompletedAt?->toDateString() ?? $data['completed_at'] ?? now()->toDateString())
                            : null,
                    ],
                );
            }
        });

        $route = $request->user()->isAdmin() ? 'admin.class-progress' : 'guru.class-progress';

        return redirect()->route($route, ['class_id' => $data['class_id']])
            ->with('success', 'Checklist lesson berhasil diperbarui.');
    }
}
