<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Mahasiswa extends Model
{
    protected $table = 'mahasiswa'; // singular
    protected $fillable = ['nama', 'nim', 'judul_ta', 'rumpun_ta', 'pembimbing_id'];
    // relasi pembimbing

    // Cast kalau rumpun_ta nanti pakai multiple (array/JSON)
    protected $casts = [
        'rumpun_ta' => 'string',  // kalau rumpun_ta cuma string single
        // kalau nanti mau multiple: 'rumpun_ta' => 'array',
    ];

    // Relasi ke dosen pembimbing (opsional tapi bagus)
    public function pembimbing(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'pembimbing_id');
    }
}