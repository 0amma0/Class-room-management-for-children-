<?php

namespace App\Actions\Admin;

use App\Models\Activity;
use App\Models\Classes;
use App\Models\Cluster;
use App\Models\Environment;
use App\Models\Region;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Route;

class GetDashboardStats
{
    /**
     * Retrieve aggregate stats and data for Admin Dashboard matching Figma mockup 12-dashboard-admin.
     * Uses strict aggregate queries (count, groupBy) without loading all rows.
     *
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        // 1. Stat cards
        $cards = [
            'active_regions' => Region::where('is_active', true)->count(),
            'clusters' => Cluster::where('is_active', true)->count(),
            'environments' => Environment::where('is_active', true)->count(),
            'active_classes' => Classes::where('status', 'active')->count(),
            'active_teachers' => Teacher::where('status', 'active')->count(),
            'active_students' => Student::where('status', 'active')->count(),
        ];

        // 2. Students per environment (Aggregate query, no N+1)
        $envData = Environment::query()
            ->where('environments.is_active', true)
            ->leftJoin('classes', function (JoinClause $join) {
                $join->on('classes.environment_id', '=', 'environments.id')
                    ->where('classes.status', '=', 'active');
            })
            ->leftJoin('students', function ($join) {
                $join->on('students.class_id', '=', 'classes.id')
                    ->where('students.status', '=', 'active');
            })
            ->leftJoin('class_teachers', 'class_teachers.class_id', '=', 'classes.id')
            ->leftJoin('teachers as active_teachers', function (JoinClause $join) {
                $join->on('active_teachers.id', '=', 'class_teachers.teacher_id')
                    ->where('active_teachers.status', '=', 'active');
            })
            ->select('environments.id', 'environments.name')
            ->selectRaw('COUNT(DISTINCT classes.id) as classes_count')
            ->selectRaw('COUNT(DISTINCT students.id) as students_count')
            ->selectRaw('COUNT(DISTINCT active_teachers.id) as teachers_count')
            ->groupBy('environments.id', 'environments.name')
            ->orderByDesc('students_count')
            ->get();

        $maxCount = $envData->max('students_count') ?: 1;

        $palette = ['#FBA93A', '#2E4BC0', '#2E9E5B', '#8ED6EC'];
        $avatarSets = [
            ['bangs', 'beret', 'short'],
            ['bun', 'afro', 'short-blue'],
            ['short', 'pigtails', 'bangs'],
        ];

        $studentsPerEnvironment = $envData->map(function ($env, $index) use ($maxCount, $palette, $avatarSets) {
            $count = (int) $env->students_count;
            $classCount = (int) $env->classes_count;

            return [
                'environment_name' => $env->name,
                'community_label' => $env->name.' Community',
                'classes_count' => $classCount,
                'classes_label' => $classCount.' '.($classCount === 1 ? 'class' : 'classes'),
                'count' => $count,
                'teachers_count' => (int) $env->teachers_count,
                'color' => $palette[$index % count($palette)],
                'avatars' => $avatarSets[$index % count($avatarSets)],
                'percentage' => $maxCount > 0 ? (int) round(($count / $maxCount) * 100) : 0,
            ];
        })->toArray();

        // 3. Needs review (Perlu ditinjau)
        $pendingActivitiesCount = Activity::where('status', 'pending_review')->count();
        $mustChangePasswordCount = User::where('role', 'guru')
            ->where('must_change_password', true)
            ->count();

        $needsReview = [
            [
                'title' => $pendingActivitiesCount.' activities awaiting review',
                'description' => 'Kegiatan kelas atau lingkungan yang diajukan untuk dipublikasikan.',
                'count' => $pendingActivitiesCount,
                'has_action' => Route::has('admin.activities'),
                'action_url' => Route::has('admin.activities') ? route('admin.activities') : null,
                'action_label' => 'Review',
            ],
            [
                'title' => $mustChangePasswordCount.' teacher has not changed their password',
                'description' => 'Guru yang masih menggunakan password sementara dari admin.',
                'count' => $mustChangePasswordCount,
                'has_action' => Route::has('admin.teachers'),
                'action_url' => Route::has('admin.teachers') ? route('admin.teachers') : null,
                'action_label' => 'View',
            ],
        ];

        return [
            'cards' => $cards,
            'studentsPerEnvironment' => $studentsPerEnvironment,
            'students_per_environment' => $studentsPerEnvironment,
            'needsReview' => $needsReview,
            'needs_review' => $needsReview,
        ];
    }
}
