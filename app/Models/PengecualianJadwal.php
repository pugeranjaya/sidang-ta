<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengecualianJadwal extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal',
        'jam_mulai_diblokir',
        'jam_selesai_diblokir',
        'keterangan',
    ];
}
