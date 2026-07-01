<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/seed-db', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);
        // db:seed sudah otomatis jalan di dalam file migration 2026_07_01_020000_seed_dosen_users_from_image.php
        return 'Database migrated and seeded perfectly!';
    } catch (\Throwable $e) {
        return 'Error: ' . $e->getMessage() . "\n\nFile: " . $e->getFile() . ':' . $e->getLine() . "\n\nTrace: " . $e->getTraceAsString();
    }
});
