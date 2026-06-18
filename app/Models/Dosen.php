<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    protected $table = 'dosen'; // singular
    protected $fillable = [
        'nama',
        'email',
        'nip',
        'rumpun_keahlian',  // ini penting untuk array/json
        'status',
        'alasan_nonaktif',
        'abaikan_bentrok',
    ];

    // Opsional: cast rumpun_keahlian jadi array supaya Laravel handle otomatis
    protected $casts = [
        'rumpun_keahlian' => 'array',  // atau 'json' kalau pakai json
        'status' => 'boolean',
        'abaikan_bentrok' => 'boolean',
    ];

    /**
     * Relasi: Mahasiswa yang dibimbing oleh dosen ini
     */
    public function pembimbingMahasiswas()
    {
        return $this->hasMany(\App\Models\Mahasiswa::class, 'pembimbing_id');
    }

    public function penguji1Sidangs()
    {
        return $this->hasMany(Sidang::class, 'penguji1_id');
    }

    public function penguji2Sidangs()
    {
        return $this->hasMany(Sidang::class, 'penguji2_id');
    }

    public function penguji3Sidangs()
    {
        return $this->hasMany(Sidang::class, 'penguji3_id');
    }

    public function penguji4Sidangs()
    {
        return $this->hasMany(Sidang::class, 'penguji4_id');
    }

    public function ketuaSidangs()
    {
        return $this->hasMany(Sidang::class, 'ketua_sidang_id');
    }
}