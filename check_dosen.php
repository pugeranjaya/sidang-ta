<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$dosen = \App\Models\Dosen::where('nama', 'LIKE', '%Chairdino%')->first();
echo "Dosen ID: " . $dosen->id . "\n";

$mengajar = \App\Models\JadwalMengajar::where('dosen_id', $dosen->id)
    ->where('hari', 'Senin')
    ->get();
echo "\n--- Jadwal Mengajar Senin ---\n";
foreach ($mengajar as $m) {
    echo "Jam: {$m->jam_mulai} - {$m->jam_selesai} | MK: {$m->mata_kuliah} | Ruang: {$m->ruangan}\n";
}
if($mengajar->isEmpty()) echo "Kosong\n";

$sidang = \App\Models\Sidang::where('tanggal', '2026-06-08')
    ->where(function($q) use ($dosen) {
        $q->whereHas('mahasiswa', function($q2) use ($dosen) {
            $q2->where('pembimbing_id', $dosen->id);
        })
        ->orWhere('ketua_sidang_id', $dosen->id)
        ->orWhere('penguji1_id', $dosen->id)
        ->orWhere('penguji2_id', $dosen->id)
        ->orWhere('penguji3_id', $dosen->id)
        ->orWhere('penguji4_id', $dosen->id);
    })
    ->get();
echo "\n--- Jadwal Sidang 8 Juni 2026 ---\n";
foreach ($sidang as $s) {
    echo "Jam: {$s->jam_mulai} - {$s->jam_selesai} | Ruang: {$s->ruangan}\n";
}
if($sidang->isEmpty()) echo "Kosong\n";
