<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin'); 
    // <-- Ganti jadi /admin
});

Route::get('/seed-db', function() {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
        return 'Database migrated and seeded perfectly!';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();
    }
});
