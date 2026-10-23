<?php

namespace App\Http\Controllers\Admin;

use App\Actions\DatabaseBackupManager;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Classes;
use App\Models\Cluster;
use App\Models\Environment;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function index(): View
    {
        $disk = Storage::disk('local');
        $backups = collect($disk->files('backups'))
            ->filter(fn (string $path): bool => str_ends_with(strtolower($path), '.sql'))
            ->map(fn (string $path): array => [
                'filename' => basename($path),
                'size' => $disk->size($path),
                'created_at' => $disk->lastModified($path),
            ])
            ->sortByDesc('created_at')
            ->values();

        return view('admin.backups.index', [
            'backups' => $backups,
            'regions' => Region::query()->orderBy('name')->get(['id', 'name']),
            'clusters' => Cluster::query()->orderBy('name')->get(['id', 'name']),
            'environments' => Environment::query()->orderBy('name')->get(['id', 'name']),
            'classes' => Classes::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request, DatabaseBackupManager $backupManager): RedirectResponse
    {
        $filename = $backupManager->backup();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'created',
            'entity_type' => 'database_backup',
            'meta' => ['filename' => $filename],
            'ip' => $request->ip(),
        ]);

        return redirect()->route('admin.backups')->with('success', 'Backup database berhasil dibuat.');
    }

    public function download(string $filename): StreamedResponse
    {
        if (basename($filename) !== $filename || ! str_ends_with(strtolower($filename), '.sql')) {
            abort(404);
        }

        $path = 'backups/'.$filename;
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, $filename, [
            'Content-Type' => 'application/sql',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
