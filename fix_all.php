<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Dosen;
use App\Models\User;
use Illuminate\Support\Str;

// 1. UPDATE DB RECORDS
$dosenUpdates = [
    'Dr. Tri Wibawa, S.T., M.T.' => 'Dr. Ir. Tri Wibawa, S.T., M.T.',
    'Laila Nafisah, S.T., M.T.' => 'Ir. Laila Nafisah, S.T., M.T.',
    'Andreas Mahendra Kuncoro, S.T., M.Sc., M.Sc., Ph.D' => 'Ir. Andreas Mahendro Kuncoro, S.T., M.Sc., M.Sc., Ph.D', // Name fixed to Mahendro
    'Intan Berlianty, S.T., M.T.' => 'Ir. Intan Berlianty, S.T., M.T.',
    'R. Achmad Chairdino Leuveano, S.T., M.Sc., Ph.D' => 'Ir. R. Achmad Chairdino Leuveano, S.T., M.Sc., Ph.D',
    'Eko Nursubiyantoro, S.T., M.T.' => 'Ir. Eko Nursubiyantoro, S.T., M.T.',
    'Gunawan Madyono Putro, S.T., M.T.' => 'Ir. Gunawan Madyono Putro, S.T., M.T.',
    'Dr. Sadi, S.T., M.T.' => 'Dr. Ir. Sadi, S.T., M.T.',
    'Dr. Apriani Soepardi, S.T.P., M.T.' => 'Dr. Ir. Apriani Soepardi, S.T.P., M.T.',
];

foreach ($dosenUpdates as $oldName => $newName) {
    // Update Dosen Table
    $dosen = Dosen::where('nama', $oldName)->first();
    if ($dosen) {
        $dosen->nama = $newName;
        $dosen->save();
    }
    
    // Also try partial match for "Andreas" in Dosen
    if (strpos($oldName, 'Andreas') !== false) {
        $d = Dosen::where('nama', 'LIKE', '%Andreas Mahendra Kuncoro%')->first();
        if ($d) {
            $d->nama = $newName;
            $d->save();
        }
    }
    
    // Update User Table
    $user = User::where('name', $oldName)->first();
    if ($user) {
        $user->name = $newName;
        $user->save();
    }
    
    // Partial match for User
    if (strpos($oldName, 'Andreas') !== false) {
        $u = User::where('name', 'LIKE', '%Andreas Mahendra Kuncoro%')->first();
        if ($u) {
            $u->name = $newName;
            $u->save();
        }
    }
}

// 2. TEXT REPLACEMENTS IN CODEBASE
function replaceInDir($dir, $replacements) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && in_array($file->getExtension(), ['php', 'blade'])) {
            $content = file_get_contents($file->getPathname());
            $newContent = strtr($content, $replacements);
            if ($newContent !== $content) {
                file_put_contents($file->getPathname(), $newContent);
                echo "Updated: " . $file->getPathname() . "\n";
            }
        }
    }
}

$replacements = [
    "'Seminar TA 2'" => "'Sidang TA 2'",
    '"Seminar TA 2"' => '"Sidang TA 2"',
    "'Sidang TA 1'" => "'Seminar TA 1'",
    '"Sidang TA 1"' => '"Seminar TA 1"',
    ">Seminar TA 2<" => ">Sidang TA 2<",
    ">Sidang TA 1<" => ">Seminar TA 1<",
    "Jadwal Seminar TA 2" => "Jadwal Sidang TA 2",
    "Jadwal Sidang TA 1" => "Jadwal Seminar TA 1",
    // Dosen names in DosenSeeder.php
    "'nama' => 'Dr. Tri Wibawa, S.T., M.T.'" => "'nama' => 'Dr. Ir. Tri Wibawa, S.T., M.T.'",
    "'nama' => 'Laila Nafisah, S.T., M.T.'" => "'nama' => 'Ir. Laila Nafisah, S.T., M.T.'",
    "'nama' => 'Andreas Mahendra Kuncoro, S.T., M.Sc., M.Sc., Ph.D'" => "'nama' => 'Ir. Andreas Mahendro Kuncoro, S.T., M.Sc., M.Sc., Ph.D'",
    "'nama' => 'Intan Berlianty, S.T., M.T.'" => "'nama' => 'Ir. Intan Berlianty, S.T., M.T.'",
    "'nama' => 'R. Achmad Chairdino Leuveano, S.T., M.Sc., Ph.D'" => "'nama' => 'Ir. R. Achmad Chairdino Leuveano, S.T., M.Sc., Ph.D'",
    "'nama' => 'Eko Nursubiyantoro, S.T., M.T.'" => "'nama' => 'Ir. Eko Nursubiyantoro, S.T., M.T.'",
    "'nama' => 'Gunawan Madyono Putro, S.T., M.T.'" => "'nama' => 'Ir. Gunawan Madyono Putro, S.T., M.T.'",
    "'nama' => 'Dr. Sadi, S.T., M.T.'" => "'nama' => 'Dr. Ir. Sadi, S.T., M.T.'",
    "'nama' => 'Dr. Apriani Soepardi, S.T.P., M.T.'" => "'nama' => 'Dr. Ir. Apriani Soepardi, S.T.P., M.T.'",
];

replaceInDir(__DIR__.'/app', $replacements);
replaceInDir(__DIR__.'/resources/views', $replacements);
replaceInDir(__DIR__.'/database/seeders', $replacements);

echo "Done!\n";
