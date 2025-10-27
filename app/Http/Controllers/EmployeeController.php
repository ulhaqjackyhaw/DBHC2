<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;



class EmployeeController extends Controller
{
    public function analitikOrganic()
    {
        // 1. SETUP DASAR
        // ===========================================
        $baseQuery = DB::table('data_karyawan')
            ->whereNotNull('status_kepegawaian')
            ->whereRaw("LOWER(status_kepegawaian) NOT LIKE '%outsour%'");

        // --- PIVOT DATA UNIT-LOKASI UNTUK TABEL KOMPARASI ---
        // Ambil semua kombinasi unit-lokasi beserta total karyawan, rata usia, rata masa kerja
        $unitLocationRaw = (clone $baseQuery)
            ->select(
                'unit',
                'lokasi',
                DB::raw('COUNT(*) as total_karyawan'),
                DB::raw('ROUND(AVG(TIMESTAMPDIFF(YEAR, STR_TO_DATE(tanggal_lahir, "%d/%m/%Y"), CURDATE())), 0) as rata_usia'),
                DB::raw('ROUND(AVG(TIMESTAMPDIFF(YEAR, STR_TO_DATE(tmt, "%d/%m/%Y"), CURDATE())), 1) as rata_masa_kerja')
            )
            ->whereNotNull('unit')
            ->whereNotNull('lokasi')
            ->groupBy('unit', 'lokasi')
            ->get();

        // Dapatkan semua unit dan lokasi unik
        $allUnits = $unitLocationRaw->pluck('unit')->unique()->sort()->values();
        $allLocations = $unitLocationRaw->pluck('lokasi')->unique()->sort()->values();

        // Bentuk pivot: [unit][lokasi] = total_karyawan
        $unitLocationPivot = [];
        foreach ($allUnits as $unit) {
            foreach ($allLocations as $lokasi) {
                $found = $unitLocationRaw->first(fn($row) => $row->unit === $unit && $row->lokasi === $lokasi);
                $unitLocationPivot[$unit][$lokasi] = $found ? $found->total_karyawan : 0;
            }
            // Total per unit
            $unitLocationPivot[$unit]['total'] = $unitLocationRaw->where('unit', $unit)->sum('total_karyawan');
        }

        // (opsional) Data rata-rata usia & masa kerja per unit per lokasi
        $unitLocationMeta = [];
        foreach ($allUnits as $unit) {
            foreach ($allLocations as $lokasi) {
                $found = $unitLocationRaw->first(fn($row) => $row->unit === $unit && $row->lokasi === $lokasi);
                $unitLocationMeta[$unit][$lokasi] = [
                    'rata_usia' => $found ? $found->rata_usia : null,
                    'rata_masa_kerja' => $found ? $found->rata_masa_kerja : null,
                ];
            }
        } {
            // 1. SETUP DASAR
            // ===========================================
            $baseQuery = DB::table('data_karyawan')
                ->whereNotNull('status_kepegawaian')
                ->whereRaw("LOWER(status_kepegawaian) NOT LIKE '%outsour%'");

            // Piramida Jabatan per Lokasi (grouped/stacked bar)
            $kkjLocationRaw = (clone $baseQuery)
                ->select('lokasi', 'kelompok_kelas_jabatan', DB::raw('COUNT(*) as total'))
                ->whereNotNull('lokasi')
                ->whereNotNull('kelompok_kelas_jabatan')
                ->groupBy('lokasi', 'kelompok_kelas_jabatan')
                ->orderBy('lokasi')
                ->get();

            $kkjLocationLabels = $kkjLocationRaw->pluck('lokasi')->unique()->values();
            $kkjGroups = $kkjLocationRaw->pluck('kelompok_kelas_jabatan')->unique()->values();
            $kkjColorPalette = ['#f97316', '#4f46e5', '#16a34a', '#db2777', '#8b5cf6', '#64748b']; // BOD-1, BOD-2, BOD-3, BOD-4, ...
            $bodOrder = ['BOD-1', 'BOD-2', 'BOD-3', 'BOD-4'];
            $kkjGroupsSorted = $kkjGroups->sort(function ($a, $b) use ($bodOrder) {
                $ai = array_search($a, $bodOrder);
                $bi = array_search($b, $bodOrder);
                if ($ai === false)
                    $ai = 99;
                if ($bi === false)
                    $bi = 99;
                return $ai <=> $bi ?: strcmp($a, $b);
            })->values();
            $kkjLocationDatasets = [];
            foreach ($kkjGroupsSorted as $i => $group) {
                $row = [];
                foreach ($kkjLocationLabels as $lokasi) {
                    $found = $kkjLocationRaw->first(fn($item) => $item->lokasi == $lokasi && $item->kelompok_kelas_jabatan == $group);
                    $row[] = $found ? $found->total : 0;
                }
                $kkjLocationDatasets[] = [
                    'label' => $group,
                    'data' => $row,
                    'backgroundColor' => $kkjColorPalette[$i % count($kkjColorPalette)]
                ];
            }
            // ...existing code...
            // ...existing code...
            // 1. SETUP DASAR
            // ===========================================
            $baseQuery = DB::table('data_karyawan')
                ->whereNotNull('status_kepegawaian')
                ->whereRaw("LOWER(status_kepegawaian) NOT LIKE '%outsour%'");

            // Distribusi Gender per Lokasi (grouped/stacked bar)
            $genderLocationRaw = (clone $baseQuery)
                ->select('lokasi', 'gender', DB::raw('COUNT(*) as total'))
                ->whereNotNull('gender')
                ->whereNotNull('lokasi')
                ->groupBy('lokasi', 'gender')
                ->orderBy('lokasi')
                ->get();

            $genderLocationLabels = $genderLocationRaw->pluck('lokasi')->unique()->values();
            $genderGroups = $genderLocationRaw->pluck('gender')->unique()->values();
            $genderColorPalette = ['#4f46e5', '#db2777', '#f97316', '#16a34a', '#8b5cf6'];
            $genderLocationDatasets = [];
            foreach ($genderGroups as $i => $gender) {
                $row = [];
                foreach ($genderLocationLabels as $lokasi) {
                    $found = $genderLocationRaw->first(fn($item) => $item->lokasi == $lokasi && $item->gender == $gender);
                    $row[] = $found ? $found->total : 0;
                }
                $genderLocationDatasets[] = [
                    'label' => $gender,
                    'data' => $row,
                    'backgroundColor' => $genderColorPalette[$i % count($genderColorPalette)]
                ];
            }

            // Distribusi Generasi per Lokasi (untuk chart grouped/stacked)
            $ageLocationRaw = (clone $baseQuery)
                ->select('lokasi', DB::raw("CASE 
                WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tanggal_lahir, '%d/%m/%Y'), CURDATE()) BETWEEN 13 AND 28 THEN 'Gen Z'
                WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tanggal_lahir, '%d/%m/%Y'), CURDATE()) BETWEEN 29 AND 44 THEN 'Gen Y (Milenial)'
                WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tanggal_lahir, '%d/%m/%Y'), CURDATE()) BETWEEN 45 AND 60 THEN 'Gen X'
                WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tanggal_lahir, '%d/%m/%Y'), CURDATE()) BETWEEN 61 AND 79 THEN 'Baby Boomers'
                WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tanggal_lahir, '%d/%m/%Y'), CURDATE()) >= 79 THEN 'Pre-Boomer'
                ELSE 'Lainnya'
            END as age_group"), DB::raw('COUNT(*) as total'))
                ->whereNotNull('tanggal_lahir')
                ->whereNotNull('lokasi')
                ->groupBy('lokasi', 'age_group')
                ->orderBy('lokasi')
                ->get();

            $ageLocationLabels = $ageLocationRaw->pluck('lokasi')->unique()->values();
            $ageGroups = $ageLocationRaw->pluck('age_group')->unique()->values();
            $ageLocationDatasets = [];
            $ageColorPalette = ['#f97316', '#4f46e5', '#db2777', '#16a34a', '#8b5cf6', '#64748b'];
            foreach ($ageGroups as $i => $ageGroup) {
                $row = [];
                foreach ($ageLocationLabels as $lokasi) {
                    $found = $ageLocationRaw->first(fn($item) => $item->lokasi == $lokasi && $item->age_group == $ageGroup);
                    $row[] = $found ? $found->total : 0;
                }
                $ageLocationDatasets[] = [
                    'label' => $ageGroup,
                    'data' => $row,
                    'backgroundColor' => $ageColorPalette[$i % count($ageColorPalette)]
                ];
            }

            // Distribusi Masa Kerja per Lokasi (grouped/stacked bar)
            $tenureLocationRaw = (clone $baseQuery)
                ->select('lokasi', DB::raw("CASE WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tmt, '%d/%m/%Y'), CURDATE()) < 1 THEN '< 1' WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tmt, '%d/%m/%Y'), CURDATE()) BETWEEN 1 AND 5 THEN '1-5' WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tmt, '%d/%m/%Y'), CURDATE()) BETWEEN 6 AND 10 THEN '6-10' WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tmt, '%d/%m/%Y'), CURDATE()) BETWEEN 11 AND 20 THEN '11-20' ELSE '> 20' END as tenure_group"), DB::raw('COUNT(*) as total'))
                ->whereNotNull('tmt')
                ->whereNotNull('lokasi')
                ->groupBy('lokasi', 'tenure_group')
                ->orderBy('lokasi')
                ->get();

            $tenureLocationLabels = $tenureLocationRaw->pluck('lokasi')->unique()->values();
            $tenureGroupsList = $tenureLocationRaw->pluck('tenure_group')->unique()->values();
            $tenureColorPalette = ['#16a34a', '#f97316', '#4f46e5', '#db2777', '#8b5cf6'];
            $tenureLocationDatasets = [];
            foreach ($tenureGroupsList as $i => $tenureGroup) {
                $row = [];
                foreach ($tenureLocationLabels as $lokasi) {
                    $found = $tenureLocationRaw->first(fn($item) => $item->lokasi == $lokasi && $item->tenure_group == $tenureGroup);
                    $row[] = $found ? $found->total : 0;
                }
                $tenureLocationDatasets[] = [
                    'label' => $tenureGroup,
                    'data' => $row,
                    'backgroundColor' => $tenureColorPalette[$i % count($tenureColorPalette)]
                ];
            }
            // 1. SETUP DASAR
            // ===========================================
            $baseQuery = DB::table('data_karyawan')
                ->whereNotNull('status_kepegawaian')
                ->whereRaw("LOWER(status_kepegawaian) NOT LIKE '%outsour%'");

            $employees = (clone $baseQuery)->get();
            $totalOrganic = $employees->count();

            // Helper function untuk pivot data menjadi matriks
            $pivot = function ($data, $rowKey, $colKey, $valKey) {
                $matrix = [];
                $colHeaders = $data->pluck($colKey)->unique()->sort();
                if ($colHeaders->isEmpty())
                    return ['matrix' => [], 'colHeaders' => collect()];
                foreach ($data as $item) {
                    $row = $item->{$rowKey};
                    $col = $item->{$colKey};
                    $val = $item->{$valKey};
                    if (!isset($matrix[$row])) {
                        $matrix[$row] = ['Total' => 0];
                        foreach ($colHeaders as $header)
                            $matrix[$row][$header] = 0;
                    }
                    $matrix[$row][$col] = $val;
                    $matrix[$row]['Total'] += $val;
                }
                return ['matrix' => $matrix, 'colHeaders' => $colHeaders];
            };

            // 2. KALKULASI SEMUA ANALISIS
            // ===========================================

            // A. Analisis untuk Dashboard Utama
            $genderCounts = (clone $baseQuery)->select('gender', DB::raw('COUNT(*) as total'))->whereNotNull('gender')->groupBy('gender')->get();
            $ageGroupsData = (clone $baseQuery)->select(
                DB::raw("CASE 
                    WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tanggal_lahir, '%d/%m/%Y'), CURDATE()) BETWEEN 13 AND 28 THEN 'Gen Z'
                    WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tanggal_lahir, '%d/%m/%Y'), CURDATE()) BETWEEN 29 AND 44 THEN 'Gen Y (Milenial)'
                    WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tanggal_lahir, '%d/%m/%Y'), CURDATE()) BETWEEN 45 AND 60 THEN 'Gen X'
                    WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tanggal_lahir, '%d/%m/%Y'), CURDATE()) BETWEEN 61 AND 79 THEN 'Baby Boomers'
                    WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tanggal_lahir, '%d/%m/%Y'), CURDATE()) >= 79 THEN 'Pre-Boomer'
                    ELSE 'Lainnya'
                END as age_group, COUNT(*) as total")
            )
                ->whereNotNull('tanggal_lahir')
                ->groupBy('age_group')
                ->orderBy(DB::raw("CASE 
                WHEN age_group = 'Pre-Boomer' THEN 1
                WHEN age_group = 'Baby Boomers' THEN 2
                WHEN age_group = 'Gen X' THEN 3
                WHEN age_group = 'Gen Y (Milenial)' THEN 4
                WHEN age_group = 'Gen Z' THEN 5
                ELSE 6
            END"))
                ->get();

            $tenureGroups = (clone $baseQuery)->select(DB::raw("CASE WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tmt, '%d/%m/%Y'), CURDATE()) < 1 THEN '< 1' WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tmt, '%d/%m/%Y'), CURDATE()) BETWEEN 1 AND 5 THEN '1-5' WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tmt, '%d/%m/%Y'), CURDATE()) BETWEEN 6 AND 10 THEN '6-10' WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tmt, '%d/%m/%Y'), CURDATE()) BETWEEN 11 AND 20 THEN '11-20' ELSE '> 20' END as tenure_group, COUNT(*) as total"))->whereNotNull('tmt')->groupBy('tenure_group')->orderBy('tenure_group')->get();
            $kkjCounts = (clone $baseQuery)->select('kelompok_kelas_jabatan', DB::raw('COUNT(*) as total'))->whereNotNull('kelompok_kelas_jabatan')->groupBy('kelompok_kelas_jabatan')->orderBy('total', 'desc')->get();

            $bodRows = (clone $baseQuery)->whereIn('kelompok_kelas_jabatan', ['BOD-1', 'BOD-2', 'BOD-3', 'BOD-4'])->select('nama', 'jabatan', 'kelompok_kelas_jabatan')->get();
            $bodGroups = collect($bodRows)->groupBy('kelompok_kelas_jabatan')->map(function ($group) {
                return $group->map(fn($item) => ['nama' => $item->nama, 'jabatan' => $item->jabatan])->all();
            })->all();


            // B. Analisis untuk Tab Strategis  <-- PERUBAHAN LOGIKA DI AREA INI
            // Mengganti `rata_masa_kerja` dengan `persen_karyawan_baru`
            $unitHealth = (clone $baseQuery)->select(
                'lokasi', // tambahkan lokasi
                'unit',
                DB::raw('COUNT(*) as total_karyawan'),
                DB::raw('ROUND(AVG(TIMESTAMPDIFF(YEAR, STR_TO_DATE(tanggal_lahir, \'%d/%m/%Y\'), CURDATE())), 0) as rata_usia'),
                // Diubah: Menghitung persentase karyawan dengan masa kerja < 1 tahun sebagai proksi stabilitas
                DB::raw('ROUND(SUM(CASE WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tmt, \'%d/%m/%Y\'), CURDATE()) < 1 THEN 1 ELSE 0 END) * 100 / COUNT(*), 1) as persen_karyawan_baru'),
                DB::raw('ROUND(AVG(TIMESTAMPDIFF(YEAR, STR_TO_DATE(tmt, \'%d/%m/%Y\'), CURDATE())), 1) as rata_masa_kerja'), // <-- Tetap dihitung untuk tabel, tapi tidak untuk kuadran
                DB::raw('ROUND(SUM(CASE WHEN gender = "Perempuan" OR gender = "Wanita" THEN 1 ELSE 0 END) * 100 / COUNT(*), 0) as persen_wanita')
            )
                ->whereNotNull('unit')
                ->groupBy('lokasi', 'unit') // group by lokasi dan unit
                ->orderByDesc('total_karyawan')
                ->get();

            // Diubah: Sumbu X sekarang menggunakan `persen_karyawan_baru`
            $quadrantData = $unitHealth->map(fn($unit) => ['x' => $unit->persen_karyawan_baru, 'y' => $unit->total_karyawan, 'r' => sqrt($unit->total_karyawan / M_PI) * 2, 'label' => $unit->unit]);

            // Diubah: Median dihitung berdasarkan metrik baru untuk garis kuadran
            $medianTenure = $unitHealth->median('persen_karyawan_baru') ?? 0;
            $medianHeadcount = $unitHealth->median('total_karyawan') ?? 0;

            // C. Analisis untuk Tab Matriks
            $ageGradeCounts = (clone $baseQuery)->select('grade', DB::raw("CASE WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tanggal_lahir, '%d/%m/%Y'), CURDATE()) <= 25 THEN '≤ 25' WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tanggal_lahir, '%d/%m/%Y'), CURDATE()) BETWEEN 26 AND 35 THEN '26-35' WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tanggal_lahir, '%d/%m/%Y'), CURDATE()) BETWEEN 36 AND 45 THEN '36-45' WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tanggal_lahir, '%d/%m/%Y'), CURDATE()) BETWEEN 46 AND 55 THEN '46-55' ELSE '> 55' END as age_group, COUNT(*) as total"))->whereNotNull('grade')->whereNotNull('tanggal_lahir')->groupBy('grade', 'age_group')->orderBy('grade', 'asc')->get()->map(fn($i) => (object) ['GRADE' => 'Grade ' . $i->grade, 'age_group' => $i->age_group, 'total' => $i->total]);
            $ageGradeResult = $pivot($ageGradeCounts, 'GRADE', 'age_group', 'total');

            $gradeGenderCounts = (clone $baseQuery)->select('grade', 'gender', DB::raw('COUNT(*) as total'))->whereNotNull('grade')->whereNotNull('gender')->groupBy('grade', 'gender')->orderBy('grade', 'asc')->get()->map(fn($i) => (object) ['GRADE' => 'Grade ' . $i->grade, 'GENDER' => $i->gender, 'total' => $i->total]);
            $gradeGenderResult = $pivot($gradeGenderCounts, 'GRADE', 'GENDER', 'total');
            $gradeGenderChartData = ['labels' => array_keys($gradeGenderResult['matrix']), 'datasets' => []];
            foreach ($gradeGenderResult['colHeaders'] as $gender) {
                $dataset = ['label' => $gender, 'data' => collect($gradeGenderResult['matrix'])->pluck($gender)->all()];
                $gradeGenderChartData['datasets'][] = $dataset;
            }

            // D. Analisis untuk Tab Lanjutan
            $educationCounts = (clone $baseQuery)->select('pendidikan_terakhir', DB::raw('COUNT(*) as total'))->whereNotNull('pendidikan_terakhir')->groupBy('pendidikan_terakhir')->orderBy('total', 'desc')->get();
            $tenureGradeCounts = (clone $baseQuery)->select('grade', DB::raw("CASE WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tmt, '%d/%m/%Y'), CURDATE()) < 1 THEN '< 1' WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tmt, '%d/%m/%Y'), CURDATE()) BETWEEN 1 AND 5 THEN '1-5' WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tmt, '%d/%m/%Y'), CURDATE()) BETWEEN 6 AND 10 THEN '6-10' WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tmt, '%d/%m/%Y'), CURDATE()) BETWEEN 11 AND 20 THEN '11-20' ELSE '> 20' END as tenure_group, COUNT(*) as total"))->whereNotNull('grade')->whereNotNull('tmt')->groupBy('grade', 'tenure_group')->orderBy('grade', 'asc')->get()->map(fn($i) => (object) ['GRADE' => 'Grade ' . $i->grade, 'tenure_group' => $i->tenure_group, 'total' => $i->total]);
            $tenureGradeResult = $pivot($tenureGradeCounts, 'GRADE', 'tenure_group', 'total');
            $turnoverProxy = (clone $baseQuery)->select('unit', DB::raw('SUM(CASE WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tmt, \'%d/%m/%Y\'), CURDATE()) < 1 THEN 1 ELSE 0 END) as baru, COUNT(*) as total'))->groupBy('unit')->get()->map(function ($i) {
                $i->persen_baru = round(($i->baru / max(1, $i->total)) * 100, 1);
                return $i;
            });

            // E. Analisis Top 10 Unit dengan tingkat pendidikan tertinggi
            $topEducatedUnits = (clone $baseQuery)
                ->select(
                    'unit',
                    DB::raw('COUNT(*) as total_karyawan'),
                    DB::raw('AVG(CASE 
                    WHEN pendidikan_terakhir = "S3" THEN 9
                    WHEN pendidikan_terakhir = "S2" THEN 8
                    WHEN pendidikan_terakhir = "S1" THEN 7
                    WHEN pendidikan_terakhir = "D4" THEN 6
                    WHEN pendidikan_terakhir = "D3" THEN 5
                    WHEN pendidikan_terakhir = "D2" THEN 4
                    WHEN pendidikan_terakhir = "D1" THEN 3
                    WHEN pendidikan_terakhir = "SMA/SMK" THEN 2
                    WHEN pendidikan_terakhir = "SMP" THEN 1
                    ELSE 0
                END) as education_score'),
                    DB::raw('ROUND(SUM(CASE 
                    WHEN pendidikan_terakhir IN ("S1", "S2", "S3") THEN 1 
                    ELSE 0 
                END) * 100.0 / COUNT(*), 1) as persen_sarjana'),
                    DB::raw('ROUND(SUM(CASE 
                    WHEN pendidikan_terakhir IN ("S1", "S2", "S3") THEN 1 
                    ELSE 0 
                END) * 100.0 / COUNT(*), 1) as high_education_percentage'),
                    DB::raw('SUM(CASE WHEN pendidikan_terakhir = "S3" THEN 1 ELSE 0 END) as s3_count'),
                    DB::raw('SUM(CASE WHEN pendidikan_terakhir = "S2" THEN 1 ELSE 0 END) as s2_count'),
                    DB::raw('SUM(CASE WHEN pendidikan_terakhir = "S1" THEN 1 ELSE 0 END) as s1_count'),
                    DB::raw('SUM(CASE WHEN pendidikan_terakhir IN ("D1", "D2", "D3", "D4") THEN 1 ELSE 0 END) as diploma_count')
                )
                ->whereNotNull('unit')
                ->where('unit', '!=', '')
                ->groupBy('unit')
                ->having('total_karyawan', '>=', 3)
                ->orderByDesc('education_score')
                ->limit(10)
                ->get();

            // F. Analisis sebaran pendidikan berdasarkan lokasi (menggunakan kolom 'lokasi')
            $locationEducation = (clone $baseQuery)
                ->select('lokasi', 'pendidikan_terakhir', DB::raw('COUNT(*) as jumlah'))
                ->whereNotNull('lokasi')
                ->where('lokasi', '!=', '')
                ->whereNotNull('pendidikan_terakhir')
                ->where('pendidikan_terakhir', '!=', '')
                ->groupBy('lokasi', 'pendidikan_terakhir')
                ->orderBy('lokasi')
                ->orderBy('pendidikan_terakhir')
                ->get();

            // Transform data untuk matrix lokasi-pendidikan
            $locationMatrix = [];
            $educationLevels = ['S3', 'S2', 'S1', 'D4', 'D3', 'D2', 'D1', 'SMA/SMK', 'SMP', 'Lainnya'];

            foreach ($locationEducation as $item) {
                if (!isset($locationMatrix[$item->lokasi])) {
                    $locationMatrix[$item->lokasi] = array_fill_keys($educationLevels, 0);
                    $locationMatrix[$item->lokasi]['total'] = 0;
                    $locationMatrix[$item->lokasi]['Lainnya'] = 0; // Tambahkan kolom Lainnya
                }

                $locationMatrix[$item->lokasi][$item->pendidikan_terakhir] = $item->jumlah;
                $locationMatrix[$item->lokasi]['total'] += $item->jumlah;
            }

            // Hitung education_score dan highest_education untuk setiap lokasi
            $scores = [
                'S3' => 9,
                'S2' => 8,
                'S1' => 7,
                'D4' => 6,
                'D3' => 5,
                'D2' => 4,
                'D1' => 3,
                'SMA/SMK' => 2,
                'SMP' => 1,
                'Lainnya' => 0
            ];

            foreach ($locationMatrix as $location => $data) {
                $total = $data['total'];
                $educationScore = 0;

                // Hitung skor pendidikan untuk lokasi
                foreach ($scores as $education => $score) {
                    if (isset($data[$education]) && $data[$education] > 0) {
                        $educationScore += ($data[$education] * $score);
                    }
                }

                $locationMatrix[$location]['education_score'] = $total > 0 ? round($educationScore / $total, 2) : 0;
                $locationMatrix[$location]['highest_education'] = $this->getHighestEducation($data);
            }

            // Summary lokasi dengan pendidikan tertinggi
            $locationSummary = collect($locationMatrix)->map(function ($data, $location) {
                $total = $data['total'];
                $educationScore = 0;

                // Hitung skor pendidikan untuk lokasi
                $scores = [
                    'S3' => 9,
                    'S2' => 8,
                    'S1' => 7,
                    'D4' => 6,
                    'D3' => 5,
                    'D2' => 4,
                    'D1' => 3,
                    'SMA/SMK' => 2,
                    'SMP' => 1
                ];

                foreach ($scores as $education => $score) {
                    if (isset($data[$education]) && $data[$education] > 0) {
                        $educationScore += ($data[$education] * $score);
                    }
                }

                return [
                    'location' => $location,
                    'total_employees' => $total,
                    'average_education_score' => $total > 0 ? round($educationScore / $total, 2) : 0,
                    'highest_education' => $this->getHighestEducation($data),
                    'education_distribution' => $data
                ];
            })->sortByDesc('average_education_score');

            // 3. KIRIM DATA KE VIEW
            // ===========================================

            // --- PIVOT DATA UNIT-LOKASI UNTUK TABEL KOMPARASI ---
            // Ambil semua kombinasi unit-lokasi beserta total karyawan, rata usia, rata masa kerja
            $unitLocationRaw = (clone $baseQuery)
                ->select(
                    'unit',
                    'lokasi',
                    DB::raw('COUNT(*) as total_karyawan'),
                    DB::raw('ROUND(AVG(TIMESTAMPDIFF(YEAR, STR_TO_DATE(tanggal_lahir, "%d/%m/%Y"), CURDATE())), 0) as rata_usia'),
                    DB::raw('ROUND(AVG(TIMESTAMPDIFF(YEAR, STR_TO_DATE(tmt, "%d/%m/%Y"), CURDATE())), 1) as rata_masa_kerja')
                )
                ->whereNotNull('unit')
                ->whereNotNull('lokasi')
                ->groupBy('unit', 'lokasi')
                ->get();

            // Dapatkan semua unit dan lokasi unik
            $allUnits = $unitLocationRaw->pluck('unit')->unique()->sort()->values();
            $allLocations = $unitLocationRaw->pluck('lokasi')->unique()->sort()->values();

            // Bentuk pivot: [unit][lokasi] = total_karyawan
            $unitLocationPivot = [];
            foreach ($allUnits as $unit) {
                foreach ($allLocations as $lokasi) {
                    $found = $unitLocationRaw->first(fn($row) => $row->unit === $unit && $row->lokasi === $lokasi);
                    $unitLocationPivot[$unit][$lokasi] = $found ? $found->total_karyawan : 0;
                }
                // Total per unit
                $unitLocationPivot[$unit]['total'] = $unitLocationRaw->where('unit', $unit)->sum('total_karyawan');
            }

            // (opsional) Data rata-rata usia & masa kerja per unit per lokasi
            $unitLocationMeta = [];
            foreach ($allUnits as $unit) {
                foreach ($allLocations as $lokasi) {
                    $found = $unitLocationRaw->first(fn($row) => $row->unit === $unit && $row->lokasi === $lokasi);
                    $unitLocationMeta[$unit][$lokasi] = [
                        'rata_usia' => $found ? $found->rata_usia : null,
                        'rata_masa_kerja' => $found ? $found->rata_masa_kerja : null,
                    ];
                }
            }

            return view('analitik.organic', [
                'unitLocationPivot' => $unitLocationPivot,
                'allUnits' => $allUnits,
                'allLocations' => $allLocations,
                'unitLocationMeta' => $unitLocationMeta,
                'employees' => $employees,
                'totalOrganic' => $totalOrganic,
                'genderData' => $genderCounts,
                'ageGroupsData' => $ageGroupsData,
                'genderLocationLabels' => $genderLocationLabels,
                'genderLocationDatasets' => $genderLocationDatasets,
                'ageLocationLabels' => $ageLocationLabels,
                'ageLocationDatasets' => $ageLocationDatasets,
                'tenureGroups' => $tenureGroups,
                'tenureLocationLabels' => $tenureLocationLabels,
                'tenureLocationDatasets' => $tenureLocationDatasets,
                'kkjData' => $kkjCounts,
                'kkjLocationLabels' => $kkjLocationLabels,
                'kkjLocationDatasets' => $kkjLocationDatasets,
                'bodGroups' => $bodGroups,
                'unitHealth' => $unitHealth,
                'quadrantData' => $quadrantData,
                'medianTenure' => $medianTenure,
                'medianHeadcount' => $medianHeadcount,
                'ageGradeMatrix' => $ageGradeResult['matrix'],
                'ageGradeHeaders' => $ageGradeResult['colHeaders'],
                'gradeGenderMatrix' => $gradeGenderResult['matrix'],
                'gradeGenderHeaders' => $gradeGenderResult['colHeaders'],
                'gradeGenderChartData' => $gradeGenderChartData,
                'educationCounts' => $educationCounts,
                'tenureGradeMatrix' => $tenureGradeResult['matrix'],
                'tenureGradeHeaders' => $tenureGradeResult['colHeaders'],
                'turnoverProxy' => $turnoverProxy,
                'topEducatedUnits' => $topEducatedUnits,
                'locationMatrix' => $locationMatrix,
                'locationSummary' => $locationSummary,
                'educationLevels' => $educationLevels,
            ]);
        }
    }


    public function analitikOutsourcing()
    {
        $baseQuery = DB::table('data_karyawan')
            ->whereRaw("LOWER(status_kepegawaian) LIKE '%outsour%'");

        $employees = (clone $baseQuery)->get();
        $totalOutsourcing = $employees->count();

        // Analisis Gender by Location (for grouped chart)
        $genderLocationRaw = (clone $baseQuery)
            ->select('lokasi', 'gender', DB::raw('COUNT(*) as total'))
            ->whereNotNull('gender')
            ->whereNotNull('lokasi')
            ->groupBy('lokasi', 'gender')
            ->orderBy('lokasi')
            ->get();

        // Prepare data for grouped/stacked bar chart
        $locations = $genderLocationRaw->pluck('lokasi')->unique()->values();
        $genders = $genderLocationRaw->pluck('gender')->unique()->values();
        $colorPalette = ['#4f46e5', '#db2777', '#f97316', '#16a34a', '#8b5cf6'];
        $genderLocationMatrix = [];
        foreach ($genders as $i => $gender) {
            $row = [];
            foreach ($locations as $lokasi) {
                $found = $genderLocationRaw->first(fn($item) => $item->lokasi == $lokasi && $item->gender == $gender);
                $row[] = $found ? $found->total : 0;
            }
            $genderLocationMatrix[] = [
                'label' => $gender,
                'data' => $row,
                'backgroundColor' => $colorPalette[$i % count($colorPalette)]
            ];
        }

        // Analisis Instansi per Lokasi (for grouped/stacked bar chart)
        $instansiLocationRaw = (clone $baseQuery)
            ->select('lokasi', 'asal_instansi', DB::raw('COUNT(*) as total'))
            ->whereNotNull('asal_instansi')
            ->where('asal_instansi', '!=', '')
            ->whereNotNull('lokasi')
            ->where('lokasi', '!=', '')
            ->groupBy('lokasi', 'asal_instansi')
            ->orderBy('lokasi')
            ->get();

        $instansiLocationLabels = $instansiLocationRaw->pluck('lokasi')->unique()->values();
        $instansiList = $instansiLocationRaw->pluck('asal_instansi')->unique()->values();
        $instansiColorPalette = ['#4f46e5', '#db2777', '#f97316', '#16a34a', '#8b5cf6', '#64748b', '#eab308', '#0ea5e9', '#f43f5e', '#22d3ee'];
        $instansiLocationDatasets = [];
        foreach ($instansiList as $i => $instansi) {
            $row = [];
            foreach ($instansiLocationLabels as $lokasi) {
                $found = $instansiLocationRaw->first(fn($item) => $item->lokasi == $lokasi && $item->asal_instansi == $instansi);
                $row[] = $found ? $found->total : 0;
            }
            $instansiLocationDatasets[] = [
                'label' => $instansi,
                'data' => $row,
                'backgroundColor' => $instansiColorPalette[$i % count($instansiColorPalette)]
            ];
        }

        // Analisis Generasi (Usia) - untuk chart biasa
        $ageGroupsData = (clone $baseQuery)->select(
            DB::raw("CASE 
                WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tanggal_lahir, '%d/%m/%Y'), CURDATE()) BETWEEN 13 AND 28 THEN 'Gen Z'
                WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tanggal_lahir, '%d/%m/%Y'), CURDATE()) BETWEEN 29 AND 44 THEN 'Gen Y (Milenial)'
                WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tanggal_lahir, '%d/%m/%Y'), CURDATE()) BETWEEN 45 AND 60 THEN 'Gen X'
                WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tanggal_lahir, '%d/%m/%Y'), CURDATE()) BETWEEN 61 AND 79 THEN 'Baby Boomers'
                WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tanggal_lahir, '%d/%m/%Y'), CURDATE()) >= 79 THEN 'Pre-Boomer'
                ELSE 'Lainnya'
            END as age_group"),
            DB::raw('COUNT(*) as total')
        )
            ->whereNotNull('tanggal_lahir')
            ->groupBy('age_group')
            ->orderBy(DB::raw("CASE 
                WHEN age_group = 'Pre-Boomer' THEN 1
                WHEN age_group = 'Baby Boomers' THEN 2
                WHEN age_group = 'Gen X' THEN 3
                WHEN age_group = 'Gen Y (Milenial)' THEN 4
                WHEN age_group = 'Gen Z' THEN 5
                ELSE 6
            END"))
            ->get();

        // Distribusi Generasi per Lokasi (untuk chart grouped/stacked)
        $ageLocationRaw = (clone $baseQuery)
            ->select('lokasi', DB::raw("CASE 
                WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tanggal_lahir, '%d/%m/%Y'), CURDATE()) BETWEEN 13 AND 28 THEN 'Gen Z'
                WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tanggal_lahir, '%d/%m/%Y'), CURDATE()) BETWEEN 29 AND 44 THEN 'Gen Y (Milenial)'
                WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tanggal_lahir, '%d/%m/%Y'), CURDATE()) BETWEEN 45 AND 60 THEN 'Gen X'
                WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tanggal_lahir, '%d/%m/%Y'), CURDATE()) BETWEEN 61 AND 79 THEN 'Baby Boomers'
                WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tanggal_lahir, '%d/%m/%Y'), CURDATE()) >= 79 THEN 'Pre-Boomer'
                ELSE 'Lainnya'
            END as age_group"), DB::raw('COUNT(*) as total'))
            ->whereNotNull('tanggal_lahir')
            ->whereNotNull('lokasi')
            ->groupBy('lokasi', 'age_group')
            ->orderBy('lokasi')
            ->get();

        $ageLocations = $ageLocationRaw->pluck('lokasi')->unique()->values();
        $ageGroups = $ageLocationRaw->pluck('age_group')->unique()->values();
        $ageLocationDatasets = [];
        $ageColorPalette = ['#f97316', '#4f46e5', '#db2777', '#16a34a', '#8b5cf6', '#64748b'];
        foreach ($ageGroups as $i => $ageGroup) {
            $row = [];
            foreach ($ageLocations as $lokasi) {
                $found = $ageLocationRaw->first(fn($item) => $item->lokasi == $lokasi && $item->age_group == $ageGroup);
                $row[] = $found ? $found->total : 0;
            }
            $ageLocationDatasets[] = [
                'label' => $ageGroup,
                'data' => $row,
                'backgroundColor' => $ageColorPalette[$i % count($ageColorPalette)]
            ];
        } {
            $baseQuery = DB::table('data_karyawan')
                ->whereRaw("LOWER(status_kepegawaian) LIKE '%outsour%'");

            $employees = (clone $baseQuery)->get();
            $totalOutsourcing = $employees->count();


            // Analisis Gender by Location (for grouped chart)
            $genderLocationRaw = (clone $baseQuery)
                ->select('lokasi', 'gender', DB::raw('COUNT(*) as total'))
                ->whereNotNull('gender')
                ->whereNotNull('lokasi')
                ->groupBy('lokasi', 'gender')
                ->orderBy('lokasi')
                ->get();

            // Prepare data for grouped/stacked bar chart
            $locations = $genderLocationRaw->pluck('lokasi')->unique()->values();
            $genders = $genderLocationRaw->pluck('gender')->unique()->values();
            $colorPalette = ['#4f46e5', '#db2777', '#f97316', '#16a34a', '#8b5cf6'];
            $genderLocationMatrix = [];
            foreach ($genders as $i => $gender) {
                $row = [];
                foreach ($locations as $lokasi) {
                    $found = $genderLocationRaw->first(fn($item) => $item->lokasi == $lokasi && $item->gender == $gender);
                    $row[] = $found ? $found->total : 0;
                }
                $genderLocationMatrix[] = [
                    'label' => $gender,
                    'data' => $row,
                    'backgroundColor' => $colorPalette[$i % count($colorPalette)]
                ];
            }

            // Analisis Generasi (Usia)
            $ageGroupsData = (clone $baseQuery)->select(
                DB::raw("CASE 
                WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tanggal_lahir, '%d/%m/%Y'), CURDATE()) BETWEEN 13 AND 28 THEN 'Gen Z'
                WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tanggal_lahir, '%d/%m/%Y'), CURDATE()) BETWEEN 29 AND 44 THEN 'Gen Y (Milenial)'
                WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tanggal_lahir, '%d/%m/%Y'), CURDATE()) BETWEEN 45 AND 60 THEN 'Gen X'
                WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tanggal_lahir, '%d/%m/%Y'), CURDATE()) BETWEEN 61 AND 79 THEN 'Baby Boomers'
                WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(tanggal_lahir, '%d/%m/%Y'), CURDATE()) >= 79 THEN 'Pre-Boomer'
                ELSE 'Lainnya'
            END as age_group, COUNT(*) as total")
            )
                ->whereNotNull('tanggal_lahir')
                ->groupBy('age_group')
                ->orderBy(DB::raw("CASE 
                WHEN age_group = 'Pre-Boomer' THEN 1
                WHEN age_group = 'Baby Boomers' THEN 2
                WHEN age_group = 'Gen X' THEN 3
                WHEN age_group = 'Gen Y (Milenial)' THEN 4
                WHEN age_group = 'Gen Z' THEN 5
                ELSE 6
            END"))
                ->get();

            // Analisis Unit
            $unitCounts = (clone $baseQuery)
                ->select('unit', DB::raw('COUNT(*) as total'))
                ->whereNotNull('unit')
                ->groupBy('unit')
                ->orderBy('total', 'desc')
                ->get();

            return view('analitik.outsourcing', [
                'employees' => $employees,
                'totalOutsourcing' => $totalOutsourcing,
                // For grouped gender chart
                'genderLocationLabels' => $locations,
                'genderLocationDatasets' => $genderLocationMatrix,
                // For grouped age chart
                'ageLocationLabels' => $ageLocations,
                'ageLocationDatasets' => $ageLocationDatasets,
                // For fallback/simple chart (if needed)
                // 'genderCounts' => $genderCounts,
                'ageGroupsData' => $ageGroupsData,
                'unitCounts' => $unitCounts,
                'instansiLocationLabels' => $instansiLocationLabels,
                'instansiLocationDatasets' => $instansiLocationDatasets,
            ]);
        }
    }

    private function getHighestEducation($data)
    {
        $educationOrder = ['S3', 'S2', 'S1', 'D4', 'D3', 'D2', 'D1', 'SMA/SMK', 'SMP'];

        foreach ($educationOrder as $education) {
            if (isset($data[$education]) && $data[$education] > 0) {
                return $education;
            }
        }

        return 'Tidak ada data';
    }
}