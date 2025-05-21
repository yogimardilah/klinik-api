<?php

namespace App\Exports;

use App\Models\Pasien;
use Maatwebsite\Excel\Concerns\FromCollection;

class PasienExport implements FromCollection
{
    public function collection()
    {
        return Pasien::select('nama', 'nik', 'no_hp', 'alamat', 'tanggal_lahir', 'jenis_kelamin')->get();
    }
}
