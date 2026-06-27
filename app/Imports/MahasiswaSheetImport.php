<?php

namespace App\Imports;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithSkipDuplicates;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\Importable;

class MahasiswaSheetImport implements ToCollection, WithHeadingRow
{
    use Importable;

    /** Statistik hasil import */
    public int $imported   = 0;
    public int $skipped    = 0;
    public int $updated    = 0;
    public array $notFound = [];   // nama pembimbing yg tidak ditemukan di DB
    public array $errors   = [];   // baris yang gagal

    /**
     * Cache dosen agar tidak query berulang per baris.
     * key: nama_lower  → Dosen model
     */
    private ?Collection $dosenCache = null;

    private function getDosenCache(): Collection
    {
        if ($this->dosenCache === null) {
            $this->dosenCache = Dosen::all();
        }
        return $this->dosenCache;
    }

    /**
     * Cari dosen berdasarkan nama (case-insensitive, strip gelar minor).
     * Strategi:
     *  1. Exact match (case-insensitive)
     *  2. DB nama contains string yg diupload
     *  3. String yg diupload contains nama DB
     */
    private function findDosen(string $namaPembimbing): ?Dosen
    {
        $needle = strtolower(trim($namaPembimbing));
        $dosens = $this->getDosenCache();

        // Pass 1 — exact match
        foreach ($dosens as $d) {
            if (strtolower(trim($d->nama)) === $needle) {
                return $d;
            }
        }

        // Pass 2 — DB nama contained in input string
        foreach ($dosens as $d) {
            $dbNama = strtolower(trim($d->nama));
            if (str_contains($needle, $dbNama)) {
                return $d;
            }
        }

        // Pass 3 — input string contained in DB nama
        foreach ($dosens as $d) {
            $dbNama = strtolower(trim($d->nama));
            if (str_contains($dbNama, $needle)) {
                return $d;
            }
        }

        // Pass 4 — similar_text fallback (>= 70% similarity)
        $bestScore  = 0;
        $bestDosen  = null;
        foreach ($dosens as $d) {
            similar_text($needle, strtolower(trim($d->nama)), $percent);
            if ($percent > $bestScore) {
                $bestScore = $percent;
                $bestDosen = $d;
            }
        }
        if ($bestScore >= 70) {
            return $bestDosen;
        }

        return null;
    }

    /**
     * Normalise heading row key dari Google Form export.
     * maatwebsite/excel lowercase + snake_case heading row secara otomatis,
     * tapi kita tetap normalise manual untuk keamanan.
     */
    private function getField(array $row, array $keys): ?string
    {
        foreach ($keys as $key) {
            // maatwebsite converts heading to snake_case lowercase
            $normalized = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $key));
            $normalized = trim($normalized, '_');

            // cek key asli dan key yang sudah dinormalkan
            foreach ($row as $k => $v) {
                $rowKey = strtolower(preg_replace('/[^a-z0-9]+/i', '_', (string)$k));
                $rowKey = trim($rowKey, '_');
                if ($rowKey === $normalized || $k === $key) {
                    return $v !== null ? trim((string)$v) : null;
                }
            }
        }
        return null;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $rowIndex => $row) {
            $rowArr = $row->toArray();

            // ── 1. Ambil kolom dari Google Form export ──────────────────────
            // Kolom Google Form (exact header):
            //   "Nama Mahasiswa (Huruf Balok)"
            //   "Nomor Mahasiswa (NPM)"
            //   "Judul TA-1 (Huruf Balok)"
            //   "Nama Pembimbing TA (lengkap dengan gelar)"

            $nama = $this->getField($rowArr, [
                'Nama Lengkap (HURUF BALOK)',
                'nama_lengkap_huruf_balok',
                'nama lengkap (huruf balok)',
                'Nama Mahasiswa (Huruf Balok)',
                'nama_mahasiswa_huruf_balok',
                'nama mahasiswa (huruf balok)',
                'nama',
            ]);

            $nim = $this->getField($rowArr, [
                'Nomor Mahasiswa (NPM)',
                'nomor_mahasiswa_npm',
                'nomor mahasiswa (npm)',
                'npm',
                'nim',
            ]);

            $judulTa = $this->getField($rowArr, [
                'Judul',
                'judul',
                'Judul TA-1 (Huruf Balok)',
                'judul_ta_1_huruf_balok',
                'judul ta-1 (huruf balok)',
                'judul_ta',
                'judul ta',
            ]);

            $namaPembimbing = $this->getField($rowArr, [
                'DOSEN PEMBIMBING',
                'dosen_pembimbing',
                'dosen pembimbing',
                'Nama Pembimbing TA (lengkap dengan gelar)',
                'nama_pembimbing_ta_lengkap_dengan_gelar',
                'nama pembimbing ta (lengkap dengan gelar)',
                'pembimbing',
                'nama pembimbing',
            ]);

            $noHp = $this->getField($rowArr, [
                'No. HP / WA',
                'no_hp_wa',
                'no hp wa',
                'no hp',
                'no_hp',
                'hp',
                'nomor hp',
                'nomor_hp',
                'telepon',
                'whatsapp',
                'no whatsapp',
                'no_whatsapp',
                'nomor whatsapp',
                'nomor_whatsapp',
            ]);

            // Skip baris kosong
            if (empty($nama) && empty($nim)) {
                continue;
            }

            // ── 2. Cari dosen pembimbing ────────────────────────────────────
            $dosen      = null;
            $pembimbingId = null;
            $rumpunTa   = '';

            if (!empty($namaPembimbing)) {
                $dosen = $this->findDosen($namaPembimbing);

                if ($dosen) {
                    $pembimbingId = $dosen->id;
                    $rumpunTa = is_array($dosen->rumpun_keahlian)
                        ? implode(', ', $dosen->rumpun_keahlian)
                        : ($dosen->rumpun_keahlian ?? '');
                } else {
                    // Catat sebagai tidak ditemukan
                    if (!in_array($namaPembimbing, $this->notFound)) {
                        $this->notFound[] = $namaPembimbing;
                    }
                }
            }

            // ── 3. Upsert ke database ───────────────────────────────────────
            try {
                $existing = Mahasiswa::where('nim', $nim)->first();

                $data = [
                    'nama'          => strtoupper($nama ?? ''),
                    'judul_ta'      => strtoupper($judulTa ?? ''),
                    'no_hp'         => $noHp ?? null,
                    'pembimbing_id' => $pembimbingId,
                    'rumpun_ta'     => $rumpunTa,
                ];

                if ($existing) {
                    $existing->update($data);
                    $this->updated++;
                } else {
                    Mahasiswa::create(array_merge($data, ['nim' => $nim ?? '']));
                    $this->imported++;
                }
            } catch (\Throwable $e) {
                $this->errors[] = "Baris " . ($rowIndex + 2) . " (NIM: $nim): " . $e->getMessage();
                $this->skipped++;
            }
        }
    }
}
