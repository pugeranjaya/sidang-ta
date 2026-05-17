<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use Faker\Factory as Faker;

class MahasiswaSeeder extends Seeder
{
    public function run(): void
    {
        // Kosongkan tabel mahasiswa terlebih dahulu
        Mahasiswa::query()->delete();

        $dosenIds = Dosen::pluck('id')->toArray();
        if (empty($dosenIds)) {
            return;
        }

        $faker = Faker::create('id_ID');
        $dosenCount = count($dosenIds);
        $rumpunList = ['SPOI', 'PSIE', 'KPDP', 'RPTE'];

        // Buat 200 mahasiswa dengan pembagian dosen pembimbing yang merata
        for ($i = 0; $i < 200; $i++) {
            // Sequential distribution untuk pemerataan:
            // Mahasiswa 1 ke Dosen 1, Mahasiswa 2 ke Dosen 2...
            // Hasilnya: 200 / 21 = ~9-10 mahasiswa per dosen
            $pembimbingId = $dosenIds[$i % $dosenCount];

            Mahasiswa::create([
                'nim' => '210' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'nama' => $faker->name(),
                'judul_ta' => ucfirst($faker->words(3, true)) . ' pada Sistem ' . ucfirst($faker->word()),
                'rumpun_ta' => $rumpunList[array_rand($rumpunList)],
                'pembimbing_id' => $pembimbingId,
            ]);
        }
    }
}
