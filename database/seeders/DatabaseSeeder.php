<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'role' => 'admin',
            'email' => 'adminjurusanti@upnyk.ac.id',
            'password' => Hash::make('adminjurusan'),
        ]);

        User::create([
            'name' => 'Dosen',
            'role' => 'dosen',
            'email' => 'dosenti@upnyk.ac.id',
            'password' => Hash::make('dosenjurusan'),
        ]);

        $this->call([
            DosenSeeder::class,
            MahasiswaSeeder::class,
            JadwalMengajarSeeder::class,
        ]);
    }
}
