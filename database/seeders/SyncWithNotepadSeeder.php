<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Dosen;
use App\Models\JadwalMengajar;
use App\Models\Mahasiswa;
use App\Models\Sidang;
use Illuminate\Support\Facades\DB;

class SyncWithNotepadSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Reset everything
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Sidang::truncate();
        JadwalMengajar::truncate();
        Mahasiswa::truncate();
        Dosen::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Parse rumpun dosen.txt
        $rumpunFile = 'c:/xampp/htdocs/sidang-ta/rumpun dosen.txt';
        $rumpunContent = file_get_contents($rumpunFile);
        $lines = explode("\n", $rumpunContent);

        $dosenList = []; // Array of ['nama' => '', 'rumpun' => '']
        $currentRumpun = '';

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line))
                continue;

            if (strpos($line, 'KBK') === 0) {
                if (strpos($line, '(SPOI)') !== false)
                    $currentRumpun = 'SPOI';
                elseif (strpos($line, '(PSIE)') !== false)
                    $currentRumpun = 'PSIE';
                elseif (strpos($line, '(KPDP)') !== false)
                    $currentRumpun = 'KPDP';
                elseif (strpos($line, '(RPTE)') !== false)
                    $currentRumpun = 'RPTE';
                continue;
            }

            // Extract name from line like "1 Ir. Nur Indrianti..."
            if (preg_match('/^\d+\s+(.*)$/', $line, $matches)) {
                $name = trim($matches[1]);
                $dosenList[] = [
                    'nama' => $name,
                    'rumpun' => $currentRumpun
                ];
            }
        }

        // 3. Insert Dosen
        $dosenMap = []; // Keyword -> ID
        foreach ($dosenList as $d) {
            $dosen = Dosen::create([
                'nama' => $d['nama'],
                'nip' => '19' . rand(70, 95) . sprintf('%02d', rand(1, 12)) . sprintf('%02d', rand(1, 28)) . rand(2000, 2024) . rand(1, 2) . rand(0, 9) . rand(0, 9) . rand(0, 9),
                'rumpun_keahlian' => [$d['rumpun']],
            ]);

            // Create keywords for matching (Nur Indrianti, etc)
            $keywords = explode(' ', str_replace(['Ir.', 'Dr.', 'S.T.', 'M.T.', 'M.Eng.', 'M.Sc.', 'Ph.D', 'IPU', 'ASEAN', 'Eng.', 'D.Eng.', 'S.Si.', 'S.T.P.', 'S.Pd.', 'MM.', 'M.Or.', ','], '', $d['nama']));
            foreach ($keywords as $kw) {
                if (strlen($kw) > 3) {
                    $dosenMap[strtolower($kw)] = $dosen->id;
                }
            }
            // Add specific mappings for tricky names
            if (strpos($d['nama'], 'Chaeron') !== false)
                $dosenMap['chaeron'] = $dosen->id;
            if (strpos($d['nama'], 'Shodiq') !== false)
                $dosenMap['shodiq'] = $dosen->id;
            if (strpos($d['nama'], 'Leuveano') !== false)
                $dosenMap['leuveano'] = $dosen->id;
        }

        // 4. Parse jadwal dosen.txt
        $jadwalFile = 'c:/xampp/htdocs/sidang-ta/jadwal dosen.txt';
        $jadwalLines = explode("\n", file_get_contents($jadwalFile));

        $schedules = [];
        $currentIdx = -1;

        foreach ($jadwalLines as $line) {
            $line = trim($line);
            if (empty($line))
                continue;
            if (in_array($line, ['Prodi', 'Kode Mata Kuliah', 'Nama Mata Kuliah', 'Kelas', 'SKS', 'Jumlah Mahasiswa', 'Jadwal', 'Ruang', 'Dosen']))
                continue;

            if (strpos($line, "TEKNIK INDUSTRI") === 0) {
                $parts = explode("\t", $line);
                if (count($parts) >= 8) {
                    $currentIdx++;
                    $schedules[$currentIdx] = [
                        'nama_mk' => $parts[2],
                        'jadwal_raw' => $parts[6],
                        'ruang' => $parts[7],
                        'lecturers' => []
                    ];
                }
            } else if ($currentIdx !== -1) {
                $schedules[$currentIdx]['lecturers'][] = $line;
            }
        }

        // 5. Insert JadwalMengajar (Only for canonical lecturers)
        foreach ($schedules as $s) {
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
                // Find match in dosenMap
                $foundId = null;
                $lNameLower = strtolower($lName);
                foreach ($dosenMap as $kw => $id) {
                    if (strpos($lNameLower, $kw) !== false) {
                        $foundId = $id;
                        break;
                    }
                }

                if ($foundId) {
                    JadwalMengajar::create([
                        'dosen_id' => $foundId,
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

        // 6. Dummy Mahasiswa (200 records distributed evenly)
        $allDosenIds = Dosen::pluck('id')->toArray();
        $dosenCount = count($allDosenIds);
        if ($dosenCount > 0) {
            $faker = \Faker\Factory::create('id_ID');
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

        $dCount = Dosen::count();
        $jCount = JadwalMengajar::count();
        echo "Successfully synced to canonical data: $dCount Dosen found in notepad, $jCount teaching schedules mapped.\n";
    }
}
