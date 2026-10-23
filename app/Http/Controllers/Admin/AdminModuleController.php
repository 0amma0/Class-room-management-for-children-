<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\Cluster;
use App\Models\Environment;
use App\Models\Grade;
use App\Models\Region;
use App\Models\Teacher;
use Illuminate\View\View;

class AdminModuleController extends Controller
{
    public function regions(): View
    {
        $regions = Region::withCount('clusters')->latest()->paginate(15);

        return view('admin.regions.index', compact('regions'));
    }

    public function clusters(): View
    {
        $clusters = Cluster::with(['region'])->withCount('environments')->latest()->paginate(15);
        $regions = Region::orderBy('name')->get(['id', 'name']);

        return view('admin.clusters.index', compact('clusters', 'regions'));
    }

    public function environments(): View
    {
        $environments = Environment::with(['cluster.region'])->withCount('classes')->latest()->paginate(15);
        $clusters = Cluster::orderBy('name')->get(['id', 'name']);

        return view('admin.environments.index', compact('environments', 'clusters'));
    }

    public function classes(): View
    {
        $classes = Classes::with(['environment'])->withCount('students')->latest()->paginate(15);
        $environments = Environment::orderBy('name')->get(['id', 'name']);
        $grades = Grade::orderBy('sort_order')->orderBy('name')->get(['id', 'name']);

        return view('admin.classes.index', compact('classes', 'environments', 'grades'));
    }

    public function teachers(): View
    {
        $teachers = Teacher::with(['user'])->latest()->paginate(15);

        return view('admin.teachers.index', compact('teachers'));
    }
}
