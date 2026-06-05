<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$m = \App\Models\Mahasiswa::find(322); // MUHAMMAD WAHYU GUNAWAN
$historyTa1 = \App\Models\Sidang::where('mahasiswa_id', $m->id)
    ->where('jenis_sidang', 'Seminar TA 1')
    ->where('status', 'lulus')
    ->latest()->first();

$dosenIds = [$m->pembimbing_id, $historyTa1->ketua_sidang_id, $historyTa1->penguji1_id, $historyTa1->penguji2_id];
foreach($dosenIds as $did) {
    $d = \App\Models\Dosen::find($did);
    $mengajar = \App\Models\JadwalMengajar::where('dosen_id', $did)->where('hari', 'Senin')->get();
    echo "\nDosen: {$d->nama}\n";
    if($mengajar->isEmpty()) echo "Kosong\n";
    foreach($mengajar as $mj) echo "Mengajar: {$mj->jam_mulai} - {$mj->jam_selesai}\n";
    
    // Also check existing sidangs on 2026-06-08
    $sidangs = \App\Models\Sidang::where('tanggal', '2026-06-08')
        ->where(function($q) use ($did) {
            $q->whereHas('mahasiswa', function($q2) use ($did) {
                $q2->where('pembimbing_id', $did);
            })
            ->orWhere('ketua_sidang_id', $did)
            ->orWhere('penguji1_id', $did)
            ->orWhere('penguji2_id', $did)
            ->orWhere('penguji3_id', $did)
            ->orWhere('penguji4_id', $did);
        })->get();
    if(!$sidangs->isEmpty()) {
        foreach($sidangs as $s) echo "Sidang: {$s->jam_mulai} - {$s->jam_selesai}\n";
    }
}
