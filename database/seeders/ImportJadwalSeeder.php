<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Dosen;
use App\Models\JadwalMengajar;
use App\Models\Mahasiswa;
use App\Models\Sidang;
use Illuminate\Support\Facades\DB;

class ImportJadwalSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Clear everything to avoid conflicts
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Sidang::truncate();
        JadwalMengajar::truncate();
        Mahasiswa::truncate();
        Dosen::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $filePath = 'c:/xampp/htdocs/sidang-ta/jadwal dosen.txt';
        if (!file_exists($filePath)) {
            $this->command->error("File not found: $filePath");
            return;
        }

        $content = file_get_contents($filePath);
        $lines = explode("\n", $content);

        $schedules = [];
        $lecturerNames = [];
        $currentIdx = -1;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line))
                continue;

            // Skip headers
            if (in_array($line, ['Prodi', 'Kode Mata Kuliah', 'Nama Mata Kuliah', 'Kelas', 'SKS', 'Jumlah Mahasiswa', 'Jadwal', 'Ruang', 'Dosen'])) {
                continue;
            }

            if (strpos($line, "TEKNIK INDUSTRI") === 0) {
                $parts = explode("\t", $line);
                if (count($parts) >= 8) {
                    $currentIdx++;
                    $schedules[$currentIdx] = [
                        'kode' => $parts[1],
                        'nama_mk' => $parts[2],
                        'kelas' => $parts[3],
                        'sks' => $parts[4],
                        'jadwal_raw' => $parts[6],
                        'ruang' => $parts[7],
                        'lecturers' => []
                    ];
                }
            } else if ($currentIdx !== -1) {
                $schedules[$currentIdx]['lecturers'][] = $line;
                $lecturerNames[] = $line;
            }
        }

        $uniqueLecturerNames = array_unique($lecturerNames);
        sort($uniqueLecturerNames);

        // 2. Insert Dosen
        $dosenMap = [];
        $rumpunList = ['SPOI', 'PSIE', 'KPDP', 'RPTE'];
        foreach ($uniqueLecturerNames as $name) {
            $dosen = Dosen::create([
                'nama' => $name,
                'nip' => '19' . rand(70, 95) . sprintf('%02d', rand(1, 12)) . sprintf('%02d', rand(1, 28)) . rand(2000, 2024) . rand(1, 2) . rand(0, 9) . rand(0, 9) . rand(0, 9),
                'rumpun_keahlian' => [$rumpunList[array_rand($rumpunList)]],
            ]);
            $dosenMap[$name] = $dosen->id;
        }

        // 3. Insert JadwalMengajar
        foreach ($schedules as $s) {
            // Parse jadwal_raw: "Senin 07:00-08:45"
            $parts = explode(' ', $s['jadwal_raw']);
            if (count($parts) < 2)
                continue;

            $hari = $parts[0];
            $times = explode('-', $parts[1]);
            if (count($times) < 2)
                continue;

            $jamMulai = $times[0];
            $jamSelesai = $times[1];

            foreach ($s['lecturers'] as $lName) {
                if (isset($dosenMap[$lName])) {
                    JadwalMengajar::create([
                        'dosen_id' => $dosenMap[$lName],
                        'hari' => $hari,
                        'jam_mulai' => $jamMulai,
                        'jam_selesai' => $jamSelesai,
                        'ruangan' => $s['ruang'],
                        'mata_kuliah' => $s['nama_mk'],
                        'tahun_akademik' => '2025/2026',
                    ]);
                }
            }
        }

        // 4. Insert dummy Mahasiswa (200 records with even pembimbing distribution)
        $faker = \Faker\Factory::create('id_ID');
        $allDosenIds = array_values($dosenMap);
        $dosenCount = count($allDosenIds);
        $rumpunList = ['SPOI', 'PSIE', 'KPDP', 'RPTE'];

        for ($i = 0; $i < 200; $i++) {
            $pembimbingId = $allDosenIds[$i % $dosenCount];

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
