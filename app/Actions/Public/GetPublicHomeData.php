<?php

namespace App\Actions\Public;

use App\Models\Activity;
use App\Models\Classes;
use App\Models\Environment;
use App\Models\PublicActivityCard;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Storage;

class GetPublicHomeData
{
    /**
     * Retrieve data for the public home page matching Figma mockup 01-beranda-publik.
     * Enforces privacy: only aggregate counts and general class info are exposed.
     * No student names, parent names, addresses, or private records are loaded.
     *
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        $classes = Classes::query()
            ->where('status', 'active')
            ->with(['environment:id,name'])
            ->withCount(['students' => function ($query) {
                $query->where('status', 'active');
            }])
            ->select(['id', 'environment_id', 'name', 'code', 'location', 'schedule', 'photo_path'])
            ->take(3)
            ->get()
            ->map(function ($class) {
                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'environment_name' => $class->environment?->name ?? 'Community',
                    'location' => $class->location ?? 'Lokasi belum ditentukan',
                    'schedule' => $class->schedule ?? 'Sabtu · 09:00–11:00',
                    'photo_url' => $class->photo_path !== null
                        && Storage::disk('public')->exists($class->photo_path)
                            ? Storage::disk('public')->url($class->photo_path)
                            : null,
                    'description' => 'A children\'s class for learning, playing, and sharing with caring teachers.',
                    // Privacy rule: if student count per class is < 3, display "< 3"
                    'student_count_display' => $class->students_count < 3 ? '< 3' : (string) $class->students_count,
                    'students_count' => $class->students_count,
                ];
            });

        $activities = Activity::query()
            ->where('status', 'published')
            ->select(['id', 'title', 'description', 'activity_date', 'scope_type'])
            ->with(['approvedPhotos' => fn ($query) => $query
                ->select(['id', 'activity_id', 'file_url'])
                ->latest('id')])
            ->latest('activity_date')
            ->take(4)
            ->get();

        // Community aggregation for the "Our Communities in West Java" section
        $communities = Environment::query()
            ->where('environments.is_active', true)
            ->leftJoin('classes as active_classes', function (JoinClause $join) {
                $join->on('active_classes.environment_id', '=', 'environments.id')
                    ->where('active_classes.status', '=', 'active');
            })
            ->leftJoin('class_teachers', 'class_teachers.class_id', '=', 'active_classes.id')
            ->leftJoin('teachers as active_teachers', function (JoinClause $join) {
                $join->on('active_teachers.id', '=', 'class_teachers.teacher_id')
                    ->where('active_teachers.status', '=', 'active');
            })
            ->leftJoin('students as active_students', function (JoinClause $join) {
                $join->on('active_students.class_id', '=', 'active_classes.id')
                    ->where('active_students.status', '=', 'active');
            })
            ->select('environments.id', 'environments.name')
            ->selectRaw('COUNT(DISTINCT active_classes.id) AS classes_count')
            ->selectRaw('COUNT(DISTINCT active_teachers.id) AS teachers_count')
            ->selectRaw('COUNT(DISTINCT active_students.id) AS students_count')
            ->groupBy('environments.id', 'environments.name')
            ->get()
            ->map(function ($env) {
                return [
                    'id' => $env->id,
                    'name' => $env->name,
                    'classes_count' => $env->classes_count,
                    'teachers_count' => (int) $env->teachers_count,
                    'students_count' => (int) $env->students_count,
                ];
            });

        $stats = [
            'active_classes' => Classes::where('status', 'active')->count(),
            'active_teachers' => Teacher::where('status', 'active')->count(),
            'active_students' => Student::where('status', 'active')->count(),
            'active_environments' => Environment::where('is_active', true)->count(),
        ];

        return [
            'classes' => $classes,
            'activities' => $activities,
            'programCards' => PublicActivityCard::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->limit(4)
                ->get(['id', 'title', 'description', 'image_path']),
            'communities' => $communities,
            'stats' => $stats,
        ];
    }
}
