<?php

namespace App\Http\Controllers\API;

use App\Jobs\RunBackupJob;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function createBackup(Request $request)
    {
        $request->validate([
            'flag' => 'required|string|in:full,db,files',
        ]);
        try {
            $flag = $request->input('flag');
            if ($flag === 'db') {
                Artisan::call('backup:run', ['--only-db' => true]);
                return response([
                    'message' => 'DB Backup created successfully.',
                    'output' => Artisan::output()
                ], 200);
            }
            if ($flag === 'files') {
                Artisan::call('backup:run', ['--only-files' => true]);
                return response([
                    'message' => 'Files Backup created successfully.',
                    'output' => Artisan::output()
                ], 200);
            }
            if ($flag === 'full') {
                Artisan::call('backup:run');
                return response([
                    'message' => 'Full Backup created successfully.',
                    'output' => Artisan::output()
                ], 200);
            }
            // RunBackupJob::dispatch($flag);
            // return response([
            //     'message' => 'Backup job dispatched successfully. It will run in the background.'
            // ], 202);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function listBackups()
    {
        try {
            $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
            $backupName = config('backup.backup.name');

            $files = $disk->files($backupName);
            $backups = collect($files)->map(function ($file) use ($disk) {
                return [
                    'name' => basename($file),
                    'size' => $disk->size($file),
                    'date' => $disk->lastModified($file),
                    'size_human' => $this->formatBytes($disk->size($file)),
                    'date_human' => date('Y-m-d H:i:s', $disk->lastModified($file)),
                ];
            })->sortByDesc('date')->values();

            return response([
                'backups' => $backups,
                'total' => $backups->count()
            ], 200);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function downloadBackup($filename)
    {
        try {
            $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
            $backupName = config('backup.backup.name');
            $path = $backupName . '/' . $filename;

            if (!$disk->exists($path)) {
                return response([
                    'message' => 'Backup file not found.'
                ], 404);
            }

            return $disk->download($path);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function deleteBackup($filename)
    {
        try {
            $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
            $backupName = config('backup.backup.name');
            $path = $backupName . '/' . $filename;

            if (!$disk->exists($path)) {
                return response([
                    'message' => 'Backup file not found.'
                ], 404);
            }

            $disk->delete($path);

            return response([
                'message' => 'Backup deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
