<?php

namespace App\Jobs;

use App\Models\ActivityLog;
use App\Models\DatabaseBackup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use ZipArchive;

class GenerateDatabaseBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $backupId;
    public int $requestedBy;

    public function __construct(int $backupId, int $requestedBy)
    {
        $this->backupId = $backupId;
        $this->requestedBy = $requestedBy;
    }

    public function handle(): void
    {
        $backup = DatabaseBackup::query()->findOrFail($this->backupId);
        $disk = (string) config('backups.disk', 'local');

        $mode = (string) data_get($backup->options, 'mode', 'default');
        $isMigration = $mode === 'migrate';

        $backup->status = 'running';
        $backup->error_message = null;
        $backup->save();

        $conn = config('database.default');
        $cfg = config('database.connections.'.$conn);

        $database = (string) ($cfg['database'] ?? '');
        $username = (string) ($cfg['username'] ?? '');
        $password = (string) ($cfg['password'] ?? '');
        $host = (string) ($cfg['host'] ?? '127.0.0.1');
        $port = (string) ($cfg['port'] ?? '3306');

        $baseDir = rtrim((string) config('backups.dir', 'backups'), '/');
        $tmpName = 'tmp_'.Str::random(12).'.sql';
        $tmpPath = $baseDir.'/tmp/'.$tmpName;

        Storage::disk($disk)->makeDirectory($baseDir.'/tmp');

        $dumpPath = $this->resolveExecutable((string) config('backups.mysqldump_path', 'mysqldump'), 'mysqldump.exe');

        $args = [
            $dumpPath,
            '--single-transaction',
            '--quick',
            '--routines',
            '--triggers',
            '--events',
            '--skip-comments',
            '--host='.$host,
            '--port='.$port,
            '--user='.$username,
        ];

        if ($isMigration) {
            $args[] = '--databases';
            $args[] = '--add-drop-database';
        }

        $args[] = $database;

        $process = new Process($args);
        $process->setTimeout(300);
        if ($password !== '') {
            $process->setEnv(['MYSQL_PWD' => $password]);
        }

        try {
            $process->mustRun();

            Storage::disk($disk)->put($tmpPath, $process->getOutput());

            $finalPath = $backup->path;
            if (! $isMigration && (bool) config('backups.zip_enabled', true)) {
                $zipLocal = tempnam(sys_get_temp_dir(), 'bngbk_');
                if ($zipLocal === false) {
                    throw new \RuntimeException('Falha ao criar ficheiro temporário para compressão.');
                }

                $zip = new ZipArchive();
                $openRes = $zip->open($zipLocal, ZipArchive::OVERWRITE);
                if ($openRes !== true) {
                    throw new \RuntimeException('Falha ao criar ZIP de backup.');
                }

                $sqlContent = Storage::disk($disk)->get($tmpPath);
                $zip->addFromString(str_replace('.zip', '.sql', $backup->filename), $sqlContent);
                $zip->close();

                Storage::disk($disk)->put($finalPath, file_get_contents($zipLocal));
                @unlink($zipLocal);
                Storage::disk($disk)->delete($tmpPath);
            } else {
                Storage::disk($disk)->move($tmpPath, $finalPath);
            }

            $size = Storage::disk($disk)->size($finalPath);
            $backup->status = 'ready';
            $backup->size_bytes = $size;
            $backup->save();

            ActivityLog::query()->create([
                'user_id' => $this->requestedBy,
                'action_type' => 'admin_backup_generated',
                'description' => 'Backup gerado: '.$backup->filename,
                'ip_address' => 'queue',
                'model_type' => DatabaseBackup::class,
                'model_id' => (int) $backup->id,
            ]);
        } catch (\Throwable $e) {
            $backup->status = 'failed';
            $backup->error_message = $e->getMessage();
            $backup->save();

            try {
                Storage::disk($disk)->delete($tmpPath);
            } catch (\Throwable $ignored) {
            }

            throw $e;
        }
    }

    private function resolveExecutable(string $configured, string $windowsExeName): string
    {
        $configured = trim($configured);
        if ($configured !== '' && $configured !== 'mysqldump' && $configured !== 'mysql') {
            return $configured;
        }

        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        if (! $isWindows) {
            return $configured !== '' ? $configured : 'mysqldump';
        }

        $candidates = [
            'C:\\xampp\\mysql\\bin\\'.$windowsExeName,
            'C:\\xampp82\\mysql\\bin\\'.$windowsExeName,
            'C:\\xampp\\mariadb\\bin\\'.$windowsExeName,
            'C:\\xampp82\\mariadb\\bin\\'.$windowsExeName,
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return $windowsExeName;
    }
}
