<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sidang extends Model
{
    protected $table = 'sidang';

    protected $fillable = [
        'mahasiswa_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'ruangan',
        'jenis_sidang',
        'penguji1_id',
        'penguji2_id',
        'penguji3_id',
        'penguji4_id',
        'ketua_sidang_id',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function penguji1(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'penguji1_id');
    }

    public function penguji2(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'penguji2_id');
    }

    public function penguji3(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'penguji3_id');
    }

    public function penguji4(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'penguji4_id');
    }

    public function ketuaSidang(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'ketua_sidang_id');
    }
}
