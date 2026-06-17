<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--keep=7 : Number of days to keep backups}';

    protected $description = 'Backup the PostgreSQL database and store to storage disk';

    public function handle(): int
    {
        $connection = config('database.connections.pgsql');
        $database = $connection['database'];
        $host = $connection['host'];
        $port = $connection['port'] ?? '5432';
        $username = $connection['username'];
        $password = $connection['password'];

        $filename = 'backup-' . now()->format('Y-m-d_H-i-s') . '.sql.gz';
        $tempPath = sys_get_temp_dir() . '/' . $filename;

        $this->info("Backing up database '{$database}'...");

        $command = sprintf(
            'PGPASSWORD=%s pg_dump -h %s -p %s -U %s -d %s --no-owner --no-acl | gzip > %s 2>&1',
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($tempPath)
        );

        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            $this->error('Backup failed: ' . implode("\n", $output));
            return self::FAILURE;
        }

        if (!file_exists($tempPath) || filesize($tempPath) === 0) {
            $this->error('Backup file is empty or missing');
            return self::FAILURE;
        }

        $disk = config('filesystems.default');

        Storage::disk($disk)->put("backups/{$filename}", fopen($tempPath, 'r'), 'private');
        unlink($tempPath);

        $size = Storage::disk($disk)->size("backups/{$filename}");
        $this->info('Backup stored: backups/' . $filename . ' (' . $this->formatBytes($size) . ')');

        $this->cleanOldBackups($disk);

        return self::SUCCESS;
    }

    protected function cleanOldBackups(string $disk): void
    {
        $keepDays = (int) $this->option('keep');
        $cutoff = now()->subDays($keepDays);
        $deleted = 0;

        foreach (Storage::disk($disk)->files('backups') as $file) {
            $lastModified = Storage::disk($disk)->lastModified($file);
            if ($lastModified < $cutoff->timestamp) {
                Storage::disk($disk)->delete($file);
                $deleted++;
            }
        }

        if ($deleted > 0) {
            $this->info("Cleaned up {$deleted} old backup(s).");
        }
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
