<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Dosen;
use App\Models\Mahasiswa;

class MahasiswaSeeder extends Seeder
{
    private array $namaDepan = [
        'Andi', 'Budi', 'Citra', 'Dewi', 'Eko', 'Fitri', 'Gilang', 'Hana', 'Ivan', 'Julia',
        'Kurnia', 'Lestari', 'Muhammad', 'Nanda', 'Oscar', 'Putri', 'Rizki', 'Sari', 'Toni', 'Umar',
        'Vina', 'Wahyu', 'Xena', 'Yusuf', 'Zahra', 'Agus', 'Bagas', 'Candra', 'Dian', 'Erna',
        'Fajar', 'Gita', 'Hamdan', 'Indah', 'Joko', 'Kartini', 'Lukman', 'Maya', 'Nabil', 'Okta',
    ];

    private array $namaBelakang = [
        'Pratama', 'Santoso', 'Wijaya', 'Rahayu', 'Kurniawan', 'Setiawan', 'Susanto', 'Wibowo',
        'Hidayat', 'Nugroho', 'Purnama', 'Saputra', 'Yuliani', 'Laksono', 'Firmansyah', 'Hakim',
        'Ramadhan', 'Handoko', 'Permana', 'Kusuma', 'Siregar', 'Hasibuan', 'Lubis', 'Nainggolan',
        'Simbolon', 'Manurung', 'Purba', 'Sinaga', 'Situmorang', 'Tambunan',
    ];

    private array $judulPrefixes = [
        'Analisis dan Perancangan', 'Implementasi', 'Optimasi', 'Pengembangan Sistem',
        'Perancangan Model', 'Evaluasi Kinerja', 'Pemodelan dan Simulasi', 'Rancang Bangun',
        'Penerapan Metode', 'Kajian Teknis',
    ];

    private array $judulTopics = [
        'Manajemen Rantai Pasok', 'Sistem Produksi', 'Penjadwalan Mesin', 'Tata Letak Fasilitas',
        'Pengendalian Kualitas', 'Ergonomi Kerja', 'Keselamatan dan Kesehatan Kerja',
        'Manajemen Persediaan', 'Pengukuran Kerja', 'Analisis Keputusan Multikriteria',
        'Sistem Informasi Manufaktur', 'Lean Manufacturing', 'Six Sigma', 'Perawatan Mesin',
        'Manajemen Proyek', 'Kapasitas Produksi', 'Pengendalian Persediaan Spare Part',
        'Perencanaan Aggregate', 'Rekayasa Nilai', 'Manajemen Risiko Operasional',
    ];

    public function run(): void
    {
        Mahasiswa::query()->delete();

        $dosenIds = Dosen::pluck('id')->toArray();
        if (empty($dosenIds)) {
            return;
        }

        $dosenCount = count($dosenIds);
        $rumpunList = ['SPOI', 'PSIE', 'KPDP', 'RPTE'];
        $namaDepanCount = count($this->namaDepan);
        $namaBelakangCount = count($this->namaBelakang);
        $prefixCount = count($this->judulPrefixes);
        $topicCount = count($this->judulTopics);

        $mahasiswas = [];
        for ($i = 0; $i < 200; $i++) {
            $pembimbingId = $dosenIds[$i % $dosenCount];
            $nama = $this->namaDepan[$i % $namaDepanCount] . ' ' . $this->namaBelakang[$i % $namaBelakangCount];
            $judul = $this->judulPrefixes[$i % $prefixCount] . ' ' . $this->judulTopics[$i % $topicCount];

            $mahasiswas[] = [
                'nim'           => '210' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'nama'          => $nama,
                'judul_ta'      => $judul,
                'rumpun_ta'     => $rumpunList[$i % 4],
                'pembimbing_id' => $pembimbingId,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }

        // Insert in chunks of 50 for efficiency
        foreach (array_chunk($mahasiswas, 50) as $chunk) {
            Mahasiswa::insert($chunk);
        }
    }
}
