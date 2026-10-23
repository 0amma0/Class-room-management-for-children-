<?php

namespace App\Console\Commands;

use App\Actions\DatabaseBackupManager;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:db-backup')]
#[Description('Create a private SQL backup of the configured MySQL database')]
class DbBackup extends Command
{
    public function handle(DatabaseBackupManager $backupManager): int
    {
        $filename = $backupManager->backup();
        $this->info('Database backup created: '.$filename);

        return self::SUCCESS;
    }
}
