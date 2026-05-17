<?php

namespace App\Imports;

use App\Models\Dosen;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class DosenImport implements ToModel, WithHeadingRow
{

    private int $imported = 0;

    /**
     * Format kolom Excel yang diharapkan:
     * | nama | nip | rumpun_keahlian |
     *
     * rumpun_keahlian bisa diisi: SPOI / PSIE / KPDP / RPTE
     */
    public function model(array $row): ?Dosen
    {
        // Karena withHeadingRow di Laravel Excel akan mengubah spasi/kapital menjadi lowercase dan underscore,
        // misal 'Nama Lengkap' menjadi 'nama_lengkap'. Kita tangkap variasinya:
        $nama = trim($row['nama'] ?? $row['nama_dosen'] ?? $row['nama_lengkap'] ?? '');
        $nip = trim($row['nip'] ?? $row['n_i_p'] ?? $row['nomor_induk_pegawai'] ?? '');

        // Pengecekan apakah baris Excel ini baris sisa/kosong sepenuhnya
        $isEmptyRow = true;
        foreach ($row as $val) {
            if (!empty(trim((string) $val))) {
                $isEmptyRow = false;
                break;
            }
        }

        if ($isEmptyRow) {
            return null; // Abaikan baris kosong secara diam-diam
        }

        if (empty($nama)) {
            throw new \Exception(
                "GATAL/ERROR: Kolom nama wajib diisi, tetapi terbaca kosong pada suatu baris! " .
                "Atau, pastikan Anda menamai headernya 'Nama' atau 'Nama Lengkap'. Kolom yang terbaca: " . implode(', ', array_keys($row))
            );
        }

        // Nilai rumpun: bisa string tunggal atau koma-separated
        $rumpunRaw = trim($row['rumpun_keahlian'] ?? $row['rumpun'] ?? $row['kbk'] ?? $row['kelompok_bidang_keahlian'] ?? '');
        $rumpun = [];
        if (!empty($rumpunRaw)) {
            $rumpun = array_map('trim', explode(',', strtoupper($rumpunRaw)));
            // Hanya simpan nilai yang valid
            $valid = ['SPOI', 'PSIE', 'KPDP', 'RPTE'];
            $rumpun = array_values(array_filter($rumpun, fn($r) => in_array($r, $valid)));
        }

        // Cari berdasarkan NIP dulu, lalu nama
        $dosen = null;
        if (!empty($nip)) {
            $dosen = Dosen::where('nip', $nip)->first();
        }
        if (!$dosen && !empty($nama)) {
            $dosen = Dosen::where('nama', $nama)->first();
        }

        if ($dosen) {
            // Update data yang ada
            $dosen->nama = $nama ?: $dosen->nama;
            $dosen->nip = $nip ?: $dosen->nip;
            if (!empty($rumpun)) {
                $dosen->rumpun_keahlian = $rumpun;
            }
            $dosen->save();
            $this->imported++;
            return null; // sudah disimpan manual, kembalikan null agar tidak disimpan dua kali
        }

        // Insert baru
        $this->imported++;
        return new Dosen([
            'nama' => $nama,
            'nip' => $nip ?: null,
            'rumpun_keahlian' => !empty($rumpun) ? $rumpun : null,
        ]);
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }
}
