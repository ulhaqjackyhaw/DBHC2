<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasAuditLog;

class DataKaryawan extends Model
{
    use HasFactory, HasAuditLog;

    protected $table = 'data_karyawan';

    protected $fillable = [
        'nik',
        'nama',
        'gender',
        'kode_jabatan',
        'lokasi',
        'unit',
        'jabatan',
        'kelompok_kelas_jabatan',
        'grade',
        'status_kepegawaian',
        'asal_instansi',
        'tanggal_lahir',
        'pendidikan_terakhir',
        'tmt',
    ];

    /**
     * Relationship dengan tabel formasi berdasarkan kode_jabatan
     */
    public function formasi()
    {
        return $this->belongsTo(Formasi::class, 'kode_jabatan', 'kode_jabatan');
    }

    /**
     * Scope untuk filter berdasarkan lokasi
     */
    public function scopeByLokasi($query, $lokasi)
    {
        return $query->where('lokasi', $lokasi);
    }

    /**
     * Scope untuk filter berdasarkan unit
     */
    public function scopeByUnit($query, $unit)
    {
        return $query->where('unit', $unit);
    }

    /**
     * Scope untuk filter berdasarkan status kepegawaian
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status_kepegawaian', $status);
    }

    /**
     * Scope untuk filter berdasarkan gender
     */
    public function scopeByGender($query, $gender)
    {
        return $query->where('gender', $gender);
    }
}
