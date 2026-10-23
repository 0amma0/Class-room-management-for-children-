<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Classes;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuruDashboardController extends Controller
{
    public function dashboard(Request $request): View
    {
        $classes = $this->assignedClassesQuery($request->user()->teacher_id)
            ->with('environment:id,name')
            ->withCount([
                'students' => function (Builder $query) {
                    $query->where('status', 'active');
                },
            ])
            ->get(['id', 'environment_id', 'name', 'code', 'schedule']);

        $activities = Activity::query()
            ->whereIn('class_id', $classes->modelKeys())
            ->where('status', 'published')
            ->whereNotNull('activity_date')
            ->with('class:id,name')
            ->orderBy('activity_date')
            ->limit(3)
            ->get(['id', 'class_id', 'title', 'activity_date']);

        return view('guru.dashboard', compact('classes', 'activities'));
    }

    public function classes(Request $request): View
    {
        $classes = $this->assignedClassesQuery($request->user()->teacher_id)
            ->with('environment:id,name')
            ->withCount([
                'students' => function (Builder $query) {
                    $query->where('status', 'active');
                },
            ])
            ->orderBy('name')
            ->get(['id', 'environment_id', 'name', 'code', 'location', 'schedule']);

        return view('guru.classes', compact('classes'));
    }

    public function students(Request $request): View
    {
        $teacherId = $request->user()->teacher_id;
        $students = Student::query()
            ->where('status', 'active')
            ->whereHas('class', function (Builder $query) use ($teacherId) {
                $query->where('classes.status', 'active')
                    ->whereHas('teachers', function (Builder $query) use ($teacherId) {
                        $query->whereKey($teacherId)
                            ->where('teachers.status', 'active');
                    });
            })
            ->with('class:id,name')
            ->orderBy('full_name')
            ->paginate(15);

        return view('guru.students', compact('students'));
    }

    public function attendances(Request $request): View
    {
        return view('guru.attendances');
    }

    public function developments(Request $request): View
    {
        return view('guru.developments');
    }

    public function activities(Request $request): View
    {
        $teacherId = $request->user()->teacher_id;
        $activities = Activity::query()
            ->where('scope_type', 'class')
            ->whereHas('class', function (Builder $query) use ($teacherId) {
                $query->where('classes.status', 'active')
                    ->whereHas('teachers', function (Builder $query) use ($teacherId) {
                        $query->whereKey($teacherId)
                            ->where('teachers.status', 'active');
                    });
            })
            ->with('class:id,name')
            ->orderByDesc('activity_date')
            ->orderByDesc('id')
            ->paginate(10);

        return view('guru.activities', compact('activities'));
    }

    private function assignedClassesQuery(?int $teacherId): Builder
    {
        return Classes::query()
            ->where('classes.status', 'active')
            ->whereHas('teachers', function (Builder $query) use ($teacherId) {
                $query->whereKey($teacherId)
                    ->where('teachers.status', 'active');
            });
    }
}
