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
    ];

    // Opsional: cast rumpun_keahlian jadi array supaya Laravel handle otomatis
    protected $casts = [
        'rumpun_keahlian' => 'array',  // atau 'json' kalau pakai json
    ];

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