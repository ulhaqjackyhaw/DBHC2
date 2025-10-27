<?php

namespace App\Exports;

use App\Models\Version;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class VersionDataExport implements FromCollection, WithHeadings, WithMapping
{
    protected $versionId;

    public function __construct($versionId)
    {
        $this->versionId = $versionId;
    }

    public function collection()
    {
        return Version::findOrFail($this->versionId)->history;
    }

    public function headings(): array
    {
        return [
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
            'tmt'
        ];
    }

    public function map($employeeHistory): array
    {
        // Calculate age from tanggal_lahir
        $age = '';
        if ($employeeHistory->tanggal_lahir) {
            try {
                $birthDate = Carbon::createFromFormat('d/m/Y', $employeeHistory->tanggal_lahir);
                $age = $birthDate->age;
            } catch (\Exception $e) {
                $age = '';
            }
        }

        // Calculate work experience from tmt
        $workExperience = '';
        if ($employeeHistory->tmt) {
            try {
                $tmtDate = Carbon::createFromFormat('d/m/Y', $employeeHistory->tmt);
                $workExperience = $tmtDate->diffInYears(Carbon::now());
            } catch (\Exception $e) {
                $workExperience = '';
            }
        }

        return [
            $employeeHistory->nik,
            $employeeHistory->nama,
            $employeeHistory->gender,
            $employeeHistory->kode_jabatan,
            $employeeHistory->lokasi,
            $employeeHistory->unit,
            $employeeHistory->jabatan,
            $employeeHistory->kelompok_kelas_jabatan,
            $employeeHistory->grade,
            $employeeHistory->status_kepegawaian,
            $employeeHistory->tanggal_lahir,
            $age,
            $employeeHistory->pendidikan_terakhir,
            $employeeHistory->tmt,
            $workExperience
        ];
    }
}
