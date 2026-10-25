<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicMediaController extends Controller
{
    public function show(string $path): StreamedResponse
    {
        abort_unless(
            preg_match('/\A(?:activity-cards|avatars|class-photos)\/[A-Za-z0-9._-]+\z/', $path) === 1
                && ! str_contains($path, '..'),
            404,
        );

        $disk = Storage::disk('public');
        abort_unless($disk->exists($path), 404);

        $mimeType = $disk->mimeType($path);
        abort_unless(
            in_array($mimeType, ['image/avif', 'image/gif', 'image/jpeg', 'image/png', 'image/webp'], true),
            404,
        );

        return $disk->response($path, basename($path), [
            'Cache-Control' => 'public, max-age=86400',
            'Content-Type' => $mimeType,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
