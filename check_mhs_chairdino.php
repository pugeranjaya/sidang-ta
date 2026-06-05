<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$dosen = \App\Models\Dosen::where('nama', 'LIKE', '%Chairdino%')->first();
$mahasiswas = \App\Models\Mahasiswa::where('pembimbing_id', $dosen->id)
    ->whereNotIn('id', \App\Models\Sidang::pluck('mahasiswa_id'))
    ->get();

echo "Mahasiswa bimbingan Chairdino yang belum ada jadwal: " . $mahasiswas->count() . "\n\n";

$scheduler = new \App\Services\SidangScheduler();

foreach ($mahasiswas as $m) {
    echo "=======================================\n";
    echo "Mahasiswa: {$m->nama} ({$m->jenis_sidang})\n";
    try {
        $method = new \ReflectionMethod(\App\Services\SidangScheduler::class, 'generatePossibleSlots');
        $method->setAccessible(true);
        $slots = $method->invoke($scheduler, '2026-06-08', '2026-06-08', 'Seminar TA 1');
        echo "   (Total slots for 2026-06-08: " . count($slots) . ")\n";
        
        $options = $scheduler->generateOptionsForMahasiswa($m->id, '2026-06-08', '2026-06-08', 'Seminar TA 1');
        
        if (isset($options['options']) && count($options['options']) > 0) {
            echo "=> BISA! Ada " . count($options['options']) . " opsi jadwal.\n";
            foreach ($options['options'] as $idx => $opt) {
                echo "   Opsi " . $idx . ": Jam {$opt['slot']['jam_mulai']} - {$opt['slot']['jam_selesai']} (Ruang: {$opt['ruangan']})\n";
            }
        } else {
            echo "=> GAGAL dapat jadwal. Error: " . ($options['error'] ?? 'Tidak ada opsi yang tersedia.') . "\n";
        }
    } catch (\Exception $e) {
        echo "=> ERROR: " . $e->getMessage() . "\n";
    }
}
