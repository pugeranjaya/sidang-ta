<?php

namespace App\Services;

use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\Sidang;
use App\Models\JadwalMengajar;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SidangScheduler
{
    /** @var array Log debug messages */
    public array $debugLog = [];
    /**
     * C1 (Ketersediaan Waktu)
     * C2 (Relevansi Keilmuan)
     * C3 (Pemerataan Beban)
     * C4 (Jumlah Menguji)
     */
    private const WEIGHTS = [
        'C1' => 0.491,
        'C2' => 0.263,
        'C3' => 0.161,
        'C4' => 0.085,
    ];

    /**
     * Jadwal resmi pelaksanaan seminar berdasarkan Pengumuman No. 181/UN62.12.2/EP/2025
     * Format: bulan (1-12) => tanggal minimal mulai
     * Sidang hanya boleh dijadwalkan pada tanggal tersebut atau setelahnya dalam bulan yang sama.
     */
    private const JADWAL_RESMI = [
        'Seminar TA 1' => [
            1 => '2026-01-15',
            2 => '2026-02-13',
            3 => '2026-03-13',
            4 => '2026-04-10',
            5 => '2026-05-13',
            6 => '2026-06-12',
            7 => '2026-07-10',
            8 => '2026-08-14',
            9 => '2026-09-11',
            10 => '2026-10-09',
            11 => '2026-11-13',
        ],
        'Sidang TA 2' => [
            1 => '2026-01-09',
            2 => '2026-02-06',
            3 => '2026-03-06',
            4 => '2026-04-02',
            5 => '2026-05-08',
            6 => '2026-06-05',
            7 => '2026-07-03',
            8 => '2026-08-07',
            9 => '2026-09-04',
            10 => '2026-10-02',
            11 => '2026-11-06',
        ],
    ];

    /**
     * Daftar ruangan sidang berdasarkan urutan prioritas penggunaan.
     * Ruang pertama paling sering dipakai, terakhir paling jarang (opsi cadangan).
     */
    private const RUANGAN = [
        'Ruang Sidang Jurusan Teknik Industri',       // Prioritas 1 (utama)
        'Ruang TUK Jurusan Teknik Industri',           // Prioritas 2
        'Ruang Seminar LT-2 Jurusan Teknik Industri', // Prioritas 3 (cadangan)
    ];

    /**
     * Mapping topik/rumpun TA ke daftar mata kuliah yang relevan.
     * Digunakan untuk menilai relevansi dosen berdasarkan riwayat mengajar.
     */
    private const RELEVANSI_MATA_KULIAH = [
        'SPOI' => [
            // Core SPOI
            'Kalkulus 1',
            'Kalkulus 2',
            'Kalkulus 3',
            'Teori Probabilitas',
            'Statistika 1',
            'Statistika 2',
            'Penelitian Operasional 1',
            'Penelitian Operasional 2',
            'Analitika data',
            'Perencanaan dan Pengendalian Produksi',
            'Praktikum Perencanaan dan Pengendalian Produksi',
            'Pemodelan Sistem',
            'Sistem Produksi',
            'Simulasi Sistem Industri',
            'Praktikum Simulasi Sistem Industri',
            'Sistem Cerdas untuk Industri',
            'Pengambilan Keputusan Multi Kriteria',
            'Otomasi Proses Produksi',
            'Flexible Manufacturing System',
            'Enterprise Resource Planning',
            'Sustainable Manufacturing',
            'Life Cycle Assessment',
            'Sistem Pemeliharaan',
            'Perencanaan Fasilitas',
            'Penjadwalan Produksi',
            'Sistem Manufaktur Terotomasi',
            'Sistem Produksi Mass Customization',
        ],
        'PSIE' => [
            // Core PSIE
            'Biologi: Anatomi dan Fisiologi',
            'Pengukuran dan Perancangan Sistem Kerja',
            'Ergonomika',
            'Kesehatan dan Keselamatan Kerja',
            'Praktikum Perancangan Sistem Kerja dan Ergonomi',
            'Perancangan Tata Letak Fasilitas',
            'Praktikum Perancangan Tata Letak Fasilitas',
            'Organisasi dan Manajemen Perusahaan Industri',
            'Pengantar Teknik Industri',
            'Ergonomi Industri',
            'Biomekanika',
            'Perencanaan Lingkungan Kerja',
            'Sistem Manajemen Lingkungan',
            'Manajemen Resiko',
            'Manajemen Kinerja',
            'E-Commerce',
            'Manajemen Sumber Daya Manusia',
            'Ergonomi dalam Perancangan Produk',
            'Usabilitas',
        ],
        'KPDP' => [
            // Core KPDP
            'Pengendalian dan Penjaminan Mutu',
            'Perancangan dan Pengembangan Produk',
            'Analisis dan Perancangan Sistem Informasi',
            'Praktikum Analisis Perancangan Sistem Informasi',
            'Pemrograman Komputer',
            'Menggambar Teknik',
            'Praktikum Menggambar Teknik',
            'Perancangan untuk Manufaktur dan Rakitan',
            'Sistem Manufaktur Aditif',
            'Rekayasa Kualitas',
            'Desain Eksperimen',
            'Six Sigma',
            'Manajemen Mutu Terpadu',
            'Analisis Multivariat',
            'Manajemen Inovasi',
            'Service Quality',
        ],
        'RPTE' => [
            // Core RPTE
            'Kimia Dasar',
            'Fisika Dasar 1',
            'Fisika Dasar 2',
            'Praktikum Fisika Dasar',
            'Material Teknik',
            'Mekanika Teknik',
            'Elemen Mesin',
            'Proses Manufaktur',
            'Praktikum Proses Manufaktur',
            'Analisis dan Pengendalian Biaya',
            'Ekonomi Teknik',
            'Sistem Rantai Pasok',
            'Capstone Design',
            'Technopreneurship',
            'Sistem Industri Berkelanjutan',
            'Rantai Pasok Berkelanjutan',
            'Optimasi Rantai Pasok',
            'Sistem Pengadaan dan Persediaan',
            'Manajemen Hubungan Pemasok dan Pelanggan',
            'Sistem Logistik',
            'Sirkular Ekonomi Rantai Pasok',
            'Analisis Kelayakan Usaha',
            'Teknik Operasional Proyek',
            'Manajemen Proyek Lanjut',
            'Manajemen Teknologi',
            'Perancangan Produk Lanjut',
        ],
    ];

    private array $dosenCounts = [];
    private int $minCount = 0;
    private int $maxCount = 0;
    private int $dynamicTolerance = 0;

    public function generateForMahasiswa(int $mahasiswaId, string $tanggalMulai, string $tanggalSelesai, string $jenisSidang = 'Seminar TA 1'): array
    {
        $this->debugLog = [];
        $mahasiswa = Mahasiswa::findOrFail($mahasiswaId);

        $this->log("=== Memulai Penjadwalan Sidang {$jenisSidang} (Metode: Rule-Based + AHP + Heuristic) ===");
        $this->log("Mahasiswa: {$mahasiswa->nama}, Rumpun TA: {$mahasiswa->rumpun_ta}");

        // Hitung beban seluruh dosen di awal untuk optimalisasi C3
        $this->calculateDosenWorkloads();

        // 1. GENERATE POSSIBLE SLOTS (Heuristic Basis)
        $possibleSlots = $this->generatePossibleSlots($tanggalMulai, $tanggalSelesai, $jenisSidang);
        if (empty($possibleSlots)) {
            $this->log("GAGAL: Tidak ada slot waktu tersedia di rentang tanggal tersebut.");
            return ['success' => false, '_debug' => $this->debugLog];
        }

        // ========== LOGIKA SEMINAR TA 2 ==========
        if ($jenisSidang === 'Sidang TA 2') {
            $historyTa1 = Sidang::where('mahasiswa_id', $mahasiswaId)
                ->where('jenis_sidang', 'Seminar TA 1')
                ->where('status', 'lulus')
                ->latest()
                ->first();

            if (!$historyTa1) {
                $this->log("GAGAL: Mahasiswa ini belum menyelesaikan Seminar TA 1 (belum ada jadwal dengan status Lulus). Tidak diizinkan Seminar TA 2.");
                return ['success' => false, '_debug' => $this->debugLog];
            }

            // Untuk TA 2, 4 Penguji diambil dari orang yang sama di TA 1
            $pembimbing = Dosen::find($mahasiswa->pembimbing_id);
            $ketuaSidang = Dosen::find($historyTa1->ketua_sidang_id);
            $penguji1TA1 = Dosen::find($historyTa1->penguji1_id);
            $penguji2TA1 = Dosen::find($historyTa1->penguji2_id);

            if (!$pembimbing || !$ketuaSidang || !$penguji1TA1 || !$penguji2TA1) {
                $this->log("GAGAL: Data dosen pembimbing atau penguji dari TA 1 tidak lengkap.");
                return ['success' => false, '_debug' => $this->debugLog];
            }

            $this->log("[TA 2] Formasi Penguji Tetap:");
            $this->log("  - Pembimbing / P1: {$pembimbing->nama}");
            $this->log("  - Ketua Sidang / P2: {$ketuaSidang->nama}");
            $this->log("  - Penguji 3 (dari P1 TA1): {$penguji1TA1->nama}");
            $this->log("  - Penguji 4 (dari P2 TA1): {$penguji2TA1->nama}");

            $bentrokInfo = '';
            $bestSchedules = $this->findSlotForFixedPengujiTA2(
                $possibleSlots,
                $pembimbing,
                $ketuaSidang,
                $penguji1TA1,
                $penguji2TA1,
                $bentrokInfo
            );

            if (empty($bestSchedules)) {
                $msg = "Tidak ditemukan jadwal di mana keempat dosen tersedia bersamaan.";
                if ($bentrokInfo) {
                    $msg .= " " . $bentrokInfo;
                }
                $this->log("GAGAL: " . $msg);
                return ['success' => false, '_debug' => $this->debugLog];
            }

            $options = [];
            foreach ($bestSchedules as $schedule) {
                $options[] = array_merge($schedule, [
                    'mahasiswa_id' => $mahasiswaId,
                    'jenis_sidang' => $jenisSidang,
                    'status' => 'belum lulus',
                    'ruangan' => $this->pilihRuangan($schedule['tanggal'], $schedule['jam_mulai'], $schedule['jam_selesai']),
                    'score_ahp' => 'N/A',
                ]);
            }

            return ['success' => true, 'options' => $options, '_debug' => $this->debugLog];
        }

        // ========== LOGIKA SEMINAR TA 1 ==========
        // TA 1: 2 penguji + 1 ketua sidang 

        // 2. RULE-BASED FILTERING (Dilonggarkan agar semua dosen dapat tugas)
        $candidates = $this->getFilteredDosenCandidates($mahasiswa);
        if ($candidates->count() < 3) {
            $this->log("GAGAL: Tidak cukup dosen yang memenuhi aturan dasar (minimal 3).");
            return ['success' => false, '_debug' => $this->debugLog];
        }

        // 3. AHP SCORING & HEURISTIC SCHEDULING
        $bestSchedules = $this->findBestSlotAndPenguji($possibleSlots, $candidates, $mahasiswa);

        if (empty($bestSchedules)) {
            $this->log("GAGAL: Tidak ditemukan kombinasi slot dan penguji yang valid.");
            return ['success' => false, '_debug' => $this->debugLog];
        }

        $options = [];
        foreach ($bestSchedules as $schedule) {
            $options[] = array_merge($schedule, [
                'mahasiswa_id' => $mahasiswaId,
                'jenis_sidang' => $jenisSidang,
                'status' => 'belum lulus',
                'ruangan' => $this->pilihRuangan($schedule['tanggal'], $schedule['jam_mulai'], $schedule['jam_selesai']),
                'score_ahp' => number_format($schedule['total_score'], 3),
            ]);
        }

        return ['success' => true, 'options' => $options, '_debug' => $this->debugLog];
    }

    private function calculateDosenWorkloads(): void
    {
        if (empty($this->dosenCounts)) {
            $dosens = Dosen::where('status', true)->get();
            $this->dosenCounts = [];

            foreach ($dosens as $d) {
                $count = Sidang::where(function ($q) use ($d) {
                    $q->where('penguji1_id', $d->id)
                        ->orWhere('penguji2_id', $d->id)
                        ->orWhere('penguji3_id', $d->id)
                        ->orWhere('penguji4_id', $d->id)
                        ->orWhere('ketua_sidang_id', $d->id);
                })->count();
                $this->dosenCounts[$d->id] = $count;
            }
        }

        $this->minCount = min($this->dosenCounts) ?: 0;
        $this->maxCount = max($this->dosenCounts) ?: 0;

        if ($this->dynamicTolerance === 0) {
            $totalMahasiswa = Mahasiswa::count();
            // Ambang batas dinamis 5% dari jumlah mahasiswa (min batas celah kemacetan = 3)
            $this->dynamicTolerance = max(3, (int) ceil($totalMahasiswa * 0.05));
        }
    }

    /**
     * REVISED Rule-Based Filter:
     * - Wajib: bukan pembimbing (dan bukan excluded dosen lain)
     * - Relevan: Filter Rumpun dihapus agar semua dosen memiliki kesempatan merata.
     *   Relevansi murni diatur lewat penilaian pembobotan AHP (C2).
     */
    private function getFilteredDosenCandidates(Mahasiswa $mahasiswa, array $excludeIds = []): Collection
    {
        $allExcludeIds = array_unique(array_merge([$mahasiswa->pembimbing_id], $excludeIds));

        // Toleransi Beban Max vs Min: Dinamis (5% dari total mahasiswa pendaftar)
        // Menggunakan property dari memory cache kalkulasi dynamicTolerance
        $toleransiSelisih = $this->dynamicTolerance;
        $maxAllowedMisi = $this->minCount + $toleransiSelisih;

        $allCandidates = Dosen::where('status', true)->whereNotIn('id', $allExcludeIds)->get();

        // Ambil dosen yang bebannya dalam batas toleransi
        $candidates = $allCandidates->filter(function ($dosen) use ($maxAllowedMisi) {
            $count = $this->dosenCounts[$dosen->id] ?? 0;
            return $count <= $maxAllowedMisi;
        })->values();

        // Jika filter terlalu ketat sehingga kandidat tidak cukup, maka relakan (ambil semua)
        if ($candidates->count() < 3) {
            $this->log("[Rule-Based] Kandidat ketat = {$candidates->count()}, kurang dari 3. Mengabaikan filter batas toleransi.");
            $candidates = $allCandidates;
        }

        $this->log("[Rule-Based] Kandidat dimasukkan ke AHP: {$candidates->count()} dosen (Berdasarkan toleransi pemerataan beban).");

        return $candidates;
    }

    /**
     * Cek apakah seorang dosen memiliki rumpun keahlian yang sesuai dengan rumpun TA mahasiswa.
     * Mendukung dosen dengan lebih dari 1 rumpun keahlian (disimpan sebagai JSON array).
     */
    private function dosenMatchRumpun(Dosen $dosen, string $rumpunTA): bool
    {
        if (empty($dosen->rumpun_keahlian)) {
            return false;
        }

        $rumpunList = is_array($dosen->rumpun_keahlian)
            ? $dosen->rumpun_keahlian
            : (json_decode((string) $dosen->rumpun_keahlian, true) ?? []);

        return in_array($rumpunTA, $rumpunList, true);
    }

    /**
     * TA 1: Cari slot terbaik dengan 2 penguji + 1 ketua sidang.
     * Aturan: Penguji 1 WAJIB sesuai rumpun TA mahasiswa.
     *         Penguji 2 dan Ketua Sidang boleh siapapun (merata).
     */
    private function findBestSlotAndPenguji(array $slots, Collection $candidates, Mahasiswa $mahasiswa): array
    {
        $scheduleOptions = [];
        $rumpunTA = $mahasiswa->rumpun_ta;

        foreach ($slots as $slot) {
            $availableInSlot = $candidates->filter(function ($dosen) use ($slot) {
                return !$this->isBentrokMengajar($dosen, $slot) && !$this->isBentrokSidang($dosen, $slot);
            });

            // Pool Penguji 1: wajib sesuai rumpun TA
            $poolP1 = $availableInSlot->filter(function ($dosen) use ($rumpunTA) {
                return $this->dosenMatchRumpun($dosen, $rumpunTA);
            })->values();

            // Pool Penguji 2 & Ketua: siapapun yang tersedia (termasuk yang sesuai rumpun)
            $poolUmum = $availableInSlot->values();

            // Butuh minimal 1 dosen sesuai rumpun (P1) + 2 dosen lain (P2 & Ketua)
            if ($poolP1->isEmpty() || $poolUmum->count() < 3) {
                continue;
            }

            // Scoring semua kandidat
            $scored = $availableInSlot->shuffle()->map(function ($dosen) use ($mahasiswa) {
                $score = $this->calculateAHPScore($dosen, $mahasiswa);
                return ['dosen' => $dosen, 'score' => $score];
            })->sortByDesc('score')->values();

            $scoredById = $scored->keyBy(fn($item) => $item['dosen']->id);

            // Pilih P1: dosen rumpun cocok dengan skor AHP tertinggi
            $scoredP1 = $poolP1->map(fn($d) => $scoredById[$d->id])->sortByDesc('score')->values();
            $p1 = $scoredP1->first();

            // Pilih P2 & Ketua dari sisa dosen (exclude P1)
            $scoredUmum = $scored->filter(fn($item) => $item['dosen']->id !== $p1['dosen']->id)->values();
            if ($scoredUmum->count() < 2) {
                continue;
            }
            $p2 = $scoredUmum[0];
            $k1 = $scoredUmum[1];

            $totalSlotScore = $p1['score'] + $p2['score'] + $k1['score'];

            $scheduleOptions[] = [
                'slot' => $slot,
                'penguji1' => $p1['dosen'],
                'penguji2' => $p2['dosen'],
                'ketua_sidang' => $k1['dosen'],
                'total_score' => $totalSlotScore,
            ];
        }

        if (empty($scheduleOptions))
            return [];

        $sorted = collect($scheduleOptions)->sortByDesc('total_score')->take(10)->values();

        $results = [];
        foreach ($sorted as $winner) {
            $results[] = [
                'penguji1_id' => $winner['penguji1']->id,
                'penguji1_nama' => $winner['penguji1']->nama,
                'penguji2_id' => $winner['penguji2']->id,
                'penguji2_nama' => $winner['penguji2']->nama,
                'ketua_sidang_id' => $winner['ketua_sidang']->id,
                'ketua_sidang_nama' => $winner['ketua_sidang']->nama,
                'tanggal' => $winner['slot']['tanggal'],
                'jam_mulai' => $winner['slot']['jam_mulai'],
                'jam_selesai' => $winner['slot']['jam_selesai'],
                'total_score' => $winner['total_score'],
            ];
        }

        return $results;
    }

    /**
     * Batch scheduling: jadwalkan hingga $maxPerRuangan mahasiswa per ruangan,
     * untuk 3 ruangan sekaligus (total maks 15 mahasiswa dalam 1x generate).
     *
     * @param  array  $mahasiswaIds  Array of mahasiswa IDs (maks 15 total)
     * @param  string $tanggalMulai
     * @param  string $tanggalSelesai
     * @param  string $jenisSidang
     * @param  int    $maxPerRuangan Maks mahasiswa per ruangan per sesi (default 5)
     * @return array  ['results' => [...], '_debug' => [...]]
     */
    public function generateBatch(
        array $mahasiswaIds,
        string $tanggalMulai,
        string $tanggalSelesai,
        string $jenisSidang = 'Seminar TA 1',
        int $maxPerRuangan = 5
    ): array {
        $this->debugLog = [];
        $this->calculateDosenWorkloads();

        $maxTotal = count(self::RUANGAN) * $maxPerRuangan;
        if (count($mahasiswaIds) > $maxTotal) {
            return [
                'success' => false,
                'error' => "Maksimal {$maxTotal} mahasiswa per batch ({$maxPerRuangan} per ruangan × " . count(self::RUANGAN) . " ruangan).",
                '_debug' => $this->debugLog,
            ];
        }

        $this->log("=== Batch Scheduling {$jenisSidang}: " . count($mahasiswaIds) . " mahasiswa ===");

        $possibleSlots = $this->generatePossibleSlots($tanggalMulai, $tanggalSelesai, $jenisSidang);
        if (empty($possibleSlots)) {
            return ['success' => false, 'error' => 'Tidak ada slot waktu tersedia.', '_debug' => $this->debugLog];
        }

        // Muat data semua mahasiswa
        $allMahasiswa = Mahasiswa::findMany($mahasiswaIds)->keyBy('id');
        $results = []; // hasil akhir per mahasiswa
        $usedDosenPerSlot = []; // track dosen yang dipakai per slot agar tidak dobel lintas ruangan
        $usedRuanganPerSlot = []; // track ruangan yang dipakai per slot di memori

        foreach ($mahasiswaIds as $mhsId) {
            $mahasiswa = $allMahasiswa[$mhsId] ?? null;
            if (!$mahasiswa) {
                $results[$mhsId] = ['success' => false, 'error' => 'Mahasiswa tidak ditemukan.'];
                continue;
            }

            if ($jenisSidang === 'Seminar TA 1') {
                $candidates = $this->getFilteredDosenCandidates($mahasiswa);
                $found = false;

                foreach ($possibleSlots as $slotKey => $slot) {
                    // Tandai dosen yang sudah dipakai di slot ini (batch-aware)
                    $usedInThisSlot = $usedDosenPerSlot[$slotKey] ?? [];

                    $availableInSlot = $candidates->filter(function ($dosen) use ($slot, $usedInThisSlot) {
                        return !in_array($dosen->id, $usedInThisSlot)
                            && !$this->isBentrokMengajar($dosen, $slot)
                            && !$this->isBentrokSidang($dosen, $slot);
                    });

                    $rumpunTA = $mahasiswa->rumpun_ta;
                    $poolP1 = $availableInSlot->filter(fn($d) => $this->dosenMatchRumpun($d, $rumpunTA))->values();
                    $poolUmum = $availableInSlot->values();

                    if ($poolP1->isEmpty() || $poolUmum->count() < 3) {
                        continue;
                    }

                    $scored = $availableInSlot->shuffle()->map(function ($dosen) use ($mahasiswa) {
                        return ['dosen' => $dosen, 'score' => $this->calculateAHPScore($dosen, $mahasiswa)];
                    })->sortByDesc('score')->values();

                    $scoredById = $scored->keyBy(fn($item) => $item['dosen']->id);
                    $p1 = $poolP1->map(fn($d) => $scoredById[$d->id])->sortByDesc('score')->first();
                    $scoredUmum = $scored->filter(fn($item) => $item['dosen']->id !== $p1['dosen']->id)->values();

                    if ($scoredUmum->count() < 2)
                        continue;

                    $p2 = $scoredUmum[0];
                    $k1 = $scoredUmum[1];

                    // Cari ruangan kosong di slot ini
                    $usedRuanganInThisSlot = $usedRuanganPerSlot[$slotKey] ?? [];
                    $ruangan = $this->pilihRuanganBatch($slot['tanggal'], $slot['jam_mulai'], $slot['jam_selesai'], $usedRuanganInThisSlot);
                    if (!$ruangan)
                        continue; // semua ruangan penuh di slot ini

                    // Catat dosen dan ruangan yang dipakai di slot ini
                    $usedDosenPerSlot[$slotKey][] = $p1['dosen']->id;
                    $usedDosenPerSlot[$slotKey][] = $p2['dosen']->id;
                    $usedDosenPerSlot[$slotKey][] = $k1['dosen']->id;
                    $usedRuanganPerSlot[$slotKey][] = $ruangan;

                    $results[$mhsId] = [
                        'success' => true,
                        'mahasiswa_id' => $mhsId,
                        'mahasiswa_nama' => $mahasiswa->nama,
                        'jenis_sidang' => $jenisSidang,
                        'tanggal' => $slot['tanggal'],
                        'jam_mulai' => $slot['jam_mulai'],
                        'jam_selesai' => $slot['jam_selesai'],
                        'ruangan' => $ruangan,
                        'penguji1_id' => $p1['dosen']->id,
                        'penguji1_nama' => $p1['dosen']->nama,
                        'penguji2_id' => $p2['dosen']->id,
                        'penguji2_nama' => $p2['dosen']->nama,
                        'ketua_sidang_id' => $k1['dosen']->id,
                        'ketua_sidang_nama' => $k1['dosen']->nama,
                        'status' => 'belum lulus',
                        'score_ahp' => number_format($p1['score'] + $p2['score'] + $k1['score'], 3),
                    ];
                    $found = true;
                    break;
                }

                if (!$found) {
                    $results[$mhsId] = [
                        'success' => false,
                        'mahasiswa_id' => $mhsId,
                        'mahasiswa_nama' => $mahasiswa->nama,
                        'error' => 'Tidak ada slot dan dosen yang tersedia untuk mahasiswa ini.',
                    ];
                }
            } else {
                // Untuk TA 2, fallback ke single generate (formasi penguji sudah tetap)
                $single = $this->generateForMahasiswa($mhsId, $tanggalMulai, $tanggalSelesai, $jenisSidang);
                $results[$mhsId] = $single;
            }
        }

        return ['success' => true, 'results' => $results, '_debug' => $this->debugLog];
    }

    /**
     * Pilih ruangan kosong di slot tertentu, memperhitungkan dosen batch yang sedang dipakai.
     * Ruangan dikunci per-slot jika sudah ada 1 sidang (1 sidang = 1 sesi = 1 ruangan).
     */
    private function pilihRuanganBatch(string $tanggal, string $jamMulai, string $jamSelesai, array $usedRuanganInThisSlot = []): ?string
    {
        foreach (self::RUANGAN as $ruangan) {
            // Cek apakah di memori (batch saat ini) ruangan ini sudah diklaim
            if (in_array($ruangan, $usedRuanganInThisSlot)) {
                continue;
            }
            $terpakai = Sidang::where('ruangan', $ruangan)
                ->where('tanggal', $tanggal)
                ->where(function ($q) use ($jamMulai, $jamSelesai) {
                    $q->whereTime('jam_mulai', '<', $jamSelesai)
                        ->whereTime('jam_selesai', '>', $jamMulai);
                })->exists();

            if (!$terpakai) {
                return $ruangan;
            }
        }
        return null; // semua ruangan penuh
    }

    /**
     * TA 2: Cari slot di mana 4 dosen spesifik semuanya tersedia
     */
    private function findSlotForFixedPengujiTA2(
        array $slots,
        Dosen $pembimbing,
        Dosen $ketuaSidang,
        Dosen $penguji1TA1,
        Dosen $penguji2TA1,
        ?string &$bentrokInfo = null
    ): array {
        $unavailableReasons = [];
        $validSlots = [];

        foreach ($slots as $slot) {
            $isPembimbingBentrok = $this->isBentrokMengajar($pembimbing, $slot) || $this->isBentrokSidang($pembimbing, $slot);
            $isKetuaBentrok = $this->isBentrokMengajar($ketuaSidang, $slot) || $this->isBentrokSidang($ketuaSidang, $slot);
            $isPenguji1Bentrok = $this->isBentrokMengajar($penguji1TA1, $slot) || $this->isBentrokSidang($penguji1TA1, $slot);
            $isPenguji2Bentrok = $this->isBentrokMengajar($penguji2TA1, $slot) || $this->isBentrokSidang($penguji2TA1, $slot);

            if (!$isPembimbingBentrok && !$isKetuaBentrok && !$isPenguji1Bentrok && !$isPenguji2Bentrok) {
                $validSlots[] = [
                    'penguji1_id' => $pembimbing->id,
                    'penguji1_nama' => $pembimbing->nama,
                    'penguji2_id' => $ketuaSidang->id,
                    'penguji2_nama' => $ketuaSidang->nama,
                    'penguji3_id' => $penguji1TA1->id,
                    'penguji3_nama' => $penguji1TA1->nama,
                    'penguji4_id' => $penguji2TA1->id,
                    'penguji4_nama' => $penguji2TA1->nama,
                    'ketua_sidang_id' => $ketuaSidang->id,
                    'ketua_sidang_nama' => $ketuaSidang->nama,
                    'tanggal' => $slot['tanggal'],
                    'jam_mulai' => $slot['jam_mulai'],
                    'jam_selesai' => $slot['jam_selesai'],
                ];

                if (count($validSlots) >= 10)
                    break;
            } else {
                if ($isPembimbingBentrok)
                    $unavailableReasons[$pembimbing->nama] = ($unavailableReasons[$pembimbing->nama] ?? 0) + 1;
                if ($isKetuaBentrok)
                    $unavailableReasons[$ketuaSidang->nama] = ($unavailableReasons[$ketuaSidang->nama] ?? 0) + 1;
                if ($isPenguji1Bentrok)
                    $unavailableReasons[$penguji1TA1->nama] = ($unavailableReasons[$penguji1TA1->nama] ?? 0) + 1;
                if ($isPenguji2Bentrok)
                    $unavailableReasons[$penguji2TA1->nama] = ($unavailableReasons[$penguji2TA1->nama] ?? 0) + 1;
            }
        }

        if (empty($validSlots) && !empty($unavailableReasons)) {
            $details = [];
            $totalSlots = count($slots);
            foreach ($unavailableReasons as $nama => $count) {
                if ($count >= $totalSlots) {
                    $details[] = "$nama (Bentrok semua slot)";
                } else {
                    $details[] = "$nama (Bentrok $count slot)";
                }
            }
            $bentrokInfo = implode(", ", $details);
        }

        return $validSlots;
    }

    /**
     * Generate multiple schedule OPTIONS for a student (preview only, nothing is saved).
     * Returns up to $numOptions slot candidates so the user can pick one.
     */
    public function generateOptionsForMahasiswa(
        int $mahasiswaId,
        string $tanggalMulai,
        string $tanggalSelesai,
        string $jenisSidang = 'Sidang TA 2',
        int $numOptions = 3
    ): array {
        $this->debugLog = [];
        $mahasiswa = Mahasiswa::findOrFail($mahasiswaId);
        $this->log("=== Generate Opsi {$jenisSidang} untuk {$mahasiswa->nama} ===");
        $this->calculateDosenWorkloads();

        $possibleSlots = $this->generatePossibleSlots($tanggalMulai, $tanggalSelesai, $jenisSidang);
        if (empty($possibleSlots)) {
            return ['options' => [], 'error' => 'Tidak ada slot waktu tersedia.', '_debug' => $this->debugLog];
        }

        if ($jenisSidang === 'Sidang TA 2') {
            $historyTa1 = Sidang::where('mahasiswa_id', $mahasiswaId)
                ->where('jenis_sidang', 'Seminar TA 1')
                ->where('status', 'lulus')
                ->latest()->first();

            if (!$historyTa1) {
                return ['options' => [], 'error' => 'Belum menyelesaikan Seminar TA 1.', '_debug' => $this->debugLog];
            }

            $pembimbing = Dosen::find($mahasiswa->pembimbing_id);
            $ketuaSidang = Dosen::find($historyTa1->ketua_sidang_id);
            $penguji1TA1 = Dosen::find($historyTa1->penguji1_id);
            $penguji2TA1 = Dosen::find($historyTa1->penguji2_id);

            if (!$pembimbing || !$ketuaSidang || !$penguji1TA1 || !$penguji2TA1) {
                return ['options' => [], 'error' => 'Data dosen dari TA 1 tidak lengkap.', '_debug' => $this->debugLog];
            }

            $slots = $this->findMultipleSlotsTA2(
                $possibleSlots,
                $pembimbing,
                $ketuaSidang,
                $penguji1TA1,
                $penguji2TA1,
                $numOptions
            );

            $options = [];
            foreach ($slots as $slot) {
                $options[] = array_merge($slot, [
                    'mahasiswa_id' => $mahasiswaId,
                    'jenis_sidang' => $jenisSidang,
                    'status' => 'belum lulus',
                    'ruangan' => $this->pilihRuangan($slot['tanggal'], $slot['jam_mulai'], $slot['jam_selesai']),
                    'pembimbing_nama' => $pembimbing->nama,
                    'ketua_nama' => $ketuaSidang->nama,
                    'p1_nama' => $pembimbing->nama,
                    'p2_nama' => $ketuaSidang->nama,
                    'p3_nama' => $penguji1TA1->nama,
                    'p4_nama' => $penguji2TA1->nama,
                ]);
            }

            return ['options' => $options, 'error' => empty($options) ? 'Tidak ada slot di mana keempat dosen tersedia bersamaan.' : null, '_debug' => $this->debugLog];
        }

        return ['options' => [], 'error' => 'Fitur opsi hanya tersedia untuk Seminar TA 2.', '_debug' => $this->debugLog];
    }

    /**
     * Collect up to $numOptions slots where all 4 fixed TA2 dosen are free.
     */
    private function findMultipleSlotsTA2(
        array $slots,
        Dosen $pembimbing,
        Dosen $ketuaSidang,
        Dosen $penguji1TA1,
        Dosen $penguji2TA1,
        int $numOptions = 3
    ): array {
        $results = [];
        foreach ($slots as $slot) {
            if (count($results) >= $numOptions)
                break;
            $ok =
                !$this->isBentrokMengajar($pembimbing, $slot) && !$this->isBentrokSidang($pembimbing, $slot) &&
                !$this->isBentrokMengajar($ketuaSidang, $slot) && !$this->isBentrokSidang($ketuaSidang, $slot) &&
                !$this->isBentrokMengajar($penguji1TA1, $slot) && !$this->isBentrokSidang($penguji1TA1, $slot) &&
                !$this->isBentrokMengajar($penguji2TA1, $slot) && !$this->isBentrokSidang($penguji2TA1, $slot);

            if ($ok) {
                $results[] = [
                    'penguji1_id' => $pembimbing->id,
                    'penguji2_id' => $ketuaSidang->id,
                    'penguji3_id' => $penguji1TA1->id,
                    'penguji4_id' => $penguji2TA1->id,
                    'ketua_sidang_id' => $ketuaSidang->id,
                    'tanggal' => $slot['tanggal'],
                    'hari' => $slot['hari'],
                    'jam_mulai' => $slot['jam_mulai'],
                    'jam_selesai' => $slot['jam_selesai'],
                ];
            }
        }
        return $results;
    }

    private function calculateAHPScore(Dosen $dosen, Mahasiswa $mahasiswa): float
    {
        $s1 = $this->getKetersediaanScore($dosen);            // C1
        $s2 = $this->getRelevansiScore($dosen, $mahasiswa);   // C2 (revised)
        $s3 = $this->getBebanScore($dosen);                   // C3 (utama)
        $s4 = $this->getPengalamanScore($dosen);              // C4

        $ahpScore = ($s1 * self::WEIGHTS['C1']) +
            ($s2 * self::WEIGHTS['C2']) +
            ($s3 * self::WEIGHTS['C3']) +
            ($s4 * self::WEIGHTS['C4']);

        return round($ahpScore, 6);
    }

    private array $dosenJadwalCountCache = [];
    private array $dosenMkCache = [];

    // Caching for Bentrok checks to prevent DB overload (N+1 scenario)
    private array $jadwalMengajarMapCache = [];
    private array $sidangDosenMapCache = [];
    private bool $isBentrokCacheLoaded = false;

    private function getKetersediaanScore(Dosen $dosen): float
    {
        if (!isset($this->dosenJadwalCountCache[$dosen->id])) {
            $this->dosenJadwalCountCache[$dosen->id] = JadwalMengajar::where('dosen_id', $dosen->id)->count();
        }
        $jadwalCount = $this->dosenJadwalCountCache[$dosen->id];

        if ($jadwalCount == 0)
            return 1.0;
        return max(0.1, 1 - ($jadwalCount / 16)); // dinormalisasi terhadap batas maks 16 SKS (UU No. 14/2005 Pasal 72 Ayat 2)
    }

    /**
     * REVISED C2: Relevansi Keilmuan
     * Menggabungkan dua sub-kriteria:
     * - Kecocokan rumpun keahlian (bobot 40%) — mendukung multi-rumpun per dosen
     * - Riwayat mengajar MK yang relevan (bobot 60%) ← sesuai arahan dosbing
     */
    private function getRelevansiScore(Dosen $dosen, Mahasiswa $mahasiswa): float
    {
        $rumpun = $mahasiswa->rumpun_ta;
        $mkList = self::RELEVANSI_MATA_KULIAH[$rumpun] ?? [];

        // Sub-score 1: Rumpun keahlian — dosen bisa punya >1 rumpun keahlian
        $rumpunScore = $this->dosenMatchRumpun($dosen, $rumpun) ? 1.0 : 0.0;

        // Sub-score 2: Riwayat mengajar MK relevan
        if (!isset($this->dosenMkCache[$dosen->id])) {
            $this->dosenMkCache[$dosen->id] = JadwalMengajar::where('dosen_id', $dosen->id)->pluck('mata_kuliah')->toArray();
        }
        $mkDosen = $this->dosenMkCache[$dosen->id];

        $matchCount = 0;
        foreach ($mkDosen as $mk) {
            foreach ($mkList as $mkR) {
                if (str_contains(strtolower($mk), strtolower(substr($mkR, 0, 10)))) {
                    $matchCount++;
                    break;
                }
            }
        }
        // Normalize: skor 1.0 jika pernah ajar >= 3 MK relevan
        $riwayatScore = min($matchCount / 3, 1.0);

        // Gabungkan: rumpun 40%, riwayat mengajar 60%
        return round(($rumpunScore * 0.4) + ($riwayatScore * 0.6), 6);
    }

    private function getBebanScore(Dosen $dosen): float
    {
        // C3 UTAMA (Beban): Pemerataan tegas berdasarkan selisih dengan beban minimum (lewat memory cache)
        $count = $this->dosenCounts[$dosen->id] ?? 0;

        if ($count <= $this->minCount) {
            return 1.0; // Dosen dengan beban terkecil langsung dapat skor sempurna
        }

        $toleransiSelisih = $this->dynamicTolerance ?: 10;

        // Semakin jauh selisihnya dari batas minimum, semakin drastis penaltinya.
        // Jika selisih melebihi toleransi dinamis, skor memprioritaskan mereka yang luang.
        $difference = $count - $this->minCount;
        $score = max(0.0, 1.0 - ($difference / $toleransiSelisih));

        return round($score, 6);
    }

    private array $dosenPengalamanCache = [];

    private function getPengalamanScore(Dosen $dosen): float
    {
        if (!isset($this->dosenPengalamanCache[$dosen->id])) {
            $count = Sidang::where(function ($q) use ($dosen) {
                $q->where('penguji1_id', $dosen->id)
                    ->orWhere('penguji2_id', $dosen->id)
                    ->orWhere('penguji3_id', $dosen->id)
                    ->orWhere('penguji4_id', $dosen->id)
                    ->orWhere('ketua_sidang_id', $dosen->id);
            })->where('status', 'lulus')->count();

            $this->dosenPengalamanCache[$dosen->id] = min(round($count / 5, 6), 1.0);
        }

        return $this->dosenPengalamanCache[$dosen->id];
    }

    private function generatePossibleSlots(string $start, string $end, string $jenisSidang = 'Seminar TA 1'): array
    {
        $slots = [];
        $current = Carbon::parse($start);
        $endDate = Carbon::parse($end);
        $hariMap = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat'];

        // Sesi pagi-siang (prioritas utama)
        // Sesi sore (diminimalisir — ditaruh belakangan agar dipakai terakhir)
        if ($jenisSidang === 'Sidang TA 2') {
            // Slot 2 jam — Senin s/d Kamis
            $sesiPagiSiang = [
                ['07:30', '09:30'],
                ['09:30', '11:30'],
                ['12:30', '14:30'],
            ];
            // Sore diminimalisir (tapi tetap tersedia jika dibutuhkan)
            $sesiSore = [
                ['14:30', '16:30'],
            ];
            // Jumat: istirahat sholat Jumat dari 11:30 sampai 13:30
            $sesiJumat = [
                ['07:30', '09:30'],
                ['09:30', '11:30'],
                ['13:30', '15:30'], // Mulai lagi setelah Jum'atan
                ['15:30', '17:30'], // Sesi sore Jumat
            ];
        } else {
            // Slot 1.5 jam (TA 1) — Senin s/d Kamis
            $sesiPagiSiang = [
                ['07:30', '09:00'],
                ['09:00', '10:30'],
                ['10:30', '12:00'],
                ['12:30', '14:00'],
                ['14:00', '15:30'],
            ];
            // Sore diminimalisir
            // $sesiSore = [
            // ['15:30', '17:00'],
            // ];
            // Jumat: istirahat sholat Jumat 10:30 sampai 13:30
            $sesiJumat = [
                ['07:30', '09:00'],
                ['09:00', '10:30'],
                // 10:30 - 13:30 istirahat/jum'atan (karena kalau dipaksakan sampai jam 12 nanggung)
                ['13:30', '15:00'],
                ['15:00', '16:30'], // Sesi sore Jumat
            ];
        }

        $jadwalResmi = self::JADWAL_RESMI[$jenisSidang] ?? [];
        $now = Carbon::now();

        while ($current->lte($endDate)) {
            $dayOfWeek = $current->dayOfWeekIso;
            if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
                $bulan = (int) $current->format('n');
                $tanggalMinimal = isset($jadwalResmi[$bulan])
                    ? Carbon::parse($jadwalResmi[$bulan])
                    : null;

                $boleh = $tanggalMinimal === null || $current->gte($tanggalMinimal);

                if ($boleh) {
                    $isFriday = ($dayOfWeek === 5);
                    $sesiHariIni = $isFriday
                        ? $sesiJumat
                        : array_merge($sesiPagiSiang, $sesiSore);

                    foreach ($sesiHariIni as $sesi) {
                        // Filter: jangan tampilkan slot yang sudah lewat (hari & jam sudah berlalu)
                        $slotDateTime = Carbon::parse($current->format('Y-m-d') . ' ' . $sesi[0]);
                        if ($slotDateTime->lte($now)) {
                            continue;
                        }

                        $slots[] = [
                            'tanggal' => $current->format('Y-m-d'),
                            'hari' => $hariMap[$dayOfWeek],
                            'jam_mulai' => $sesi[0],
                            'jam_selesai' => $sesi[1],
                        ];
                    }
                }
            }
            $current->addDay();
        }

        $this->log('[Slot] Total slot valid setelah filter jadwal resmi & waktu sekarang: ' . count($slots));
        return $slots;
    }

    private function initBentrokCache(): void
    {
        if ($this->isBentrokCacheLoaded)
            return;

        // Fetch all JadwalMengajar once — skip jadwal yang ditandai abaikan_bentrok (tim teaching)
        $allJadwal = JadwalMengajar::where('abaikan_bentrok', false)->get();
        foreach ($allJadwal as $j) {
            $this->jadwalMengajarMapCache[$j->dosen_id][$j->hari][] = [
                'mulai' => strtotime($j->jam_mulai),
                'selesai' => strtotime($j->jam_selesai),
            ];
        }

        // Fetch all Sidang once
        $allSidang = Sidang::all();
        foreach ($allSidang as $s) {
            $mulai = strtotime($s->jam_mulai);
            $selesai = strtotime($s->jam_selesai);

            $dosenIds = array_filter([$s->penguji1_id, $s->penguji2_id, $s->penguji3_id, $s->penguji4_id, $s->ketua_sidang_id]);
            foreach ($dosenIds as $dId) {
                $this->sidangDosenMapCache[$dId][$s->tanggal][] = [
                    'mulai' => $mulai,
                    'selesai' => $selesai,
                ];
            }
        }

        $this->isBentrokCacheLoaded = true;
    }

    private function isBentrokMengajar($dosen, $slot): bool
    {
        $this->initBentrokCache();

        $hari = $slot['hari'];
        if (empty($this->jadwalMengajarMapCache[$dosen->id][$hari])) {
            return false;
        }

        $sMulai = strtotime($slot['jam_mulai']);
        $sSelesai = strtotime($slot['jam_selesai']);

        foreach ($this->jadwalMengajarMapCache[$dosen->id][$hari] as $b) {
            if ($b['mulai'] < $sSelesai && $b['selesai'] > $sMulai) {
                return true;
            }
        }
        return false;
    }

    private function isBentrokSidang($dosen, $slot): bool
    {
        $this->initBentrokCache();

        $tgl = $slot['tanggal'];
        if (empty($this->sidangDosenMapCache[$dosen->id][$tgl])) {
            return false;
        }

        $sMulai = strtotime($slot['jam_mulai']);
        $sSelesai = strtotime($slot['jam_selesai']);

        foreach ($this->sidangDosenMapCache[$dosen->id][$tgl] as $b) {
            if ($b['mulai'] < $sSelesai && $b['selesai'] > $sMulai) {
                return true;
            }
        }
        return false;
    }

    /**
     * Pilih ruangan berdasarkan prioritas dan ketersediaan.
     * Prioritas: Ruang Sidang → TUK → Seminar LT-2.
     * Jika ruang prioritas sudah terpakai di slot yang sama, pakai ruang berikutnya.
     */
    private function pilihRuangan(string $tanggal, string $jamMulai, string $jamSelesai): string
    {
        foreach (self::RUANGAN as $ruangan) {
            $terpakai = Sidang::where('ruangan', $ruangan)
                ->where('tanggal', $tanggal)
                ->where(function ($q) use ($jamMulai, $jamSelesai) {
                    $q->whereTime('jam_mulai', '<', $jamSelesai)
                        ->whereTime('jam_selesai', '>', $jamMulai);
                })->exists();

            if (!$terpakai) {
                $this->log("[Ruangan] Terpilih: {$ruangan}");
                return $ruangan;
            }
        }

        // Semua ruangan penuh — fallback ke ruang utama (edge case)
        $this->log('[Ruangan] Semua ruangan penuh, fallback ke ruang utama.');
        return self::RUANGAN[0];
    }

    private function log(string $message): void
    {
        $this->debugLog[] = $message;
        Log::info("[SidangScheduler] {$message}");
    }
}
