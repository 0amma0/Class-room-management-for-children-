<?php

namespace App\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PDO;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class DatabaseBackupManager
{
    public function backup(): string
    {
        $connection = $this->mysqlConnection();
        $directory = 'backups';
        Storage::disk('local')->makeDirectory($directory);

        $filename = 'backup-'.now()->format('Ymd-His').'-'.Str::lower(Str::random(6)).'.sql';
        $temporaryPath = Storage::disk('local')->path($directory.'/'.$filename.'.partial');
        $stream = fopen($temporaryPath, 'xb');

        if ($stream === false) {
            throw new RuntimeException('Unable to create the private database backup file.');
        }

        $completed = false;

        try {
            $this->writeDump($stream, $connection);

            if (filesize($temporaryPath) === 0) {
                throw new RuntimeException('Database backup returned an empty dump.');
            }

            $completed = true;
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }

            if (! $completed && file_exists($temporaryPath)) {
                unlink($temporaryPath);
            }
        }

        Storage::disk('local')->move($directory.'/'.$filename.'.partial', $directory.'/'.$filename);

        return $filename;
    }

    public function restore(string $filename): void
    {
        if (basename($filename) !== $filename || ! str_ends_with(strtolower($filename), '.sql')) {
            throw new RuntimeException('Provide the name of a .sql backup file stored by this application.');
        }

        $relativePath = 'backups/'.$filename;
        $storage = Storage::disk('local');

        if (! $storage->exists($relativePath) || $storage->size($relativePath) === 0) {
            throw new RuntimeException('The requested backup does not exist or is empty.');
        }

        $connection = $this->mysqlConnection();
        $stream = fopen($storage->path($relativePath), 'rb');

        if ($stream === false) {
            throw new RuntimeException('Unable to read the selected database backup.');
        }

        try {
            $process = new Process(
                $this->restoreCommand($connection),
                base_path(),
                $this->processEnvironment($connection),
                input: $stream,
                timeout: (float) config('backup.timeout', 900),
            );
            $process->run();

            if (! $process->isSuccessful()) {
                throw new RuntimeException('Database restore failed: '.trim($process->getErrorOutput()));
            }
        } finally {
            fclose($stream);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function mysqlConnection(): array
    {
        $connection = config('database.connections.'.DB::getDefaultConnection());

        if (! in_array($connection['driver'] ?? null, ['mysql', 'mariadb'], true)) {
            throw new RuntimeException('Database backup and restore currently require a MySQL or MariaDB connection.');
        }

        foreach (['database', 'username', 'host', 'port'] as $key) {
            if (blank($connection[$key] ?? null)) {
                throw new RuntimeException("The database connection is missing its {$key} setting.");
            }
        }

        return $connection;
    }

    /**
     * @param  resource  $stream
     * @param  array<string, mixed>  $connection
     */
    private function writeDump($stream, array $connection): void
    {
        $pdo = DB::connection()->getPdo();
        $this->writeLine($stream, '-- Database backup created at '.now()->toIso8601String());
        $this->writeLine($stream, 'SET NAMES utf8mb4;');
        $this->writeLine($stream, 'SET FOREIGN_KEY_CHECKS=0;');

        if ($pdo->inTransaction()) {
            throw new RuntimeException('A database backup cannot start while another transaction is active.');
        }

        $pdo->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
        $pdo->exec('START TRANSACTION WITH CONSISTENT SNAPSHOT');

        try {
            $tables = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")
                ->fetchAll(PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                $quotedTable = $this->quoteIdentifier((string) $table);
                $definition = $pdo->query("SHOW CREATE TABLE {$quotedTable}")->fetch(PDO::FETCH_ASSOC);
                $createTable = $definition['Create Table'] ?? null;
                if (! is_string($createTable)) {
                    throw new RuntimeException("Unable to read the definition for table {$table}.");
                }

                $this->writeLine($stream, "DROP TABLE IF EXISTS {$quotedTable};");
                $this->writeLine($stream, $createTable.';');

                $columns = $pdo->query("SHOW COLUMNS FROM {$quotedTable}")->fetchAll(PDO::FETCH_ASSOC);
                $columnNames = array_map(fn (array $column): string => $this->quoteIdentifier($column['Field']), $columns);
                $binaryColumns = [];
                foreach ($columns as $column) {
                    if (preg_match('/(binary|blob)/i', (string) $column['Type']) === 1) {
                        $binaryColumns[$column['Field']] = true;
                    }
                }

                $rows = $pdo->query("SELECT * FROM {$quotedTable}");
                while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
                    $values = [];
                    foreach ($row as $column => $value) {
                        if ($value === null) {
                            $values[] = 'NULL';
                        } elseif (isset($binaryColumns[$column])) {
                            $values[] = '0x'.bin2hex((string) $value);
                        } else {
                            $quotedValue = $pdo->quote((string) $value);
                            if ($quotedValue === false) {
                                throw new RuntimeException("Unable to encode a value in table {$table}.");
                            }
                            $values[] = $quotedValue;
                        }
                    }

                    $this->writeLine(
                        $stream,
                        "INSERT INTO {$quotedTable} (".implode(', ', $columnNames).') VALUES ('.implode(', ', $values).');',
                    );
                }
            }

            $this->writeRoutines($stream, $pdo, $connection['database']);
            $this->writeTriggers($stream, $pdo);
            $this->writeLine($stream, 'SET FOREIGN_KEY_CHECKS=1;');
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
    }

    /**
     * @param  resource  $stream
     */
    private function writeRoutines($stream, PDO $pdo, string $database): void
    {
        $statement = $pdo->prepare('SELECT ROUTINE_NAME, ROUTINE_TYPE FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = ?');
        $statement->execute([$database]);

        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $routine) {
            $name = $this->quoteIdentifier($routine['ROUTINE_NAME']);
            $type = strtoupper((string) $routine['ROUTINE_TYPE']);
            if (! in_array($type, ['PROCEDURE', 'FUNCTION'], true)) {
                continue;
            }
            $definition = $pdo->query("SHOW CREATE {$type} {$name}")->fetch(PDO::FETCH_ASSOC);
            $createRoutine = $definition['Create '.ucfirst(strtolower($type))] ?? null;
            if (! is_string($createRoutine)) {
                throw new RuntimeException("Unable to read the {$type} definition for {$routine['ROUTINE_NAME']}.");
            }

            $this->writeLine($stream, "DROP {$type} IF EXISTS {$name};");
            $this->writeDelimitedDefinition($stream, $createRoutine);
        }
    }

    /**
     * @param  resource  $stream
     */
    private function writeTriggers($stream, PDO $pdo): void
    {
        foreach ($pdo->query('SHOW TRIGGERS')->fetchAll(PDO::FETCH_ASSOC) as $trigger) {
            $name = $this->quoteIdentifier($trigger['Trigger']);
            $definition = $pdo->query("SHOW CREATE TRIGGER {$name}")->fetch(PDO::FETCH_ASSOC);
            $createTrigger = $definition['SQL Original Statement'] ?? $definition['Create Trigger'] ?? null;
            if (! is_string($createTrigger)) {
                throw new RuntimeException("Unable to read the trigger definition for {$trigger['Trigger']}.");
            }

            $this->writeLine($stream, "DROP TRIGGER IF EXISTS {$name};");
            $this->writeDelimitedDefinition($stream, $createTrigger);
        }
    }

    /**
     * @param  resource  $stream
     */
    private function writeDelimitedDefinition($stream, string $definition): void
    {
        $this->writeLine($stream, 'DELIMITER $$');
        $this->writeLine($stream, rtrim($definition, ';').'$$');
        $this->writeLine($stream, 'DELIMITER ;');
    }

    /**
     * @param  resource  $stream
     */
    private function writeLine($stream, string $line): void
    {
        if (fwrite($stream, $line.PHP_EOL) !== strlen($line.PHP_EOL)) {
            throw new RuntimeException('Unable to write the database dump to storage.');
        }
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '`'.str_replace('`', '``', $identifier).'`';
    }

    /**
     * @param  array<string, mixed>  $connection
     * @return list<string>
     */
    private function restoreCommand(array $connection): array
    {
        return [
            config('backup.mysql_binary', 'mysql'),
            '--default-character-set=utf8mb4',
            '--host='.$connection['host'],
            '--port='.$connection['port'],
            '--user='.$connection['username'],
            $connection['database'],
        ];
    }

    /**
     * @param  array<string, mixed>  $connection
     * @return array<string, string>
     */
    private function processEnvironment(array $connection): array
    {
        return ['MYSQL_PWD' => (string) ($connection['password'] ?? '')];
    }
}
