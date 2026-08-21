<?php

namespace App\Jobs;

use App\Models\ActivityLog;
use App\Models\DatabaseBackup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use ZipArchive;

class RestoreDatabaseBackupJob implements ShouldQueue
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
        $disk = (string) ($backup->disk ?: config('backups.disk', 'local'));

        $backup->status = 'restoring';
        $backup->error_message = null;
        $backup->save();

        $conn = config('database.default');
        $cfg = config('database.connections.'.$conn);

        $database = (string) ($cfg['database'] ?? '');
        $username = (string) ($cfg['username'] ?? '');
        $password = (string) ($cfg['password'] ?? '');
        $host = (string) ($cfg['host'] ?? '127.0.0.1');
        $port = (string) ($cfg['port'] ?? '3306');

        $mysqlPath = $this->resolveExecutable((string) config('backups.mysql_path', 'mysql'), 'mysql.exe');

        $tmpSql = tempnam(sys_get_temp_dir(), 'bng_restore_');
        if ($tmpSql === false) {
            throw new \RuntimeException('Falha ao criar ficheiro temporário para restauro.');
        }
        $tmpSqlFile = $tmpSql.'.sql';
        @rename($tmpSql, $tmpSqlFile);

        try {
            $raw = Storage::disk($disk)->get($backup->path);

            $isZip = Str::endsWith(Str::lower($backup->filename), '.zip');
            if ($isZip) {
                $zipLocal = tempnam(sys_get_temp_dir(), 'bng_zip_');
                if ($zipLocal === false) {
                    throw new \RuntimeException('Falha ao criar ficheiro temporário para ZIP.');
                }
                file_put_contents($zipLocal, $raw);

                $zip = new ZipArchive();
                $res = $zip->open($zipLocal);
                if ($res !== true) {
                    throw new \RuntimeException('ZIP inválido para restauro.');
                }

                if ($zip->numFiles < 1) {
                    throw new \RuntimeException('ZIP vazio.');
                }

                $firstName = $zip->getNameIndex(0);
                $content = $zip->getFromIndex(0);
                $zip->close();
                @unlink($zipLocal);

                if ($content === false) {
                    throw new \RuntimeException('Não foi possível ler o SQL dentro do ZIP.');
                }

                file_put_contents($tmpSqlFile, $content);
            } else {
                file_put_contents($tmpSqlFile, $raw);
            }

            $cmd = [
                $mysqlPath,
                '--host='.$host,
                '--port='.$port,
                '--user='.$username,
                $database,
            ];

            $process = new Process($cmd);
            $process->setTimeout(600);
            if ($password !== '') {
                $process->setEnv(['MYSQL_PWD' => $password]);
            }

            $process->setInput(file_get_contents($tmpSqlFile));
            $process->mustRun();

            $backup->status = 'restored';
            $backup->restored_by = $this->requestedBy;
            $backup->restored_at = now();
            $backup->save();

            ActivityLog::query()->create([
                'user_id' => $this->requestedBy,
                'action_type' => 'admin_backup_restored',
                'description' => 'Backup restaurado: '.$backup->filename,
                'ip_address' => 'queue',
                'model_type' => DatabaseBackup::class,
                'model_id' => (int) $backup->id,
            ]);
        } catch (\Throwable $e) {
            $backup->status = 'failed';
            $backup->error_message = $e->getMessage();
            $backup->save();

            throw $e;
        } finally {
            @unlink($tmpSqlFile);
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
            return $configured !== '' ? $configured : 'mysql';
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
