<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Cluster;
use App\Models\Region;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PublicRegionController extends Controller
{
    public function index(Request $request): View
    {
        $regions = Region::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
        $requestedRegionId = $request->integer('region_id');
        $region = $requestedRegionId > 0
            ? $regions->firstWhere('id', $requestedRegionId)
            : $regions->first(fn (Region $candidate): bool => $candidate->clusters()
                ->where('is_active', true)
                ->whereHas('environments', fn ($query) => $query->where('is_active', true)
                    ->whereHas('classes', fn ($classQuery) => $classQuery->where('status', 'active')))
                ->exists());
        abort_if($requestedRegionId > 0 && $region === null, 404);

        $regionNames = [
            'JB' => 'West Java',
            'JT' => 'Central Java',
            'JI' => 'East Java',
            'JK' => 'Jakarta',
        ];

        $regionName = $region === null
            ? null
            : ($regionNames[$region->code] ?? $region->name);

        $comingSoonRegions = Region::query()
            ->where('is_active', false)
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->map(fn (Region $comingSoonRegion): array => [
                'id' => $comingSoonRegion->id,
                'name' => $regionNames[$comingSoonRegion->code] ?? $comingSoonRegion->name,
            ]);

        $communities = collect();
        $clusters = collect();
        $classes = collect();
        $stats = [
            'active_classes' => 0,
            'active_teachers' => 0,
            'active_students' => 0,
        ];

        if ($region !== null) {
            $communityQuery = DB::table('environments')
                ->join('clusters', 'clusters.id', '=', 'environments.cluster_id')
                ->leftJoin('classes as active_classes', function (JoinClause $join): void {
                    $join->on('active_classes.environment_id', '=', 'environments.id')
                        ->where('active_classes.status', '=', 'active');
                })
                ->leftJoin('class_teachers', 'class_teachers.class_id', '=', 'active_classes.id')
                ->leftJoin('teachers as active_teachers', function (JoinClause $join): void {
                    $join->on('active_teachers.id', '=', 'class_teachers.teacher_id')
                        ->where('active_teachers.status', '=', 'active');
                })
                ->leftJoin('students as active_students', function (JoinClause $join): void {
                    $join->on('active_students.class_id', '=', 'active_classes.id')
                        ->where('active_students.status', '=', 'active');
                })
                ->where('clusters.region_id', $region->id)
                ->where('clusters.is_active', true)
                ->where('environments.is_active', true)
                ->whereExists(function ($query): void {
                    $query->selectRaw('1')
                        ->from('classes')
                        ->whereColumn('classes.environment_id', 'environments.id')
                        ->where('classes.status', 'active');
                });

            $communities = (clone $communityQuery)
                ->select([
                    'clusters.id as cluster_id',
                    'clusters.name as cluster_name',
                    'environments.id',
                    'environments.name',
                ])
                ->selectRaw('COUNT(DISTINCT active_classes.id) AS classes_count')
                ->selectRaw('COUNT(DISTINCT active_teachers.id) AS teachers_count')
                ->selectRaw('COUNT(DISTINCT active_students.id) AS students_count')
                ->groupBy('clusters.id', 'clusters.name', 'environments.id', 'environments.name')
                ->orderBy('clusters.name')
                ->orderBy('environments.name')
                ->get()
                ->map(fn (object $community): array => [
                    'cluster_id' => (int) $community->cluster_id,
                    'cluster_name' => $community->cluster_name,
                    'id' => (int) $community->id,
                    'name' => $community->name,
                    'classes_count' => (int) $community->classes_count,
                    'teachers_count' => (int) $community->teachers_count,
                    'students_count' => (int) $community->students_count,
                ]);

            $regionStats = (clone $communityQuery)
                ->selectRaw('COUNT(DISTINCT active_classes.id) AS active_classes')
                ->selectRaw('COUNT(DISTINCT active_teachers.id) AS active_teachers')
                ->selectRaw('COUNT(DISTINCT active_students.id) AS active_students')
                ->first();

            $stats = [
                'active_classes' => (int) $regionStats->active_classes,
                'active_teachers' => (int) $regionStats->active_teachers,
                'active_students' => (int) $regionStats->active_students,
            ];

            $clusters = Cluster::query()
                ->where('region_id', $region->id)
                ->where('is_active', true)
                ->whereHas('environments', function ($query): void {
                    $query->where('is_active', true)
                        ->whereHas('classes', fn ($classQuery) => $classQuery->where('status', 'active'));
                })
                ->orderBy('name')
                ->get(['id', 'name']);

            $classes = Classes::query()
                ->where('status', 'active')
                ->whereHas('environment', function ($environmentQuery) use ($region): void {
                    $environmentQuery
                        ->where('is_active', true)
                        ->whereHas('cluster', function ($clusterQuery) use ($region): void {
                            $clusterQuery
                                ->where('region_id', $region->id)
                                ->where('is_active', true);
                        });
                })
                ->with(['environment:id,name,cluster_id'])
                ->withCount(['students as active_students_count' => function ($query): void {
                    $query->where('status', 'active');
                }])
                ->orderBy('environment_id')
                ->orderBy('name')
                ->limit(30)
                ->get(['id', 'environment_id', 'name', 'schedule']);
        }

        $communitiesByCluster = $communities->groupBy('cluster_id');
        $classesByEnvironment = $classes->groupBy('environment_id');
        $requestedClusterId = $request->integer('cluster_id');
        $selectedClusterId = $clusters->contains('id', $requestedClusterId)
            ? $requestedClusterId
            : $clusters->first()?->id;
        $selectedClusterCommunities = $communitiesByCluster->get($selectedClusterId, collect());
        $requestedEnvironmentId = $request->integer('environment_id');
        $selectedEnvironment = $selectedClusterCommunities->contains('id', $requestedEnvironmentId)
            ? $selectedClusterCommunities->firstWhere('id', $requestedEnvironmentId)
            : $selectedClusterCommunities->first();

        return view('public.regions', compact(
            'region',
            'regions',
            'regionNames',
            'regionName',
            'comingSoonRegions',
            'communities',
            'communitiesByCluster',
            'clusters',
            'selectedClusterId',
            'selectedClusterCommunities',
            'selectedEnvironment',
            'classesByEnvironment',
            'stats',
        ));
    }
}
