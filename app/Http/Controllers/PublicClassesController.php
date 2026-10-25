<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\LearningMaterial;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PublicClassesController extends Controller
{
    public function index(): View
    {
        $classes = Classes::query()
            ->where('status', 'active')
            ->with('environment:id,name')
            ->withCount(['students' => fn ($query) => $query->where('status', 'active')])
            ->orderBy('name')
            ->get(['id', 'environment_id', 'name', 'code', 'location', 'schedule', 'photo_path']);

        $bookModule = LearningMaterial::query()
            ->where('slug', 'modul-buku-3')
            ->where('is_active', true)
            ->first();

        return view('public.classes', compact('classes', 'bookModule'));
    }

    public function module(): BinaryFileResponse
    {
        abort(403, 'Ajukan izin Admin melalui halaman Classes sebelum membaca modul.');
    }

    public function downloadModule(): BinaryFileResponse
    {
        abort(403, 'Ajukan izin Admin melalui halaman Classes sebelum mengunduh modul.');
    }
}
