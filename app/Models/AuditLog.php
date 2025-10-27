<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'action',
        'model_type',
        'model_id',
        'model_identifier',
        'old_values',
        'new_values',
        'changes',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'changes' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get human-readable action name
     */
    public function getActionNameAttribute()
    {
        return match ($this->action) {
            'created' => 'Menambah Data',
            'updated' => 'Mengubah Data',
            'deleted' => 'Menghapus Data',
            'restored' => 'Memulihkan Data',
            default => ucfirst($this->action),
        };
    }

    /**
     * Get model name in Indonesian
     */
    public function getModelNameAttribute()
    {
        return match ($this->model_type) {
            'App\\Models\\DataKaryawan' => 'Data Karyawan',
            'App\\Models\\Formasi' => 'Formasi',
            'App\\Models\\Realisasi' => 'Realisasi',
            'App\\Models\\User' => 'User',
            default => class_basename($this->model_type),
        };
    }

    /**
     * Get badge color based on action
     */
    public function getActionBadgeColorAttribute()
    {
        return match ($this->action) {
            'created' => 'success',
            'updated' => 'warning',
            'deleted' => 'danger',
            'restored' => 'info',
            default => 'secondary',
        };
    }

    /**
     * Scope untuk filter berdasarkan model type
     */
    public function scopeForModel($query, $modelType)
    {
        return $query->where('model_type', $modelType);
    }

    /**
     * Scope untuk filter berdasarkan action
     */
    public function scopeForAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope untuk filter berdasarkan user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope untuk filter berdasarkan tanggal
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get formatted changes summary
     */
    public function getChangesSummary()
    {
        $changes = $this->getAttribute('changes');

        if (empty($changes) || !is_array($changes)) {
            return [];
        }

        // Field yang tidak perlu ditampilkan sama sekali
        $excludedFields = ['updated_at', 'remember_token'];

        // Field sensitif yang perlu disembunyikan nilainya
        $sensitiveFields = ['password'];

        $summary = [];
        foreach ($changes as $field => $change) {
            // Skip field yang di-exclude
            if (in_array($field, $excludedFields)) {
                continue;
            }

            if (!is_array($change)) {
                continue;
            }

            // Untuk field sensitif, tampilkan hanya info bahwa field diubah
            if (in_array($field, $sensitiveFields)) {
                $summary[] = [
                    'field' => $this->getFieldLabel($field),
                    'old' => '••••••••',
                    'new' => '••••••••',
                    'is_sensitive' => true,
                ];
            } else {
                $summary[] = [
                    'field' => $this->getFieldLabel($field),
                    'old' => $change['old'] ?? '-',
                    'new' => $change['new'] ?? '-',
                    'is_sensitive' => false,
                ];
            }
        }

        return $summary;
    }    /**
         * Get field label in Indonesian
         */
    private function getFieldLabel($field)
    {
        $labels = [
            // DataKaryawan fields
            'nipp' => 'NIPP',
            'nama' => 'Nama',
            'jenis_kelamin' => 'Jenis Kelamin',
            'tanggal_lahir' => 'Tanggal Lahir',
            'pendidikan_terakhir' => 'Pendidikan Terakhir',
            'kode_jabatan' => 'Kode Jabatan',
            'jabatan' => 'Jabatan',
            'unit' => 'Unit',
            'lokasi' => 'Lokasi',
            'status_pegawai' => 'Status Pegawai',
            'tanggal_masuk' => 'Tanggal Masuk',

            // Formasi fields
            'kuota' => 'Kuota',
            'level' => 'Level',

            // Realisasi fields
            'program_kerja' => 'Program Kerja',
            'tahun' => 'Tahun',
            'rkap' => 'RKAP',
            'realisasi_s1' => 'Realisasi S1',
            'realisasi_ytd' => 'Realisasi YTD',
            'outlook' => 'Outlook',

            // User fields
            'name' => 'Nama',
            'email' => 'Email',
            'role' => 'Role',
            'password' => 'Password',
        ];

        return $labels[$field] ?? ucwords(str_replace('_', ' ', $field));
    }
}
