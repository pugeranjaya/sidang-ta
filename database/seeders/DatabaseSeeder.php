<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'adminjurusanti@upnyk.ac.id'],
            [
                'name' => 'Admin',
                'role' => 'admin',
                'password' => Hash::make('adminjurusan'),
            ]
        );

        User::firstOrCreate(
            ['email' => 'dosenti@upnyk.ac.id'],
            [
                'name' => 'Dosen',
                'role' => 'dosen',
                'password' => Hash::make('dosenjurusan'),
            ]
        );

        $this->call([
            DosenSeeder::class,
            MahasiswaSeeder::class,
            JadwalMengajarSeeder::class,
        ]);
    }
}
