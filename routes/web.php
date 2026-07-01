<?php

use Illuminate\Support\Facades\Route;

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
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return "Mantap, cok! Database berhasil di-migrate ke Neon.";
    } catch (\Exception $e) {
        return "Gagal, nilainya: " . $e->getMessage();
    }
});
