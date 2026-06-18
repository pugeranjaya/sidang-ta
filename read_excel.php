<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Facades\Excel;

$data = Excel::toArray(new class implements ToArray {
    public function array(array $array) {}
}, 'Matriks Pembimbing-Penguji 2024-2025.xlsx');

print_r(array_slice($data[0], 4, 6));
