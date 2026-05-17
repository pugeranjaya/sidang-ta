<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalMengajar extends Model
{
    protected $table = 'jadwal_mengajar';  // singular

    protected $fillable = [
        'dosen_id',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'ruangan',
        'mata_kuliah',       // ← PASTIKAN ADA INI
        'tahun_akademik',    // ← PASTIKAN ADA INI
        // kalau ada field lain di form, tambahin juga
    ];

    // Relasi dosen (harus ada biar relationship('dosen', 'nama') jalan)
    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }
}