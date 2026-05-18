<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Berita Acara - {{ $sidang->mahasiswa->nama ?? '' }}</title>
    <style>
        @page {
            margin: 2cm;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            line-height: 1.3;
            color: #000;
        }
        .text-center { text-align: center; }
        .text-justify { text-align: justify; }
        .bold { font-weight: bold; }
        .underline { text-decoration: underline; }
        .uppercase { text-transform: uppercase; }
        .nowrap { white-space: nowrap; }

        /* Header Teks */
        .header-teks {
            margin-bottom: 10px;
        }
        .header-teks div {
            font-size: 11pt;
        }

        /* Judul */
        .judul {
            font-size: 13pt;
            margin: 15px 0;
            letter-spacing: 1px;
        }

        /* Paragraf */
        .paragraf {
            margin-bottom: 15px;
        }

        /* Data Mahasiswa */
        .table-data {
            width: 100%;
            margin-bottom: 15px;
        }
        .table-data td {
            vertical-align: top;
            padding: 2px 0;
        }

        /* TTD Pejabat */
        .ttd-pejabat-container {
            width: 100%;
            margin-top: 15px;
            margin-bottom: 25px;
        }
        .ttd-pejabat-container td {
            width: 50%;
            vertical-align: top;
            padding: 0 5px;
        }

        /* Seksi TTD Detail */
        .section-title {
            margin-top: 12px;
            margin-bottom: 4px;
        }
        .table-ttd-detail {
            width: 100%;
            border-collapse: collapse;
        }
        .table-ttd-detail td {
            padding: 2px 0;
            vertical-align: top;
        }
        /* Rapetin jarak baris data biar muat 1 halaman */
        .table-ttd-detail .data-row td {
            padding-top: 20px; 
        }

        /* Keterangan Nilai */
        .keterangan-nilai {
            margin-top: 25px;
            font-size: 10pt;
        }
        .table-keterangan {
            width: 100%;
            margin-top: 5px;
        }
    </style>
</head>
<body>

    <!-- Header Teks -->
    <div class="header-teks">
        <div>KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</div>
        <div class="underline">UNIVERSITAS PEMBANGUNAN NASIONAL "VETERAN" YOGYAKARTA</div>
    </div>

    <!-- Judul -->
    <div class="judul text-center bold underline">BERITA ACARA</div>

    @php
        use Carbon\Carbon;
        Carbon::setLocale('id');
        $mhs = $sidang->mahasiswa;
        $dt = Carbon::parse($sidang->tanggal);
        
        $hari = $dt->translatedFormat('l');
        $bulan = $dt->translatedFormat('F');
        
        if (!function_exists('terbilang')) {
            function terbilang($nilai) {
                $nilai = abs($nilai);
                $huruf = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
                $temp = "";
                if ($nilai < 12) {
                    $temp = " ". $huruf[$nilai];
                } else if ($nilai < 20) {
                    $temp = terbilang($nilai - 10). " Belas";
                } else if ($nilai < 100) {
                    $temp = terbilang($nilai/10)." Puluh". terbilang($nilai % 10);
                } else if ($nilai < 200) {
                    $temp = " Seratus" . terbilang($nilai - 100);
                } else if ($nilai < 1000) {
                    $temp = terbilang($nilai/100) . " Ratus" . terbilang($nilai % 100);
                } else if ($nilai < 2000) {
                    $temp = " Seribu" . terbilang($nilai - 1000);
                } else if ($nilai < 10000) {
                    $temp = terbilang($nilai/1000) . " Ribu" . terbilang($nilai % 1000);
                }
                return $temp;
            }
        }

        if (!function_exists('formatNip')) {
            function formatNip($nip) {
                $nip = preg_replace('/[^0-9]/', '', (string)$nip);
                if (strlen($nip) === 18) {
                    return preg_replace('/^(\d{8})(\d{6})(\d{1})(\d{3})$/', '$1 $2 $3 $4', $nip);
                }
                return $nip;
            }
        }

        $tglHuruf = trim(terbilang((int)$dt->format('j')));
        $thnHuruf = trim(terbilang((int)$dt->format('Y')));
        
        $jam = (int)$dt->format('H');
        $menit = (int)$dt->format('i');
        $jamHuruf = trim(terbilang($jam));
        $menitHuruf = ($menit > 0) ? trim(terbilang($menit)) : "";
        
        $waktuEjaan = $jamHuruf . ($menitHuruf ? " " . $menitHuruf : "");
        
        $jenisSidangLabel = ($sidang->jenis_sidang === 'Seminar TA 2') 
            ? 'Ujian Pendadaran Tugas Akhir II' 
            : 'Seminar Tugas Akhir I';
    @endphp

    <!-- Paragraf Pembuka -->
    <p class="paragraf text-justify">
        Pada hari ini {{ $hari }} tanggal {{ $tglHuruf }} bulan {{ $bulan }} tahun {{ $thnHuruf }} dari pukul {{ $waktuEjaan }} Waktu Indonesia Bagian Barat sampai dengan selesai bertempat di {{ $sidang->ruangan }} Jurusan Teknik Industri Fakultas Teknik Industri UPN "Veteran" Yogyakarta telah dilaksanakan {{ $jenisSidangLabel }}.
    </p>

    <!-- Tabel Data -->
    <table class="table-data">
        <tr>
            <td style="width: 130px;">Nama</td>
            <td style="width: 15px;">:</td>
            <td class="uppercase">{{ $mhs->nama ?? '-' }}</td>
            <td style="width: 70px;">No. Mhs</td>
            <td style="width: 15px;">:</td>
            <td>{{ $mhs->nim ?? '-' }}</td>
        </tr>
        <tr>
            <td>Fakultas</td>
            <td>:</td>
            <td colspan="4">Teknik Industri</td>
        </tr>
        <tr>
            <td>Jurusan</td>
            <td>:</td>
            <td colspan="4">Teknik Industri</td>
        </tr>
        <tr>
            <td>Judul Tugas Akhir</td>
            <td>:</td>
            <td colspan="4" class="uppercase text-justify">{{ $mhs->judul_ta ?? '-' }}</td>
        </tr>
    </table>

    <!-- Hasil Penilaian (Coretan dihapus sesuai Gambar 3) -->
    <div style="margin: 15px 0;" class="text-justify">
        Berdasar hasil penilaian dari Tim/Penelaah mahasiswa tersebut dinyatakan LULUS / TIDAK LULUS dengan Nilai : A / B+ / B / C+ / C *)
    </div>

    <!-- TTD Pejabat (Sejajar Baris demi Baris) -->
    <table class="ttd-pejabat-container">
        <tr>
            <td></td>
            <td style="padding-left: 5%;">
                <p style="margin:0">Yogyakarta, {{ $dt->translatedFormat('j F Y') }}</p>
                <div style="height: 5px;"></div>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top; padding-left: 0;">
                <p style="margin:0">Ketua Jurusan Teknik Industri</p>
            </td>
            <td style="padding-left: 5%; vertical-align: top;">
                <p style="margin:0">Koordinator Prodi Teknik Industri</p>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top; padding-left: 0;">
                <div style="height: 70px;"></div>
                <p style="margin:0" class="nowrap">{{ $kajur->nama ?? '-' }}</p>
                <p style="margin:0">NIP. {{ formatNip($kajur->nip ?? '-') }}</p>
            </td>
            <td style="padding-left: 5%; vertical-align: top;">
                <div style="height: 70px;"></div>
                <p style="margin:0" class="nowrap">{{ $koordinator->nama ?? '-' }}</p>
                <p style="margin:0">NIP. {{ formatNip($koordinator->nip ?? '-') }}</p>
            </td>
        </tr>
    </table>

    <!-- Nama Pemakalah Section (Jarak ditambah sesuai Gambar 1) -->
    <table class="table-ttd-detail">
        <tr class="bold underline">
            <td style="width: 50%;">Nama Pemakalah</td>
            <td style="width: 15%;">NIM</td>
            <td style="width: 35%;">Tanda Tangan</td>
        </tr>
        <tr class="data-row">
            <td class="uppercase">{{ $mhs->nama ?? '-' }}</td>
            <td>{{ $mhs->nim ?? '-' }}</td>
            <td>....................</td>
        </tr>
    </table>

    <!-- Dosen Pembimbing -->
    <div class="section-title bold underline">Dosen Pembimbing</div>
    <table class="table-ttd-detail">
        <tr class="data-row">
            <td style="width: 55%;">1. {{ $mhs->pembimbing->nama ?? '-' }}</td>
            <td style="width: 45%;">1....................</td>
        </tr>
    </table>

    <!-- Ketua Sidang -->
    <div class="section-title bold underline">Ketua Sidang</div>
    <table class="table-ttd-detail">
        <tr class="data-row">
            <td style="width: 55%;">1. {{ $sidang->ketuaSidang->nama ?? '-' }}</td>
            <td style="width: 45%;">1....................</td>
        </tr>
    </table>

    <!-- Dosen Penguji -->
    <div class="section-title bold underline">Dosen Penguji</div>
    <table class="table-ttd-detail">
        <tr class="data-row">
            <td style="width: 55%;">1. {{ $sidang->penguji1->nama ?? '-' }}</td>
            <td style="width: 45%;">1....................</td>
        </tr>
        <tr class="data-row">
            <td style="width: 55%;">2. {{ $sidang->penguji2->nama ?? '-' }}</td>
            <td style="width: 45%; padding-left: 150px;">2....................</td>
        </tr>
        @if($sidang->penguji3)
        <tr class="data-row">
            <td style="width: 55%;">3. {{ $sidang->penguji3->nama }}</td>
            <td style="width: 45%;">3....................</td>
        </tr>
        @endif
        @if($sidang->penguji4)
        <tr class="data-row">
            <td style="width: 55%;">4. {{ $sidang->penguji4->nama }}</td>
            <td style="width: 45%; padding-left: 150px;">4....................</td>
        </tr>
        @endif
    </table>

    <!-- Keterangan (Simbol diperbaiki sesuai Gambar 2) -->
    <div class="keterangan-nilai">
        *) Keterangan:
        <table class="table-keterangan">
            <tr>
                <td>A : X <span style="font-family: DejaVu Sans;">≥</span> 85</td>
                <td>B+ : 80 <span style="font-family: DejaVu Sans;">≤</span> X <span style="font-family: DejaVu Sans;">≤</span> 85</td>
                <td>B : 75 <span style="font-family: DejaVu Sans;">≤</span> X <span style="font-family: DejaVu Sans;">≤</span> 80</td>
                <td>C+ : 70 <span style="font-family: DejaVu Sans;">≤</span> X <span style="font-family: DejaVu Sans;">≤</span> 75</td>
                <td>C : 60 <span style="font-family: DejaVu Sans;">≤</span> X <span style="font-family: DejaVu Sans;">≤</span> 70</td>
            </tr>
        </table>
    </div>

    <!-- ==================== DAFTAR HADIR ==================== -->
    <div style="page-break-before: always;"></div>

    <!-- Header Teks (Daftar Hadir) -->
    <div class="header-teks">
        <div>KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</div>
        <div class="underline">UNIVERSITAS PEMBANGUNAN NASIONAL "VETERAN" YOGYAKARTA</div>
    </div>
    <br>
    <div class="judul text-center bold underline" style="line-height: 1.5;">DAFTAR HADIR<br>{{ strtoupper($jenisSidangLabel) }}</div>
    <br>
    
    <table class="table-data" style="margin-bottom: 20px;">
        <tr><td style="width: 130px;">Fakultas</td><td style="width: 15px;">:</td><td class="uppercase">TEKNIK INDUSTRI</td></tr>
        <tr><td>Jurusan</td><td>:</td><td class="uppercase">TEKNIK INDUSTRI</td></tr>
        <tr><td>Program Studi</td><td>:</td><td class="uppercase">S1 TEKNIK INDUSTRI</td></tr>
        <tr><td>Hari, Tanggal</td><td>:</td><td>{{ $hari }}, {{ $dt->translatedFormat('j F Y') }}</td></tr>
        <tr><td>Tempat</td><td>:</td><td>{{ $sidang->ruangan }} Jurusan Teknik Industri</td></tr>
        <tr><td>Pukul</td><td>:</td><td>{{ $dt->format('H.i') }} WIB - Selesai</td></tr>
    </table>

    <table border="1" style="width: 100%; border-collapse: collapse; text-align: center; margin-bottom: 30px;">
        <tr class="bold">
            <td style="padding: 10px;">NAMA MAHASISWA</td>
            <td style="padding: 10px;">NO. MAHASISWA</td>
            <td style="padding: 10px;">TANDA TANGAN</td>
        </tr>
        <tr>
            <td style="padding: 15px; text-transform: uppercase;">{{ $mhs->nama ?? '-' }}</td>
            <td style="padding: 15px;">{{ $mhs->nim ?? '-' }}</td>
            <td style="padding: 15px; text-align: left;">1.........................</td>
        </tr>
    </table>

    <table class="ttd-pejabat-container" style="margin-top: 15px; margin-bottom: 15px;">
        <tr>
            <td style="vertical-align: top; padding-left: 0;">
                <p style="margin:0">Ketua Jurusan Teknik Industri</p>
            </td>
            <td style="padding-left: 5%; vertical-align: top;">
                <p style="margin:0">Koordinator Prodi Teknik Industri</p>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top; padding-left: 0;">
                <div style="height: 70px;"></div>
                <p style="margin:0; font-weight: bold;" class="nowrap">{{ $kajur->nama ?? '-' }}</p>
                <p style="margin:0">NIP. {{ formatNip($kajur->nip ?? '-') }}</p>
            </td>
            <td style="padding-left: 5%; vertical-align: top;">
                <div style="height: 70px;"></div>
                <p style="margin:0; font-weight: bold;" class="nowrap">{{ $koordinator->nama ?? '-' }}</p>
                <p style="margin:0">NIP. {{ formatNip($koordinator->nip ?? '-') }}</p>
            </td>
        </tr>
    </table>

    <!-- Dosen Pembimbing -->
    <div class="section-title bold underline">Dosen Pembimbing</div>
    <table class="table-ttd-detail">
        <tr class="data-row">
            <td style="width: 55%;">1. {{ $mhs->pembimbing->nama ?? '-' }}</td>
            <td style="width: 45%;">1....................</td>
        </tr>
    </table>

    <!-- Ketua Sidang -->
    <div class="section-title bold underline">Ketua Sidang</div>
    <table class="table-ttd-detail">
        <tr class="data-row">
            <td style="width: 55%;">1. {{ $sidang->ketuaSidang->nama ?? '-' }}</td>
            <td style="width: 45%;">1....................</td>
        </tr>
    </table>

    <!-- Dosen Penguji -->
    <div class="section-title bold underline">Dosen Penguji</div>
    <table class="table-ttd-detail">
        <tr class="data-row">
            <td style="width: 55%;">1. {{ $sidang->penguji1->nama ?? '-' }}</td>
            <td style="width: 45%;">1....................</td>
        </tr>
        <tr class="data-row">
            <td style="width: 55%;">2. {{ $sidang->penguji2->nama ?? '-' }}</td>
            <td style="width: 45%; padding-left: 150px;">2....................</td>
        </tr>
        @if($sidang->penguji3)
        <tr class="data-row">
            <td style="width: 55%;">3. {{ $sidang->penguji3->nama }}</td>
            <td style="width: 45%;">3....................</td>
        </tr>
        @endif
        @if($sidang->penguji4)
        <tr class="data-row">
            <td style="width: 55%;">4. {{ $sidang->penguji4->nama }}</td>
            <td style="width: 45%; padding-left: 150px;">4....................</td>
        </tr>
        @endif
    </table>

    <!-- ==================== LEMBAR REVISI ==================== -->
    <div style="page-break-before: always;"></div>
    
    <div class="judul text-center bold underline" style="margin-bottom: 25px;">LEMBAR REVISI<br>{{ strtoupper($jenisSidangLabel) }}</div>
    
    <table class="table-data" style="margin-bottom: 20px;">
        <tr><td style="width: 150px;">Nama Mahasiswa</td><td style="width: 15px;">:</td><td class="uppercase">{{ $mhs->nama ?? '-' }}</td></tr>
        <tr><td>Nomor Mahasiswa</td><td>:</td><td>{{ $mhs->nim ?? '-' }}</td></tr>
        <tr><td>Judul Tugas Akhir</td><td>:</td><td class="uppercase">{{ $mhs->judul_ta ?? '-' }}</td></tr>
    </table>

    <table border="1" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <tr class="bold text-center">
            <td style="padding: 10px; width: 85%;">Catatan/Revisi</td>
            <td style="padding: 10px; width: 15%;">Hal</td>
        </tr>
        @for($i=0; $i<17; $i++)
        <tr>
            <td style="padding: 13px;"></td>
            <td style="padding: 13px;"></td>
        </tr>
        @endfor
    </table>

    <table style="width: 100%; margin-top: 20px;">
        <tr>
            <td style="width: 60%;"></td>
            <td style="width: 40%; text-align: left;">
                <p style="margin:0">Yogyakarta, {{ $dt->translatedFormat('j F Y') }}</p>
                <p style="margin:0">Penguji,</p>
                <div style="height: 80px;"></div>
                <p style="margin:0">(...................................................)</p>
            </td>
        </tr>
    </table>

</body>
</html>
