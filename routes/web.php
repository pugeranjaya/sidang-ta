<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;


Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/seed-db', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
        return 'Database migrated and seeded perfectly!';
    } catch (\Throwable $e) {
        return 'Error: ' . $e->getMessage() . "\n\nFile: " . $e->getFile() . ':' . $e->getLine() . "\n\nTrace: " . $e->getTraceAsString();
    }
});

Route::get('/auto-migrate-neon', function () {
    try {
        // Kita tes koneksi database dulu sebelum migrate
        DB::connection()->getPdo();

        // Jalankan migrasi
        Artisan::call('migrate', ['--force' => true]);

        return "Mantap, cok! Database berhasil di-migrate ke Neon. Output: " . Artisan::output();
    } catch (\Exception $e) {
        return "Gagal konek/migrate, error: " . $e->getMessage();
    }
});
