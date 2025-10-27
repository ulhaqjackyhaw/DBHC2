<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DataKaryawan;
use App\Models\Formasi;
use App\Exports\JabatanLowongExport;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    public function index()
    {
        // === KPI RINGKAS ===
        $total = DataKaryawan::count();

        $totalUnits = DataKaryawan::whereNotNull('unit_kerja')
            ->where('unit_kerja', '!=', '')
            ->distinct('unit_kerja')->count('unit_kerja');

        // Untuk usia, kita hitung dari tanggal_lahir (format dd/mm/yyyy)
        $karyawanData = DataKaryawan::whereNotNull('tanggal_lahir')
            ->where('tanggal_lahir', '!=', '')
            ->whereNotNull('tmt')
            ->where('tmt', '!=', '')
            ->get(['tanggal_lahir', 'tmt']);

        $totalAge = 0;
        $validAgeCount = 0;
        $totalMK = 0;
        $validMKCount = 0;

        foreach ($karyawanData as $karyawan) {
            // Hitung usia
            if ($karyawan->tanggal_lahir) {
                $age = $this->calculateAge($karyawan->tanggal_lahir);
                if ($age > 0) {
                    $totalAge += $age;
                    $validAgeCount++;
                }
            }

            // Hitung masa kerja
            if ($karyawan->tmt) {
                $mk = $this->calculateWorkPeriod($karyawan->tmt);
                if ($mk >= 0) {
                    $totalMK += $mk;
                    $validMKCount++;
                }
            }
        }

        $avgAge = $validAgeCount > 0 ? round($totalAge / $validAgeCount, 1) : 0;
        $avgMK = $validMKCount > 0 ? round($totalMK / $validMKCount, 1) : 0;

        $female = DataKaryawan::where('jenis_kelamin', 'Perempuan')->count();
        $femalePct = $total ? round($female / $total * 100, 1) : 0;

        // === STATUS (PIE) ===
        $employeeStatus = DataKaryawan::select(DB::raw("COALESCE(status,'(Kosong)') AS status_label"), DB::raw('COUNT(*) AS total'))
            ->groupBy('status_label')
            ->orderByDesc('total')
            ->get();

        $labels = $employeeStatus->pluck('status_label')->toArray();
        $data = $employeeStatus->pluck('total')->toArray();

        // === GENDER (fallback pie) ===
        $gender = DataKaryawan::select(DB::raw("COALESCE(jenis_kelamin,'(Kosong)') AS gender_label"), DB::raw('COUNT(*) AS total'))
            ->groupBy('gender_label')
            ->orderByDesc('total')
            ->get();
        $genderLabels = $gender->pluck('gender_label')->toArray();
        $genderData = $gender->pluck('total')->toArray();

        // === PENDIDIKAN (fallback) ===
        $pend = DataKaryawan::select(DB::raw("COALESCE(pendidikan_diakui,'(Kosong)') AS pend_label"), DB::raw('COUNT(*) AS total'))
            ->groupBy('pend_label')
            ->orderByDesc('total')
            ->get();
        $pendLabels = $pend->pluck('pend_label')->toArray();
        $pendData = $pend->pluck('total')->toArray();

        // app/Http/Controllers/DashboardController.php

        // ====== PENDIDIKAN Grouped (Organik vs OS) ======
        $pendidikanStatus = DataKaryawan::select(
            DB::raw("COALESCE(pendidikan_diakui,'(Kosong)') AS pend_label"),
            DB::raw("SUM(CASE WHEN LOWER(TRIM(status)) IN ('outsourcing','os','outsource') OR LOWER(TRIM(status)) LIKE '%outsour%' THEN 1 ELSE 0 END) AS os"),
            DB::raw("SUM(CASE WHEN LOWER(TRIM(status)) IN ('outsourcing','os','outsource') OR LOWER(TRIM(status)) LIKE '%outsour%' THEN 0 ELSE 1 END) AS organic")
        )
            ->groupBy('pend_label')
            // ---- MODIFIKASI DIMULAI DI SINI ----
            ->orderByRaw("
                CASE pend_label
                    WHEN 'SMP' THEN 1
                    WHEN 'SMA/SMK' THEN 2
                    WHEN 'D2' THEN 3
                    WHEN 'D3' THEN 4
                    WHEN 'S1' THEN 5
                    WHEN 'S2' THEN 6
                    WHEN 'S3' THEN 7
                    ELSE 99
                END ASC
            ")
            // ---- MODIFIKASI SELESAI ----
            ->get();

        $pendGroupLabels = $pendidikanStatus->pluck('pend_label')->toArray();
        $pendGroupOS = $pendidikanStatus->pluck('os')->map(fn($v) => (int) $v)->toArray();
        $pendGroupOrganic = $pendidikanStatus->pluck('organic')->map(fn($v) => (int) $v)->toArray();
        // ====== AKHIR DARI KODE YANG DITAMBAHKAN KEMBALI ======

        // === SEBARAN USIA (BIN) ===
        $usiaBins = [
            '<=24' => [0, 24],
            '25-29' => [25, 29],
            '30-34' => [30, 34],
            '35-39' => [35, 39],
            '>=40' => [40, 200],
        ];
        $usiaLabels = array_keys($usiaBins);
        $usiaData = [];

        // Hitung sebaran usia dengan parsing manual
        $allKaryawan = DataKaryawan::whereNotNull('tanggal_lahir')
            ->where('tanggal_lahir', '!=', '')
            ->get(['tanggal_lahir']);

        foreach ($usiaBins as $label => [$min, $max]) {
            $count = 0;
            foreach ($allKaryawan as $k) {
                $age = $this->calculateAge($k->tanggal_lahir);
                if ($label === '>=40') {
                    if ($age >= 40)
                        $count++;
                } else {
                    if ($age >= $min && $age <= $max)
                        $count++;
                }
            }
            $usiaData[] = $count;
        }

        // === SEBARAN MASA KERJA (BIN) ===
        $mkBins = [
            '0-1' => [0, 1],
            '2-3' => [2, 3],
            '4-6' => [4, 6],
            '7-10' => [7, 10],
            '>10' => [11, 200],
        ];
        $mkLabels = array_keys($mkBins);
        $mkData = [];

        // Hitung sebaran masa kerja dengan parsing manual
        $allKaryawanMK = DataKaryawan::whereNotNull('tmt')
            ->where('tmt', '!=', '')
            ->get(['tmt']);

        foreach ($mkBins as $label => [$min, $max]) {
            $count = 0;
            foreach ($allKaryawanMK as $k) {
                $mk = $this->calculateWorkPeriod($k->tmt);
                if ($label === '>10') {
                    if ($mk > 10)
                        $count++;
                } else {
                    if ($mk >= $min && $mk <= $max)
                        $count++;
                }
            }
            $mkData[] = $count;
        }

        // === TOP 10 UNIT ===
        $unitTop = DataKaryawan::select('unit_kerja', DB::raw('COUNT(*) AS total'))
            ->whereNotNull('unit_kerja')->where('unit_kerja', '!=', '')
            ->groupBy('unit_kerja')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
        $unitTopLabels = $unitTop->pluck('unit_kerja')->toArray();
        $unitTopData = $unitTop->pluck('total')->toArray();

        // --- LOGIKA BARU UNTUK JABATAN LOWONG (PAKAI FIELD KUOTA) ---
        // Ambil semua formasi unik (kode_jabatan, lokasi, unit, jabatan, kelompok_kelas_jabatan, kuota)
        $formasiData = Formasi::select('kode_jabatan', 'lokasi', 'unit', 'jabatan', 'kelompok_kelas_jabatan', 'kuota')->get();

        // Hitung jumlah karyawan per formasi (kode_jabatan, lokasi, unit)
        $karyawanData = DataKaryawan::select('kode_jabatan', 'lokasi_kerja', 'unit_kerja', DB::raw('COUNT(*) as karyawan_count'))
            ->groupBy('kode_jabatan', 'lokasi_kerja', 'unit_kerja')
            ->get()
            ->keyBy(function ($item) {
                return $item->kode_jabatan . '|' . $item->lokasi_kerja . '|' . $item->unit_kerja;
            });

        // Hitung jabatan lowong per lokasi dan kelompok_kelas_jabatan
        $jabatanLowongData = collect();
        foreach ($formasiData as $formasi) {
            $key = $formasi->kode_jabatan . '|' . $formasi->lokasi . '|' . $formasi->unit;
            $karyawanCount = $karyawanData->get($key)->karyawan_count ?? 0;
            $lowongCount = $formasi->kuota - $karyawanCount;

            if ($lowongCount > 0) {
                $jabatanLowongData->push((object) [
                    'lokasi' => $formasi->lokasi,
                    'level' => $formasi->kelompok_kelas_jabatan, // menggunakan kelompok_kelas_jabatan sebagai level
                    'total' => $lowongCount
                ]);
            }
        }

        // Menghitung total keseluruhan
        $totalJabatanLowong = $jabatanLowongData->sum('total');

        // Group by lokasi dan aggregate level yang sama
        $jabatanLowongGrouped = $jabatanLowongData->groupBy('lokasi')->map(function ($items, $lokasi) {
            return $items->groupBy('level')->map(function ($levelItems) {
                return (object) [
                    'level' => $levelItems->first()->level,
                    'total' => $levelItems->sum('total')
                ];
            })->values();
        });

        // === TABEL UNIT (SEMUA + JUMLAH) ===
        $unitTable = DataKaryawan::select('unit_kerja', DB::raw('COUNT(*) AS total'))
            ->whereNotNull('unit_kerja')->where('unit_kerja', '!=', '')
            ->groupBy('unit_kerja')
            ->orderBy('unit_kerja')
            ->get();

        // ====== GENDER Grouped (Organik vs OS) ======
        $genderStatus = DataKaryawan::select(
            DB::raw("
                    CASE
                        WHEN LOWER(TRIM(jenis_kelamin)) IN ('l','lk','laki','laki-laki','pria','male','m') THEN 'Laki-laki'
                        WHEN LOWER(TRIM(jenis_kelamin)) IN ('p','perempuan','wanita','female','f') THEN 'Perempuan'
                        ELSE '(Kosong)'
                    END AS gender_label
                "),
            DB::raw("SUM(CASE WHEN LOWER(TRIM(status)) IN ('outsourcing','os','outsource') OR LOWER(TRIM(status)) LIKE '%outsour%' THEN 1 ELSE 0 END) AS os"),
            DB::raw("SUM(CASE WHEN LOWER(TRIM(status)) IN ('outsourcing','os','outsource') OR LOWER(TRIM(status)) LIKE '%outsour%' THEN 0 ELSE 1 END) AS organic")
        )
            ->groupBy('gender_label')
            ->get();

        $genderGroupLabels = ['Laki-laki', 'Perempuan'];
        $map = [];
        foreach ($genderStatus as $row) {
            $map[$row->gender_label] = ['os' => (int) $row->os, 'organic' => (int) $row->organic];
        }
        $genderGroupOS = [];
        $genderGroupOrganic = [];
        foreach ($genderGroupLabels as $g) {
            $genderGroupOS[] = $map[$g]['os'] ?? 0;
            $genderGroupOrganic[] = $map[$g]['organic'] ?? 0;
        }

        // ====== PENGELOMPOKAN KELOMPOK KELAS JABATAN (BOD-4 .. BOD-1) ======
        $normExpr = "LOWER(REPLACE(REPLACE(TRIM(kelompok_kelas_jabatan),'-',''),' ',''))";

        $bodRows = DataKaryawan::select([
            DB::raw("$normExpr as kelas_norm"),
            'nama',
            'jabatan',
        ])
            ->whereNotNull('kelompok_kelas_jabatan')
            ->where('kelompok_kelas_jabatan', '!=', '')
            ->whereIn(DB::raw($normExpr), ['bod4', 'bod3', 'bod2', 'bod1'])
            ->orderBy('nama')
            ->get();

        $bodGroups = ['BOD-4' => [], 'BOD-3' => [], 'BOD-2' => [], 'BOD-1' => []];
        foreach ($bodRows as $r) {
            $key = match ($r->kelas_norm) {
                'bod4' => 'BOD-4',
                'bod3' => 'BOD-3',
                'bod2' => 'BOD-2',
                'bod1' => 'BOD-1',
                default => null,
            };
            if ($key) {
                $bodGroups[$key][] = [
                    'nama' => $r->nama ?? '-',
                    'jabatan' => $r->jabatan ?? '-',
                ];
            }
        }

        // ====== VARIABEL PENDIDIKAN GROUPED YANG HILANG ======
        $pendGroupLabels = ['SMP', 'SMA/SMK', 'D2', 'D3', 'S1', 'S2', 'S3'];
        $mapPend = [];
        foreach ($pendidikanStatus as $row) {
            $mapPend[$row->pend_label] = ['os' => (int) $row->os, 'organic' => (int) $row->organic];
        }
        $pendGroupOS = [];
        $pendGroupOrganic = [];
        foreach ($pendGroupLabels as $p) {
            $pendGroupOS[] = $mapPend[$p]['os'] ?? 0;
            $pendGroupOrganic[] = $mapPend[$p]['organic'] ?? 0;
        }

        // ====== BAGIAN COMPACT() YANG DIPERBAIKI ======
        return view('dashboard', compact(
            // chart lama
            'labels',
            'data',
            // KPI
            'total',
            'totalUnits',
            'avgAge',
            'avgMK',
            'femalePct',
            // chart tambahan
            'genderLabels',
            'genderData',
            'pendLabels',
            'pendData',
            'usiaLabels',
            'usiaData',
            'mkLabels',
            'mkData',
            'unitTopLabels',
            'unitTopData',
            // tabel unit
            'unitTable',
            // gender grouped bar
            'genderGroupLabels',
            'genderGroupOS',
            'genderGroupOrganic',
            // pendidikan grouped bar (INI YANG HILANG)
            'pendGroupLabels',      // <--- Pastikan ini ada
            'pendGroupOS',          // <--- Pastikan ini ada
            'pendGroupOrganic',     // <--- Pastikan ini ada
            // tabel BOD
            'bodGroups',
            'jabatanLowongGrouped', // Data terkelompok
            'totalJabatanLowong'   // Variabel TOTAL KESELURUHAN yang baru
        ));
    }

    /**
     * Calculate age from birth date
     */
    private function calculateAge($birthDate)
    {
        if (!$birthDate)
            return 0;

        // Parse berbagai format tanggal (dd/mm/yyyy, dd-mm-yyyy, yyyy-mm-dd)
        $parsedDate = null;
        if (strpos($birthDate, '/') !== false) {
            $parts = explode('/', $birthDate);
            if (count($parts) === 3) {
                $parsedDate = \DateTime::createFromFormat('d/m/Y', $birthDate);
            }
        } elseif (strpos($birthDate, '-') !== false) {
            $parts = explode('-', $birthDate);
            if (count($parts) === 3) {
                if (strlen($parts[0]) === 4) {
                    $parsedDate = \DateTime::createFromFormat('Y-m-d', $birthDate);
                } else {
                    $parsedDate = \DateTime::createFromFormat('d-m-Y', $birthDate);
                }
            }
        }

        if (!$parsedDate || $parsedDate === false)
            return 0;

        $today = new \DateTime();
        $age = $today->diff($parsedDate)->y;

        return $age;
    }

    /**
     * Calculate work period from TMT date
     */
    private function calculateWorkPeriod($tmtDate)
    {
        if (!$tmtDate)
            return 0;

        // Parse berbagai format tanggal (dd/mm/yyyy, dd-mm-yyyy, yyyy-mm-dd)
        $parsedDate = null;
        if (strpos($tmtDate, '/') !== false) {
            $parts = explode('/', $tmtDate);
            if (count($parts) === 3) {
                $parsedDate = \DateTime::createFromFormat('d/m/Y', $tmtDate);
            }
        } elseif (strpos($tmtDate, '-') !== false) {
            $parts = explode('-', $tmtDate);
            if (count($parts) === 3) {
                if (strlen($parts[0]) === 4) {
                    $parsedDate = \DateTime::createFromFormat('Y-m-d', $tmtDate);
                } else {
                    $parsedDate = \DateTime::createFromFormat('d-m-Y', $tmtDate);
                }
            }
        }

        if (!$parsedDate || $parsedDate === false)
            return 0;

        $today = new \DateTime();
        $workYears = $today->diff($parsedDate)->y;

        return $workYears;
    }

    /**
     * Get detail jabatan lowong untuk modal popup
     */
    public function getJabatanLowongDetail(Request $request)
    {
        $lokasi = $request->get('lokasi');
        $level = $request->get('level');

        if (!$lokasi || !$level) {
            return response()->json([
                'success' => false,
                'message' => 'Lokasi dan level harus diisi'
            ]);
        }

        try {
            // Ambil semua formasi berdasarkan lokasi dan level (pakai field kuota)
            $formasiDetail = Formasi::select([
                'kode_jabatan',
                'lokasi',
                'unit',
                'jabatan',
                'kelompok_kelas_jabatan',
                'kuota',
            ])
                ->where('lokasi', $lokasi)
                ->where('kelompok_kelas_jabatan', $level)
                ->get();

            // Hitung karyawan yang sudah terisi per formasi (kode_jabatan, lokasi, unit)
            $karyawanCounts = DataKaryawan::select('kode_jabatan', 'lokasi_kerja', 'unit_kerja', DB::raw('COUNT(*) as karyawan_count'))
                ->groupBy('kode_jabatan', 'lokasi_kerja', 'unit_kerja')
                ->get()
                ->keyBy(function ($item) {
                    return $item->kode_jabatan . '|' . $item->lokasi_kerja . '|' . $item->unit_kerja;
                });

            // Gabungkan data dan filter hanya yang lowong
            $detailLowong = collect();
            foreach ($formasiDetail as $formasi) {
                $key = $formasi->kode_jabatan . '|' . $formasi->lokasi . '|' . $formasi->unit;
                $karyawanCount = $karyawanCounts->get($key)->karyawan_count ?? 0;
                $lowongCount = $formasi->kuota - $karyawanCount;

                if ($lowongCount > 0) {
                    $detailLowong->push([
                        'kode_jabatan' => $formasi->kode_jabatan,
                        'lokasi' => $formasi->lokasi,
                        'unit' => $formasi->unit,
                        'jabatan' => $formasi->jabatan,
                        'level' => $formasi->kelompok_kelas_jabatan,
                        'formasi_count' => $formasi->kuota,
                        'karyawan_count' => $karyawanCount,
                        'lowong_count' => $lowongCount
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'data' => $detailLowong->values()->all(),
                'total' => $detailLowong->sum('lowong_count')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Export detail jabatan lowong ke Excel
     */
    public function exportJabatanLowongDetail(Request $request)
    {
        $lokasi = $request->get('lokasi');
        $level = $request->get('level');

        if (!$lokasi || !$level) {
            return redirect()->back()->with('error', 'Lokasi dan level harus diisi');
        }

        // Panggil method detail untuk mendapatkan data
        $detailResponse = $this->getJabatanLowongDetail($request);
        $detailData = $detailResponse->getData(true);

        if (!$detailData['success']) {
            return redirect()->back()->with('error', $detailData['message']);
        }

        // Buat Excel manual menggunakan simple approach
        $filename = 'jabatan_lowong_' . str_replace([' ', '/'], '_', $lokasi) . '_' . str_replace(['-', ' '], '_', $level) . '_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($detailData, $lokasi, $level) {
            $file = fopen('php://output', 'w');

            // Header info
            fputcsv($file, ['Detail Jabatan Lowong']);
            fputcsv($file, ['Lokasi:', $lokasi]);
            fputcsv($file, ['Level:', $level]);
            fputcsv($file, ['Tanggal Export:', date('d/m/Y H:i:s')]);
            fputcsv($file, []); // Empty row

            // Header tabel
            fputcsv($file, ['Kode Jabatan', 'Nama Jabatan', 'Unit', 'Formasi', 'Terisi', 'Lowong']);

            // Data
            foreach ($detailData['data'] as $row) {
                fputcsv($file, [
                    $row['kode_jabatan'],
                    $row['jabatan'],
                    $row['unit'],
                    $row['formasi_count'],
                    $row['karyawan_count'],
                    $row['lowong_count']
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export jabatan lowong (vacancy) per lokasi
     * Accessible by all authenticated users
     */
    public function exportJabatanLowong()
    {
        $fileName = 'Jabatan_Lowong_' . date('YmdHis') . '.xlsx';

        return Excel::download(new JabatanLowongExport(), $fileName);
    }
}