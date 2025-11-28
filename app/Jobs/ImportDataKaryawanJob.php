<?php

namespace App\Jobs;

use App\Imports\DataKaryawanImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class ImportDataKaryawanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $sessionId;
    protected $mode; // 'add' or 'replace'

    public function __construct($filePath, $sessionId, $mode = 'add')
    {
        $this->filePath = $filePath;
        $this->sessionId = $sessionId;
        $this->mode = $mode;
    }

    public function handle()
    {
        try {
            // Initialize progress
            Cache::put("import_progress_{$this->sessionId}", [
                'progress' => 0,
                'status' => 'processing',
                'message' => 'Memulai import...'
            ], 3600);

            if ($this->mode === 'replace') {
                Cache::put("import_progress_{$this->sessionId}", [
                    'progress' => 10,
                    'status' => 'processing',
                    'message' => 'Menghapus data lama...'
                ], 3600);

                \App\Models\DataKaryawan::query()->delete();
            }

            Cache::put("import_progress_{$this->sessionId}", [
                'progress' => 20,
                'status' => 'processing',
                'message' => 'Membaca file Excel...'
            ], 3600);

            // Track count sebelum import
            $countBefore = \App\Models\DataKaryawan::count();

            $import = new DataKaryawanImport($this->sessionId);
            Excel::import($import, $this->filePath);

            // Track count setelah import
            $countAfter = \App\Models\DataKaryawan::count();
            $imported = $countAfter - $countBefore;

            $skipped = $import->getSkippedCount();
            $message = $this->mode === 'replace'
                ? 'Semua data berhasil diganti.'
                : 'Data berhasil ditambahkan.';

            if ($skipped > 0) {
                $message .= " ({$skipped} baris dilewati karena NIK duplikat atau kosong)";
            }

            // Catat ke audit log
            if (\Auth::check()) {
                \App\Models\AuditLog::create([
                    'user_id' => \Auth::id(),
                    'user_name' => \Auth::user()->name,
                    'user_email' => \Auth::user()->email,
                    'action' => $this->mode === 'replace' ? 'bulk_replaced' : 'bulk_created',
                    'model_type' => 'App\\Models\\DataKaryawan',
                    'model_id' => null,
                    'model_identifier' => 'Bulk Import - ' . basename($this->filePath),
                    'old_values' => $this->mode === 'replace' ? ['total_records' => $countBefore] : null,
                    'new_values' => [
                        'imported' => $imported,
                        'skipped' => $skipped,
                        'total_after' => $countAfter,
                        'mode' => $this->mode,
                        'filename' => basename($this->filePath)
                    ],
                    'changes' => null,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            Cache::put("import_progress_{$this->sessionId}", [
                'progress' => 100,
                'status' => 'completed',
                'message' => $message,
                'skipped' => $skipped
            ], 3600);

        } catch (\Exception $e) {
            \Log::error("Import Job Error: " . $e->getMessage());

            Cache::put("import_progress_{$this->sessionId}", [
                'progress' => 0,
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 3600);
        } finally {
            // Cleanup file
            if (file_exists($this->filePath)) {
                @unlink($this->filePath);
            }
        }
    }
}
