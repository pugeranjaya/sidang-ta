<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Dosen;
use App\Models\JadwalMengajar;

class JadwalMengajarSeeder extends Seeder
{
    private function getCleanName($name)
    {
        // Bersihkan gelar dan singkatan
        $name = strtolower(preg_replace('/[^a-zA-Z\s]/', ' ', $name));
        $words = explode(' ', $name);
        $cleanWords = [];
        $titles = ['dr', 'ir', 'st', 'mt', 'phd', 'msc', 'meng', 'spd', 'mm', 'mor', 'stp', 'deng', 'ipu', 'asean', 'eng', 'msie', 'prof', 'mhum', 'ssi', 'msi', 'cipm'];
        foreach ($words as $w) {
            $w = trim($w);
            if (empty($w) || strlen($w) <= 1 || in_array($w, $titles))
                continue;
            $cleanWords[] = $w;
        }
        return implode(' ', $cleanWords);
    }

    public function run(): void
    {
        // 1. Ambil semua dosen yang ada di DB
        $dosens = Dosen::all();
        if ($dosens->isEmpty()) {
            return;
        }

        $dosenMap = [];
        foreach ($dosens as $d) {
            $key = $this->getCleanName($d->nama);
            $dosenMap[$key] = $d->id;
        }

        // 2. Baca file teks
        $filePath = base_path('../jadwal dosen.txt');
        if (!file_exists($filePath)) {
            return; // File not found, skip silently
        }

        $content = file_get_contents($filePath);
        $lines = explode("\n", $content);

        $schedules = [];
        $currentIdx = -1;

        // Parsing File
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line))
                continue;
            if (in_array($line, ['Prodi', 'Kode Mata Kuliah', 'Nama Mata Kuliah', 'Kelas', 'SKS', 'Jumlah Mahasiswa', 'Jadwal', 'Ruang', 'Dosen'])) {
                continue;
            }

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

        // 3. Masukkan Jadwal yang dosennya MATCH dengan DB
        foreach ($schedules as $s) {
            // "Senin 07:00-08:45"
            $parts = explode(' ', $s['jadwal_raw']);
            if (count($parts) < 2)
                continue;

            $hari = $parts[0];
            $times = explode('-', $parts[1]);
            if (count($times) < 2)
                continue;

            $jamMulai = trim($times[0]);
            $jamSelesai = trim($times[1]);

            foreach ($s['lecturers'] as $lName) {
                // Cari dosen_id berdasarkan nama yang di bersihkan
                $cleanRaw = $this->getCleanName($lName);
                $dosenId = null;

                if (isset($dosenMap[$cleanRaw])) {
                    $dosenId = $dosenMap[$cleanRaw];
                } else {
                    // Coba fuzzy match (mirip 85% ke atas atau kata pertamanya sama)
                    foreach ($dosenMap as $dbClean => $id) {
                        similar_text($cleanRaw, $dbClean, $perc);
                        if ($perc > 80 || strpos($dbClean, $cleanRaw) !== false || strpos($cleanRaw, $dbClean) !== false) {
                            $dosenId = $id;
                            break;
                        }
                    }
                }

                // HARUS dosen yang sudah ada di DB
                if ($dosenId) {
                    // Hindari duplikat jika seeder dijalankan ulang, tapi karena kita migrate:fresh ya aman.
                    JadwalMengajar::create([
                        'dosen_id' => $dosenId,
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
    }
}
