<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

DB::statement("ALTER TABLE sidang MODIFY COLUMN status ENUM('pending', 'disetujui', 'selesai', 'lulus', 'tidak lulus', 'belum lulus') DEFAULT 'belum lulus'");
DB::statement("UPDATE sidang SET status = 'lulus' WHERE status IN ('selesai', 'disetujui')");
DB::statement("UPDATE sidang SET status = 'belum lulus' WHERE status IN ('pending', 'tidak lulus')");
DB::statement("ALTER TABLE sidang MODIFY COLUMN status ENUM('belum lulus', 'lulus') DEFAULT 'belum lulus'");
echo 'DB Updated!';
