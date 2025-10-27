<?php

namespace App\Exports;

use App\Models\DataKaryawan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class DataKaryawanExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DataKaryawan::orderBy('nama')
            ->orderBy('lokasi')
            ->orderBy('unit')
            ->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'NIK',
            'Nama',
            'GENDER',
            'KODE JABATAN',
            'LOKASI',
            'UNIT',
            'JABATAN',
            'KELOMPOK KELAS JABATAN',
            'GRADE',
            'STATUS KEPEGAWAIAN',
            'ASAL INSTANSI',
            'TANGGAL LAHIR',
            'USIA (TAHUN)',
            'PENDIDIKAN TERAKHIR',
            'TMT',
            'MASA KERJA (TAHUN)',
        ];
    }

    /**
     * @param mixed $dataKaryawan
     * @return array
     */
    public function map($dataKaryawan): array
    {
        // Hitung sisa formasi (vacancy) berdasarkan kode_jabatan, lokasi, unit
        $formasi = \App\Models\Formasi::where('kode_jabatan', $dataKaryawan->kode_jabatan)
            ->where('lokasi', $dataKaryawan->lokasi)
            ->where('unit', $dataKaryawan->unit)
            ->first();
        $kuota = $formasi ? $formasi->kuota : 1;
        $terisi = \App\Models\DataKaryawan::where('kode_jabatan', $dataKaryawan->kode_jabatan)
            ->where('lokasi', $dataKaryawan->lokasi)
            ->where('unit', $dataKaryawan->unit)
            ->count();
        $sisaFormasi = $kuota - $terisi;
        return [
            $dataKaryawan->nik,
            $dataKaryawan->nama,
            $dataKaryawan->gender,
            $dataKaryawan->kode_jabatan,
            $dataKaryawan->lokasi,
            $dataKaryawan->unit,
            $dataKaryawan->jabatan,
            $dataKaryawan->kelompok_kelas_jabatan,
            $dataKaryawan->grade,
            $dataKaryawan->status_kepegawaian,
            $dataKaryawan->asal_instansi,
            $dataKaryawan->tanggal_lahir,
            $this->calculateAge($dataKaryawan->tanggal_lahir),
            $dataKaryawan->pendidikan_terakhir,
            $dataKaryawan->tmt,
            $this->calculateWorkPeriod($dataKaryawan->tmt),
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // Style header row
        $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '059669'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Style data rows with alternating colors
        for ($row = 2; $row <= $highestRow; $row++) {
            $fillColor = ($row % 2 == 0) ? 'F0FDF4' : 'FFFFFF';
            $sheet->getStyle('A' . $row . ':' . $highestColumn . $row)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $fillColor],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E7EB'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
        }

        // Center align specific columns (NIK, Gender, Grade, Usia, Masa Kerja)
        $sheet->getStyle('A2:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C2:C' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('I2:I' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('M2:M' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('P2:P' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15,  // NIK
            'B' => 30,  // Nama
            'C' => 12,  // Gender
            'D' => 25,  // Kode Jabatan
            'E' => 35,  // Lokasi
            'F' => 35,  // Unit
            'G' => 40,  // Jabatan
            'H' => 28,  // Kelompok Kelas Jabatan
            'I' => 10,  // Grade
            'J' => 22,  // Status Kepegawaian
            'K' => 30,  // Asal Instansi
            'L' => 15,  // Tanggal Lahir
            'M' => 12,  // Usia
            'N' => 22,  // Pendidikan Terakhir
            'O' => 15,  // TMT
            'P' => 15,  // Masa Kerja
        ];
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

        if (!$parsedDate)
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

        if (!$parsedDate)
            return 0;

        $today = new \DateTime();
        $workYears = $today->diff($parsedDate)->y;

        return $workYears;
    }
}