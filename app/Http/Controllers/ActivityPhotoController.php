<?php

namespace App\Http\Controllers;

use App\Models\ActivityPhoto;
use App\Support\GuruClassAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActivityPhotoController extends Controller
{
    public function show(Request $request, ActivityPhoto $photo, GuruClassAccess $access): StreamedResponse
    {
        $activity = $photo->activity()->firstOrFail();
        $user = $request->user();
        $isPubliclyApproved = $activity->status === 'published' && $photo->is_approved;
        $isAdmin = $user !== null && $user->status === 'active' && $user->isAdmin();
        $isAssignedTeacher = $user !== null
            && $user->status === 'active'
            && $user->isGuru()
            && $activity->scope_type === 'class'
            && (int) $activity->created_by === (int) $user->id
            && $access->classesFor($user)->whereKey($activity->class_id)->exists();

        abort_unless($isPubliclyApproved || $isAdmin || $isAssignedTeacher, 404);
        abort_unless(
            Str::startsWith($photo->file_url, 'activity-photos/')
                && ! Str::contains($photo->file_url, ['../', '..\\']),
            404,
        );

        $disk = Storage::disk('local');
        if (! $disk->exists($photo->file_url)) {
            $disk = Storage::disk('public');
        }

        abort_unless($disk->exists($photo->file_url), 404);

        $mimeType = $disk->mimeType($photo->file_url);
        abort_unless(in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true), 404);

        return $disk->response($photo->file_url, basename($photo->file_url), [
            'Cache-Control' => 'private, no-store',
            'Content-Type' => $mimeType,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
