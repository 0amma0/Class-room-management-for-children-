<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\LearningMaterial;
use App\Models\PublicDownloadRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LearningMaterialController extends Controller
{
    public function index(): View
    {
        $materials = LearningMaterial::query()
            ->with('uploader:id,name')
            ->orderByDesc('is_system')
            ->orderBy('title')
            ->get();
        $requests = PublicDownloadRequest::query()
            ->with('learningMaterial:id,title,type')
            ->latest()
            ->paginate(15);

        return view('materials.staff-index', [
            'materials' => $materials,
            'requests' => $requests,
            'isAdmin' => true,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['lesson', 'song', 'image', 'document'])],
            'description' => ['nullable', 'string', 'max:2000'],
            'file' => [
                'nullable',
                'required_without:external_url',
                'file',
                'mimes:pdf,doc,docx,jpg,jpeg,png,webp,mp3,m4a,wav',
                'max:15360',
            ],
            'external_url' => ['nullable', 'required_without:file', 'url:http,https', 'max:2000'],
        ]);
        if ($request->hasFile('file') && filled($data['external_url'] ?? null)) {
            throw ValidationException::withMessages([
                'external_url' => 'Pilih unggah file atau tautan eksternal, bukan keduanya.',
            ]);
        }

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('learning-materials', 'local');
            if (! is_string($filePath)) {
                throw ValidationException::withMessages(['file' => 'File materi gagal disimpan. Silakan coba lagi.']);
            }
        }

        LearningMaterial::query()->create([
            'slug' => Str::slug($data['title']).'-'.Str::lower(Str::random(8)),
            'type' => $data['type'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'file_path' => $filePath,
            'external_url' => $data['external_url'] ?? null,
            'is_system' => false,
            'is_active' => true,
            'uploaded_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.materials')->with('success', 'Materi berhasil ditambahkan.');
    }

    public function decide(Request $request, PublicDownloadRequest $downloadRequest): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', Rule::in(['approved', 'rejected'])],
            'review_note' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $downloadRequest, $data): void {
            $lockedRequest = PublicDownloadRequest::query()
                ->whereKey($downloadRequest->id)
                ->lockForUpdate()
                ->firstOrFail();
            if ($lockedRequest->status !== 'pending') {
                throw ValidationException::withMessages([
                    'decision' => 'Permintaan ini sudah diproses sebelumnya.',
                ]);
            }

            $lockedRequest->update([
                'status' => $data['decision'],
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'review_note' => $data['review_note'] ?? null,
            ]);
            $request->user()->appNotifications()
                ->where('public_download_request_id', $lockedRequest->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        });

        $message = $data['decision'] === 'approved'
            ? 'Permintaan unduh disetujui.'
            : 'Permintaan unduh ditolak.';

        return redirect()->route('admin.materials', ['request' => $downloadRequest->id])
            ->with('success', $message);
    }

    public function destroy(PublicDownloadRequest $downloadRequest): RedirectResponse
    {
        DB::transaction(function () use ($downloadRequest): void {
            AppNotification::query()
                ->where('public_download_request_id', $downloadRequest->id)
                ->delete();
            $downloadRequest->delete();
        });

        return redirect()->route('admin.materials')->with('success', 'Permintaan unduh berhasil dihapus.');
    }
}
