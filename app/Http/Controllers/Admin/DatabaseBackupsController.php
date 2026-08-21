<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateDatabaseBackupJob;
use App\Jobs\RestoreDatabaseBackupJob;
use App\Models\DatabaseBackup;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseBackupsController extends Controller
{
    public function index(Request $request)
    {
        $backups = DatabaseBackup::query()->orderByDesc('id')->paginate(15)->withQueryString();

        return view('admin.backups.index', [
            'backups' => $backups,
        ]);
    }

    public function generate(Request $request)
    {
        $disk = (string) config('backups.disk', 'local');
        $dir = rtrim((string) config('backups.dir', 'backups'), '/');

        $stamp = now()->format('Ymd_His');
        $ext = (bool) config('backups.zip_enabled', true) ? 'zip' : 'sql';
        $filename = 'backup_'.$stamp.'_'.Str::lower(Str::random(6)).'.'.$ext;
        $path = $dir.'/'.$filename;

        $backup = DatabaseBackup::query()->create([
            'filename' => $filename,
            'disk' => $disk,
            'path' => $path,
            'status' => 'pending',
            'created_by' => optional($request->user())->id,
        ]);

        ActivityLogger::log(
            $request,
            'admin_backup_generate_requested',
            'Backup solicitado: '.$filename,
            DatabaseBackup::class,
            (int) $backup->id
        );

        GenerateDatabaseBackupJob::dispatch((int) $backup->id, (int) optional($request->user())->id);

        return redirect()->route('admin.backups.index')->with('success', 'Backup em geração (background).');
    }

    public function generateFull(Request $request)
    {
        $disk = (string) config('backups.disk', 'local');
        $dir = rtrim((string) config('backups.dir', 'backups'), '/');

        $stamp = now()->format('Ymd_His');
        $filename = 'backup_completo_'.$stamp.'_'.Str::lower(Str::random(6)).'.sql';
        $path = $dir.'/'.$filename;

        $backup = DatabaseBackup::query()->create([
            'filename' => $filename,
            'disk' => $disk,
            'path' => $path,
            'status' => 'pending',
            'created_by' => optional($request->user())->id,
            'options' => [
                'mode' => 'migrate',
            ],
        ]);

        ActivityLogger::log(
            $request,
            'admin_backup_generate_full_requested',
            'Backup completo (migração) solicitado: '.$filename,
            DatabaseBackup::class,
            (int) $backup->id
        );

        GenerateDatabaseBackupJob::dispatch((int) $backup->id, (int) optional($request->user())->id);

        return redirect()->route('admin.backups.index')->with('success', 'Backup completo em geração (background).');
    }

    public function download(Request $request, DatabaseBackup $backup)
    {
        $disk = (string) ($backup->disk ?: config('backups.disk', 'local'));
        if (! Storage::disk($disk)->exists($backup->path)) {
            return redirect()->route('admin.backups.index')->with('error', 'Ficheiro de backup não encontrado.');
        }

        ActivityLogger::log(
            $request,
            'admin_backup_downloaded',
            'Backup baixado: '.$backup->filename,
            DatabaseBackup::class,
            (int) $backup->id
        );

        return Storage::disk($disk)->download($backup->path, $backup->filename);
    }

    public function restore(Request $request)
    {
        $data = $request->validate([
            'backup_file' => ['required', 'file', 'mimes:sql,zip', 'max:51200'],
        ]);

        $disk = (string) config('backups.disk', 'local');
        $dir = rtrim((string) config('backups.dir', 'backups'), '/');

        $file = $data['backup_file'];
        $origExt = strtolower((string) $file->getClientOriginalExtension());
        $stamp = now()->format('Ymd_His');
        $filename = 'restore_'.$stamp.'_'.Str::lower(Str::random(6)).'.'.$origExt;
        $path = $dir.'/uploads/'.$filename;

        Storage::disk($disk)->makeDirectory($dir.'/uploads');
        Storage::disk($disk)->putFileAs($dir.'/uploads', $file, $filename);

        $backup = DatabaseBackup::query()->create([
            'filename' => $filename,
            'disk' => $disk,
            'path' => $path,
            'status' => 'pending',
            'created_by' => optional($request->user())->id,
        ]);

        ActivityLogger::log(
            $request,
            'admin_backup_restore_requested',
            'Restauro solicitado: '.$filename,
            DatabaseBackup::class,
            (int) $backup->id
        );

        RestoreDatabaseBackupJob::dispatch((int) $backup->id, (int) optional($request->user())->id);

        return redirect()->route('admin.backups.index')->with('success', 'Restauro em processamento (background).');
    }
}
