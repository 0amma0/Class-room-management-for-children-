<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\LearningMaterial;
use App\Models\PublicDownloadRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\RedirectResponse;

class LearningMaterialController extends Controller
{
    public function staffIndex(Request $request): View
    {
        abort_unless($request->user()?->isAdmin() || $request->user()?->isGuru(), 403);

        $materials = LearningMaterial::query()
            ->where('is_active', true)
            ->orderByRaw("CASE type WHEN 'lesson' THEN 1 WHEN 'song' THEN 2 WHEN 'image' THEN 3 ELSE 4 END")
            ->orderBy('title')
            ->get();

        return view('materials.staff-index', [
            'materials' => $materials,
            'isAdmin' => false,
        ]);
    }

    public function index(): View
    {
        $materials = LearningMaterial::query()
            ->where('is_active', true)
            ->orderByRaw("CASE type WHEN 'lesson' THEN 1 WHEN 'song' THEN 2 WHEN 'image' THEN 3 ELSE 4 END")
            ->orderBy('title')
            ->get();

        return view('public.materials', compact('materials'));
    }

    public function requestAccess(Request $request, LearningMaterial $learningMaterial): RedirectResponse
    {
        abort_unless($learningMaterial->is_active, 404);

        $data = $request->validate([
            'requester_name' => ['required', 'string', 'max:255'],
            'requester_email' => ['required', 'email', 'max:255'],
        ]);
        $accessToken = Str::random(64);

        DB::transaction(function () use ($data, $accessToken, $learningMaterial): void {
            $downloadRequest = PublicDownloadRequest::query()->create([
                'learning_material_id' => $learningMaterial->id,
                'requester_name' => $data['requester_name'],
                'requester_email' => $data['requester_email'],
                'access_token_hash' => hash('sha256', $accessToken),
            ]);

            $admins = User::query()
                ->where('role', 'admin')
                ->where('status', 'active')
                ->get(['id']);

            foreach ($admins as $admin) {
                AppNotification::query()->create([
                    'user_id' => $admin->id,
                    'type' => 'download_request',
                    'public_download_request_id' => $downloadRequest->id,
                    'title' => 'Permintaan unduh materi baru',
                    'message' => $data['requester_name'].' meminta akses ke '.$learningMaterial->title.'.',
                ]);
            }
        });

        return redirect()
            ->route('learning-materials.requests.show', $accessToken)
            ->with('success', 'Permintaan akses terkirim. Simpan tautan status ini untuk memeriksa persetujuan admin.');
    }

    public function showRequest(string $token): View
    {
        $downloadRequest = $this->requestForToken($token);

        return view('public.material-request', compact('downloadRequest'));
    }

    public function downloadRequest(string $token): Response
    {
        $downloadRequest = $this->requestForToken($token);
        abort_unless($downloadRequest->status === 'approved', 403, 'Permintaan unduh belum disetujui.');
        abort_unless($downloadRequest->learningMaterial->is_active, 404);

        return $this->deliver($downloadRequest->learningMaterial);
    }

    public function staffDownload(Request $request, LearningMaterial $learningMaterial): Response
    {
        abort_unless($request->user()?->isAdmin() || $request->user()?->isGuru(), 403);
        abort_unless($learningMaterial->is_active, 404);

        return $this->deliver($learningMaterial);
    }

    private function requestForToken(string $token): PublicDownloadRequest
    {
        return PublicDownloadRequest::query()
            ->with('learningMaterial')
            ->where('access_token_hash', hash('sha256', $token))
            ->firstOrFail();
    }

    private function deliver(LearningMaterial $learningMaterial): Response
    {
        if (filled($learningMaterial->external_url)) {
            $url = $learningMaterial->external_url;
            abort_unless(
                filter_var($url, FILTER_VALIDATE_URL)
                    && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true),
                404,
            );

            return redirect()->away($url);
        }

        if ($learningMaterial->slug === '24-lesson') {
            $path = base_path('docs/24_lesson.pdf');
            abort_unless(is_file($path), 404, 'Dokumen 24 lesson tidak ditemukan.');

            return response()->download($path, '24_lesson.pdf', ['X-Content-Type-Options' => 'nosniff']);
        }

        abort_unless(filled($learningMaterial->file_path), 404, 'File materi tidak tersedia.');
        abort_unless(Storage::disk('local')->exists($learningMaterial->file_path), 404, 'File materi tidak ditemukan.');

        return Storage::disk('local')->download(
            $learningMaterial->file_path,
            basename($learningMaterial->file_path),
            ['X-Content-Type-Options' => 'nosniff'],
        );
    }
}
