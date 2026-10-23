<?php

namespace App\Console\Commands;

use App\Actions\DatabaseBackupManager;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('app:db-restore {file : The .sql backup filename in private storage} {--force : Skip the restore confirmation}')]
#[Description('Restore a private SQL backup into the configured MySQL database')]
class DbRestore extends Command
{
    public function handle(DatabaseBackupManager $backupManager): int
    {
        $filename = (string) $this->argument('file');

        if (basename($filename) !== $filename || ! str_ends_with(strtolower($filename), '.sql') || ! Storage::disk('local')->exists('backups/'.$filename)) {
            $this->error('Choose an existing .sql backup from the private backups directory.');

            return self::FAILURE;
        }

        if (app()->isProduction() && ! $this->option('force')) {
            $this->error('In production, restore requires the explicit --force option.');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('Restore will replace data in the configured database. Continue?')) {
            $this->warn('Restore cancelled.');

            return self::FAILURE;
        }

        $recoveryBackup = $backupManager->backup();
        $this->info('Recovery backup created: '.$recoveryBackup);

        $backupManager->restore($filename);
        $this->info('Database restore completed.');

        return self::SUCCESS;
    }
}
