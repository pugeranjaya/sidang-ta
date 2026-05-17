<?php

namespace App\Imports;

use App\Models\Dosen;
use App\Models\JadwalMengajar;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class JadwalMengajarImport implements ToModel, WithHeadingRow
{
    private int $imported = 0;
    private array $dosenMap = [];

    public function __construct()
    {
        // Cache semua dosen untuk mencocokkan nama
        $dosens = Dosen::all();
        foreach ($dosens as $d) {
            $key = $this->getCleanName($d->nama);
            $this->dosenMap[$key] = $d->id;
        }
    }

    private function getCleanName($name)
    {
        // Sama dengan seeder: Bersihkan gelar dan singkatan
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

    public function model(array $row): ?JadwalMengajar
    {
        // Cek baris kosong sepenuhnya
        $isEmptyRow = true;
        foreach ($row as $val) {
            if (!empty(trim((string) $val))) {
                $isEmptyRow = false;
                break;
            }
        }
        if ($isEmptyRow) {
            return null;
        }

        // Ambil data dengan penamaan header yang baru
        $namaDosenRaw = trim($row['nama'] ?? $row['nama_dosen'] ?? $row['dosen'] ?? '');
        $hari = trim($row['hari'] ?? '');
        $waktu = trim($row['waktu'] ?? $row['jam'] ?? '');
        $ruangan = trim($row['ruangan'] ?? $row['ruang'] ?? $row['kelas'] ?? '');
        $mata_kuliah = trim($row['mata_kuliah'] ?? $row['matkul'] ?? $row['mk'] ?? '');
        $tahun_akademik = trim($row['tahun_akademik'] ?? $row['ta'] ?? '');

        // Filter: Abaikan jika mata kuliah mengandung kata "Praktikum" (case-insensitive)
        if (stripos($mata_kuliah, 'praktikum') !== false) {
            return null;
        }

        // Jika salah satu dari yang wajib ada yang kosong, throw error / abaikan
        if (empty($namaDosenRaw) || empty($hari) || empty($waktu) || empty($mata_kuliah)) {
            throw new \Exception(
                "GATAL/ERROR: Data jadwal tidak lengkap (Terdapat Nama/Hari/Waktu/Matkul yang kosong)! " .
                "Pastikan format header kolom adalah: nama, hari, waktu, mata_kuliah, ruangan, tahun_akademik. " .
                "Header file Anda saat ini memuat: " . implode(', ', array_keys($row))
            );
        }

        // Parse waktu: ex '07:00 - 08:45' atau '07.00-08.45'
        $jam_mulai = '00:00';
        $jam_selesai = '00:00';
        if (strpos($waktu, '-') !== false) {
            $parts = explode('-', $waktu);
            $jam_mulai = trim($parts[0]);
            $jam_selesai = trim($parts[1]);
        } else {
            // Jika hanya 1 waktu tunggal
            $jam_mulai = trim($waktu);
            $jam_selesai = trim($waktu);
        }

        // Standardisasi format jam: ubah titik menjadi titik dua (22.00 -> 22:00) agar MySQL tidak error menjadi 00:00
        $jam_mulai = str_replace('.', ':', $jam_mulai);
        $jam_selesai = str_replace('.', ':', $jam_selesai);


        // Cari ID dosen berdasar nama
        $cleanRaw = $this->getCleanName($namaDosenRaw);
        $dosenId = null;

        if (isset($this->dosenMap[$cleanRaw])) {
            $dosenId = $this->dosenMap[$cleanRaw];
        } else {
            // Fuzzy match (80% similarity atau substring)
            foreach ($this->dosenMap as $dbClean => $id) {
                similar_text($cleanRaw, $dbClean, $perc);
                if ($perc > 80 || strpos($dbClean, $cleanRaw) !== false || strpos($cleanRaw, $dbClean) !== false) {
                    $dosenId = $id;
                    break;
                }
            }
        }

        if (!$dosenId) {
            // Jika dosen tidak ditemukan di database (misal dosen dari fakultas/jurusan lain), 
            // lewati baris ini agar tidak error dan tidak dimasukkan ke jadwal.
            return null;
        }

        if (empty($tahun_akademik)) {
            $tahun_akademik = '2025/2026';
        }

        // Cari jadwal yang sudah ada berdasarkan Dosen, Hari, Jam Mulai untuk di UPDATE atau CREATE
        $jadwal = JadwalMengajar::where('dosen_id', $dosenId)
            ->where('hari', $hari)
            ->where('jam_mulai', $jam_mulai)
            ->first();

        if ($jadwal) {
            // Edit Data
            $jadwal->jam_selesai = $jam_selesai;
            $jadwal->ruangan = $ruangan;
            $jadwal->mata_kuliah = $mata_kuliah;
            $jadwal->tahun_akademik = $tahun_akademik;
            $jadwal->save();

            $this->imported++;
            return null;
        }

        // Insert Baru
        $this->imported++;
        return new JadwalMengajar([
            'dosen_id' => $dosenId,
            'hari' => $hari,
            'jam_mulai' => $jam_mulai,
            'jam_selesai' => $jam_selesai,
            'ruangan' => $ruangan,
            'mata_kuliah' => $mata_kuliah,
            'tahun_akademik' => $tahun_akademik,
        ]);
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }
}
