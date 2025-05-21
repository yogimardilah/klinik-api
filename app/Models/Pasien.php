<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pasien extends Model
{
    protected $table = 'pasiens';  // optional jika sudah default sesuai nama tabel

    protected $fillable = [
        'nama',
        'alamat',
        'telepon',
        'nik',
        'no_hp',
        'tanggal_lahir',
        'jenis_kelamin',
        'created_at',
        'updated_at'
    ];

    // Jika kamu ingin otomatis atur created_at & updated_at, pastikan $timestamps = true
    public $timestamps = true;
}
