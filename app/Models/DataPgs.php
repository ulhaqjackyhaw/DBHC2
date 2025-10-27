<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasAuditLog;

class DataPgs extends Model
{
    use HasFactory, HasAuditLog;

    protected $table = 'data_pgs';

    protected $fillable = [
        'nik',
        'nama',
        'jabatan_definitif',
        'jabatan_pgs',
        'lokasi_unit_kerja',
        'tanggal_pgs',
        'tanggal_selesai_pgs',
    ];

    /**
     * Append accessors to JSON output
     */
    protected $appends = [
        'durasi_pgs',
        'tanggal_pgs_formatted',
        'tanggal_selesai_pgs_formatted',
        'is_overdue',
        'is_warning',
        'sisa_hari',
        'total_days',
    ];

    /**
     * Scope untuk filter berdasarkan lokasi unit kerja
     */
    public function scopeByLokasiUnit($query, $lokasiUnit)
    {
        return $query->where('lokasi_unit_kerja', $lokasiUnit);
    }

    /**
     * Scope untuk filter berdasarkan jabatan definitif
     */
    public function scopeByJabatanDefinitif($query, $jabatanDefinitif)
    {
        return $query->where('jabatan_definitif', $jabatanDefinitif);
    }

    /**
     * Scope untuk filter berdasarkan jabatan PGS
     */
    public function scopeByJabatanPgs($query, $jabatanPgs)
    {
        return $query->where('jabatan_pgs', $jabatanPgs);
    }

    /**
     * Menghitung durasi PGS dari tanggal PGS sampai sekarang
     */
    public function getDurasiPgsAttribute()
    {
        if (!$this->tanggal_pgs) {
            return '-';
        }

        try {
            // Parse tanggal PGS dengan format Indonesia (dd/mm/yyyy)
            $tanggalPgs = \Carbon\Carbon::createFromFormat('d/m/Y', $this->tanggal_pgs);
            $sekarang = \Carbon\Carbon::now();

            // Hitung selisih
            $diff = $tanggalPgs->diff($sekarang);

            // Hitung total bulan (termasuk tahun dikonversi ke bulan)
            $totalBulan = ($diff->y * 12) + $diff->m;

            // Format output hanya dengan bulan dan hari
            if ($totalBulan > 0) {
                return $totalBulan . ' bulan' . ($diff->d > 0 ? ' ' . $diff->d . ' hari' : '');
            } elseif ($diff->d > 0) {
                return $diff->d . ' hari';
            } else {
                return 'Hari ini';
            }
        } catch (\Exception $e) {
            return 'Invalid Date';
        }
    }

    /**
     * Format tanggal PGS untuk tampilan
     */
    public function getTanggalPgsFormattedAttribute()
    {
        if (!$this->tanggal_pgs) {
            return '-';
        }

        try {
            $tanggal = \Carbon\Carbon::createFromFormat('d/m/Y', $this->tanggal_pgs);
            return $tanggal->format('d M Y');
        } catch (\Exception $e) {
            return $this->tanggal_pgs;
        }
    }

    /**
     * Format tanggal selesai PGS untuk tampilan
     */
    public function getTanggalSelesaiPgsFormattedAttribute()
    {
        if (!$this->tanggal_selesai_pgs) {
            return '-';
        }

        try {
            $tanggal = \Carbon\Carbon::createFromFormat('d/m/Y', $this->tanggal_selesai_pgs);
            return $tanggal->format('d M Y');
        } catch (\Exception $e) {
            return $this->tanggal_selesai_pgs;
        }
    }

    /**
     * Cek apakah durasi PGS sudah melebihi batas (sudah melewati tanggal selesai)
     */
    public function getIsOverdueAttribute()
    {
        if (!$this->tanggal_selesai_pgs) {
            return false;
        }

        try {
            $tanggalSelesai = \Carbon\Carbon::createFromFormat('d/m/Y', $this->tanggal_selesai_pgs);
            $sekarang = \Carbon\Carbon::now();

            // Overdue jika sudah melewati tanggal selesai
            return $sekarang->isAfter($tanggalSelesai);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Cek apakah dalam masa peringatan (15 hari sebelum tanggal selesai)
     */
    public function getIsWarningAttribute()
    {
        if (!$this->tanggal_selesai_pgs) {
            return false;
        }

        try {
            $tanggalSelesai = \Carbon\Carbon::createFromFormat('d/m/Y', $this->tanggal_selesai_pgs);
            $sekarang = \Carbon\Carbon::now();

            // Warning jika 15 hari atau kurang sebelum tanggal selesai dan belum overdue
            $sisaHari = $sekarang->diffInDays($tanggalSelesai, false);
            return $sisaHari >= 0 && $sisaHari <= 15;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Dapatkan sisa hari sebelum tanggal selesai
     */
    public function getSisaHariAttribute()
    {
        if (!$this->tanggal_selesai_pgs) {
            return null;
        }

        try {
            $tanggalSelesai = \Carbon\Carbon::createFromFormat('d/m/Y', $this->tanggal_selesai_pgs);
            $sekarang = \Carbon\Carbon::now();

            // Bulatkan hasil diffInDays
            $sisaHari = round($sekarang->diffInDays($tanggalSelesai, false));

            if ($sisaHari < 0) {
                // Sudah lewat
                return 'Lewat ' . abs($sisaHari) . ' hari';
            } elseif ($sisaHari == 0) {
                return 'Hari ini';
            } else {
                return $sisaHari . ' hari lagi';
            }
        } catch (\Exception $e) {
            return '-';
        }
    }

    /**
     * Dapatkan total hari PGS
     */
    public function getTotalDaysAttribute()
    {
        if (!$this->tanggal_pgs) {
            return 0;
        }

        try {
            $tanggalPgs = \Carbon\Carbon::createFromFormat('d/m/Y', $this->tanggal_pgs);
            $sekarang = \Carbon\Carbon::now();
            return $tanggalPgs->diffInDays($sekarang);
        } catch (\Exception $e) {
            return 0;
        }
    }
}
