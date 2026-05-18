<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin'); 
    // <-- Ganti jadi /admin
});

Route::get('/seed-db', function() {
    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
    return 'Database seeded!';
});
