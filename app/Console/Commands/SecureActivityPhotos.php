<?php

namespace App\Console\Commands;

use App\Models\ActivityPhoto;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Signature('app:secure-activity-photos')]
#[Description('Move legacy activity photos from public storage to private storage')]
class SecureActivityPhotos extends Command
{
    public function handle(): int
    {
        $publicDisk = Storage::disk('public');
        $privateDisk = Storage::disk('local');
        $photos = ActivityPhoto::query()
            ->where('file_url', 'like', 'activity-photos/%')
            ->get(['id', 'file_url']);
        $movedCount = 0;
        $failureCount = 0;

        foreach ($photos as $photo) {
            $path = $photo->file_url;

            if (! Str::startsWith($path, 'activity-photos/') || Str::contains($path, ['../', '..\\'])) {
                $this->error("Skipped unsafe path for activity photo {$photo->id}.");
                $failureCount++;

                continue;
            }

            if (! $publicDisk->exists($path)) {
                if (! $privateDisk->exists($path)) {
                    $this->error("Activity photo {$photo->id} is missing from both storage disks.");
                    $failureCount++;
                }

                continue;
            }

            $stream = $publicDisk->readStream($path);
            if (! is_resource($stream)) {
                $this->error("Unable to read legacy activity photo {$photo->id}.");
                $failureCount++;

                continue;
            }

            try {
                $stored = $privateDisk->writeStream($path, $stream);
            } finally {
                fclose($stream);
            }

            if (! $stored) {
                $this->error("Unable to write private activity photo {$photo->id}.");
                $failureCount++;

                continue;
            }

            if (! $publicDisk->delete($path)) {
                $this->error("Private copy created, but public activity photo {$photo->id} could not be removed.");
                $failureCount++;

                continue;
            }

            $movedCount++;
        }

        $this->info("Moved {$movedCount} activity photo(s) to private storage.");

        return $failureCount === 0 ? self::SUCCESS : self::FAILURE;
    }
}
