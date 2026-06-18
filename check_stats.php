<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$map = App\Filament\Resources\StatistikDosen\StatistikDosenResource::getWithCountMap('bulanan', 'semua', 'semua', 'semua');
$dosens = App\Models\Dosen::withCount($map)->get();

foreach($dosens as $d) {
    if ($d->pembimbing_periode_count > 0 || $d->ketua_periode_count > 0 || $d->p1_periode_count > 0) {
        echo $d->nama . " - Pemb: " . $d->pembimbing_periode_count . " Ketua: " . $d->ketua_periode_count . " Penguji: " . ($d->p1_periode_count + $d->p2_periode_count) . "\n";
    }
}
