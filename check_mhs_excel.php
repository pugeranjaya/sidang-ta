<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use PhpOffice\PhpSpreadsheet\IOFactory;

$spreadsheet = IOFactory::load('C:\xampp\htdocs\sidang-ta\PENDAFTAR TA2_JUNI-26.xlsx');
$worksheet = $spreadsheet->getActiveSheet();
$rows = $worksheet->toArray();

$header = array_shift($rows); // row 1
$pembimbingCol = -1;
$namaCol = -1;
$nimCol = -1;

foreach ($header as $index => $colName) {
    $col = strtolower(trim($colName));
    if (trim($col) === 'dosen pembimbing') {
        $pembimbingCol = $index;
    }
    if (trim($col) === 'nama lengkap (huruf balok)') {
        $namaCol = $index;
    }
    if (trim($col) === 'nomor mahasiswa (npm)') {
        $nimCol = $index;
    }
}

if ($pembimbingCol === -1 || $namaCol === -1) {
    die("Kolom tidak ditemukan\n");
}

$students = [];
foreach ($rows as $row) {
    if (!isset($row[$pembimbingCol])) continue;
    $pembimbing = $row[$pembimbingCol];
    if (stripos($pembimbing, 'Chairdino') !== false) {
        $students[] = [
            'nama' => $row[$namaCol],
            'nim' => $row[$nimCol] ?? ''
        ];
    }
}

if (empty($students)) {
    die("Tidak ada mahasiswa bimbingan Chairdino di Excel.\n");
}

$scheduler = new \App\Services\SidangScheduler();

foreach ($students as $stu) {
    echo "=======================================\n";
    echo "Mahasiswa dari Excel: {$stu['nama']} ({$stu['nim']})\n";
    
    // cari di DB
    $m = \App\Models\Mahasiswa::where('nama', 'LIKE', '%' . trim($stu['nama']) . '%')->first();
    if (!$m) {
        echo "=> ERROR: Mahasiswa tidak ditemukan di database!\n";
        continue;
    }
    
    echo "Ditemukan di DB, ID: {$m->id}\n";
    
    try {
        $options = $scheduler->generateOptionsForMahasiswa($m->id, '2026-06-08', '2026-06-08', 'Sidang TA 2');
        
        if (isset($options['options']) && count($options['options']) > 0) {
            echo "=> BISA! Ada " . count($options['options']) . " opsi jadwal.\n";
            foreach ($options['options'] as $idx => $opt) {
                echo "   Opsi " . ($idx+1) . ": Jam {$opt['slot']['jam_mulai']} - {$opt['slot']['jam_selesai']} (Ruang: {$opt['ruangan']})\n";
                if(isset($opt['pengujis'])) {
                   foreach($opt['pengujis'] as $p) {
                       echo "     - {$p->nama}\n";
                   }
                }
            }
        } else {
            echo "=> GAGAL dapat jadwal. Error: " . ($options['error'] ?? 'Tidak ada opsi yang tersedia.') . "\n";
            if (isset($options['error'])) {
               // If there's error, it might be related to no valid slots due to conflicts
            }
        }
    } catch (\Exception $e) {
        echo "=> ERROR Exception: " . $e->getMessage() . "\n";
    }
}
