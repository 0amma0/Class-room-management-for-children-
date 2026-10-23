<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\ClassGalleryPhoto;
use App\Support\GuruClassAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class GalleryController extends Controller
{
    public function index(Request $request, GuruClassAccess $access): View
    {
        $filters = $request->validate([
            'class_id' => ['nullable', 'integer'],
            'photo_date' => ['nullable', 'date'],
        ]);
        $classes = $access->classesFor($request->user())->orderBy('name')->get(['id', 'name']);
        if (isset($filters['class_id'])) {
            abort_unless($classes->contains('id', (int) $filters['class_id']), 404);
        }

        $photos = ClassGalleryPhoto::query()
            ->whereIn('class_id', $classes->modelKeys())
            ->when(isset($filters['class_id']), fn ($query) => $query->where('class_id', $filters['class_id']))
            ->when(isset($filters['photo_date']), fn ($query) => $query->whereDate('photo_date', $filters['photo_date']))
            ->with('class:id,name')
            ->orderByDesc('photo_date')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('guru.gallery', [
            'classes' => $classes,
            'photos' => $photos,
            'filters' => $filters,
        ]);
    }

    public function store(Request $request, GuruClassAccess $access): RedirectResponse
    {
        $data = $request->validate([
            'class_id' => ['required', 'integer'],
            'photo_date' => ['required', 'date'],
            'session_label' => ['nullable', 'string', 'max:100'],
            'caption' => ['nullable', 'string', 'max:255'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);
        $access->classesFor($request->user())->whereKey($data['class_id'])->firstOrFail();
        $path = $request->file('image')->store('class-gallery', 'local');
        if (! is_string($path) || ! Storage::disk('local')->exists($path)) {
            throw ValidationException::withMessages(['image' => 'Foto gagal disimpan. Silakan coba lagi.']);
        }

        try {
            DB::transaction(function () use ($data, $path, $request): void {
                ClassGalleryPhoto::query()->create([
                    'class_id' => $data['class_id'],
                    'uploaded_by' => $request->user()->id,
                    'photo_date' => $data['photo_date'],
                    'session_label' => $data['session_label'] ?? null,
                    'caption' => $data['caption'] ?? null,
                    'file_path' => $path,
                ]);
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);

            throw $exception;
        }

        return redirect()->route('guru.gallery')->with('success', 'Foto berhasil ditambahkan ke galeri kelas.');
    }

    public function file(Request $request, ClassGalleryPhoto $photo, GuruClassAccess $access): StreamedResponse
    {
        abort_unless($access->classesFor($request->user())->whereKey($photo->class_id)->exists(), 404);
        abort_unless(Storage::disk('local')->exists($photo->file_path), 404);

        return Storage::disk('local')->response($photo->file_path, basename($photo->file_path), [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
