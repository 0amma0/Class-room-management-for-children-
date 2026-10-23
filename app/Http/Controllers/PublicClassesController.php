<?php

namespace App\Http\Controllers;

use App\Models\Classes;
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

        return view('public.classes', compact('classes'));
    }

    public function module(): BinaryFileResponse
    {
        $path = base_path('docs/modul buku 3.pdf');
        abort_unless(is_file($path), 404, 'Modul Buku 3 tidak ditemukan.');

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="modul-buku-3.pdf"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function downloadModule(): BinaryFileResponse
    {
        $path = base_path('docs/modul buku 3.pdf');
        abort_unless(is_file($path), 404, 'Modul Buku 3 tidak ditemukan.');

        return response()->download($path, 'modul-buku-3.pdf', [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
