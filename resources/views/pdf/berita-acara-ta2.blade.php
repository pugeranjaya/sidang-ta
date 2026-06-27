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
            margin-bottom: 15px;
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

        /* Data Mahasiswa Top */
        .table-data-top {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .table-data-top td {
            vertical-align: top;
            padding: 2px 0;
        }

        /* TTD Pejabat */
        .ttd-pejabat-container {
            width: 100%;
            margin-top: 15px;
            margin-bottom: 15px;
        }
        .ttd-pejabat-container td {
            width: 50%;
            vertical-align: top;
        }

        /* Seksi TTD Detail */
        .section-title {
            margin-top: 10px;
            margin-bottom: 5px;
        }
        .table-ttd-detail {
            width: 100%;
            border-collapse: collapse;
        }
        .table-ttd-detail td {
            padding: 6px 0;
            vertical-align: top;
        }

        /* Daftar Hadir TTD Detail */
        .section-title-hadir {
            margin-top: 15px;
            margin-bottom: 5px;
        }
        .table-ttd-hadir {
            width: 100%;
            border-collapse: collapse;
        }
        .table-ttd-hadir td {
            padding: 12px 0;
            vertical-align: top;
        }

        /* Keterangan Nilai */
        .keterangan-nilai {
            margin-top: 5px;
            font-size: 9pt;
        }
        .table-keterangan {
            width: 100%;
            margin-top: 2px;
        }

        /* Form Revisi Table */
        .table-form-revisi {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .table-form-revisi th, .table-form-revisi td {
            padding: 8px;
        }
        .table-form-revisi th {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            font-weight: normal;
        }
        .table-form-revisi tr:last-child td {
            border-bottom: none;
        }

        /* SK Revisi Table */
        .table-sk-revisi {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table-sk-revisi th, .table-sk-revisi td {
            padding: 8px;
            border: 1px solid #000;
        }
    </style>
</head>
<body>

    @php
        use Carbon\Carbon;
        Carbon::setLocale('id');
        $mhs = $sidang->mahasiswa;
        $dt = Carbon::parse($sidang->tanggal);
        
        $judulTA = $mhs->judul_ta ?? '-';
        $judulLen = strlen($judulTA);
        if ($judulLen > 150) $fontSizeJudul = '9pt';
        elseif ($judulLen > 100) $fontSizeJudul = '10pt';
        else $fontSizeJudul = '11pt';
        
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
        
        $jamMulai = Carbon::parse($sidang->jam_mulai);
        $jam = (int)$jamMulai->format('H');
        $menit = (int)$jamMulai->format('i');
        $jamHuruf = trim(terbilang($jam));
        $menitHuruf = ($menit > 0) ? trim(terbilang($menit)) : "";
        
        $waktuEjaan = $jamHuruf . ($menitHuruf ? " " . $menitHuruf : "");
        
        $jenisSidangLabel = 'Ujian Pendadaran Tugas Akhir II';

        $pengujis = [
            $sidang->penguji1->nama ?? '-',
            $sidang->penguji2->nama ?? '-',
            $sidang->penguji3->nama ?? '-',
            $sidang->penguji4->nama ?? '-'
        ];
    @endphp

    <!-- ==================== BERITA ACARA ==================== -->
    <div class="header-teks">
        <div>KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</div>
        <div class="underline uppercase">UNIVERSITAS PEMBANGUNAN NASIONAL "VETERAN" YOGYAKARTA</div>
    </div>

    <div class="judul text-center bold underline">BERITA ACARA</div>

    <p class="paragraf text-justify">
        Pada hari ini {{ $hari }} tanggal {{ $tglHuruf }} bulan {{ $bulan }} tahun {{ $thnHuruf }} dari pukul {{ $waktuEjaan }} Waktu Indonesia Bagian Barat sampai dengan selesai bertempat di {{ $sidang->ruangan }} Jurusan Teknik Industri Fakultas Teknik Industri UPN "Veteran" Yogyakarta telah dilaksanakan {{ $jenisSidangLabel }}.
    </p>

    <table class="table-data-top">
        <tr>
            <td style="width: 130px;">Nama</td>
            <td style="width: 15px;">:</td>
            <td class="bold uppercase" style="white-space: nowrap;">{{ $mhs->nama ?? '-' }}</td>
            <td style="width: 70px;">No. Mhs</td>
            <td style="width: 15px;">:</td>
            <td class="bold">{{ $mhs->nim ?? '-' }}</td>
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
            <td colspan="4" class="uppercase text-justify" style="font-size: {{ $fontSizeJudul }};">{{ $judulTA }}</td>
        </tr>
    </table>

    <div style="margin: 15px 0;" class="text-justify">
        Berdasar hasil penilaian dari Tim Penguji/Penelaah mahasiswa tersebut dinyatakan LULUS/TIDAK LULUS dengan Nilai : A / B+ / B / C+ / C *)
    </div>

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
                <div style="height: 60px;"></div>
                <p style="margin:0" class="nowrap">{{ $kajur->nama ?? '-' }}</p>
                <p style="margin:0">NIP. {{ formatNip($kajur->nip ?? '-') }}</p>
            </td>
            <td style="padding-left: 5%; vertical-align: top;">
                <div style="height: 60px;"></div>
                <p style="margin:0" class="nowrap">{{ $koordinator->nama ?? '-' }}</p>
                <p style="margin:0">NIP. {{ formatNip($koordinator->nip ?? '-') }}</p>
            </td>
        </tr>
    </table>

    <table class="table-ttd-detail">
        <tr>
            <td class="underline" style="width: 50%;">Nama Pemakalah</td>
            <td style="width: 15%;">NIM</td>
            <td style="width: 35%;">Tanda Tangan</td>
        </tr>
        <tr>
            <td class="uppercase" style="white-space: nowrap;">{{ $mhs->nama ?? '-' }}</td>
            <td>{{ $mhs->nim ?? '-' }}</td>
            <td>.............................</td>
        </tr>
    </table>

    <div class="section-title underline">Dosen Pembimbing</div>
    <table class="table-ttd-detail">
        <tr>
            <td style="width: 65%;">1. {{ $mhs->pembimbing->nama ?? '-' }}</td>
            <td style="width: 35%;">1. ....................</td>
        </tr>
    </table>

    <div class="section-title underline">Ketua Sidang</div>
    <table class="table-ttd-detail">
        <tr>
            <td style="width: 65%;">1. {{ $sidang->ketuaSidang->nama ?? '-' }}</td>
            <td style="width: 35%;">1. ....................</td>
        </tr>
    </table>

    <div class="section-title underline">Dosen Penguji</div>
    <table class="table-ttd-detail">
        <tr>
            <td style="width: 65%;">1. {{ $sidang->penguji1->nama ?? '-' }}</td>
            <td style="width: 35%;">1. ....................</td>
        </tr>
        <tr>
            <td style="width: 65%;">2. {{ $sidang->penguji2->nama ?? '-' }}</td>
            <td style="width: 35%; padding-left: 100px;">2. ....................</td>
        </tr>
        <tr>
            <td style="width: 65%;">3. {{ $sidang->penguji3->nama ?? '-' }}</td>
            <td style="width: 35%;">3. ....................</td>
        </tr>
        <tr>
            <td style="width: 65%;">4. {{ $sidang->penguji4->nama ?? '-' }}</td>
            <td style="width: 35%; padding-left: 100px;">4. ....................</td>
        </tr>
    </table>

    <div class="keterangan-nilai">
        <table class="table-keterangan" style="border: none;">
            <tr>
                <td style="width: 90px; vertical-align: top; white-space: nowrap;">*) Keterangan</td>
                <td style="width: 10px; vertical-align: top;">:</td>
                <td style="white-space: nowrap;">
                    A : X <span style="font-family: DejaVu Sans;">≥</span> 85 &nbsp;&nbsp;&nbsp; 
                    B+ : 80 <span style="font-family: DejaVu Sans;">≤</span> X < 85 &nbsp;&nbsp;&nbsp; 
                    B : 75 <span style="font-family: DejaVu Sans;">≤</span> X < 80 &nbsp;&nbsp;&nbsp; 
                    C+ : 70 <span style="font-family: DejaVu Sans;">≤</span> X < 75 &nbsp;&nbsp;&nbsp; 
                    C : 60 <span style="font-family: DejaVu Sans;">≤</span> X < 70
                </td>
            </tr>
        </table>
    </div>

    <!-- ==================== DAFTAR HADIR ==================== -->
    <div style="page-break-before: always;"></div>

    <div class="header-teks">
        <div>KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</div>
        <div class="underline uppercase">UNIVERSITAS PEMBANGUNAN NASIONAL "VETERAN" YOGYAKARTA</div>
    </div>
    <br>
    <div class="judul text-center" style="line-height: 1.5; margin-bottom: 25px;">
        <span class="bold">DAFTAR HADIR</span><br>
        <span class="bold underline uppercase">{{ $jenisSidangLabel }}</span>
    </div>
    
    <table class="table-data-top" style="margin-bottom: 25px;">
        <tr><td style="width: 130px;">Fakultas</td><td style="width: 15px;">:</td><td class="uppercase">TEKNIK INDUSTRI</td></tr>
        <tr><td>Jurusan</td><td>:</td><td class="uppercase">TEKNIK INDUSTRI</td></tr>
        <tr><td>Program Studi</td><td>:</td><td class="uppercase">S1 TEKNIK INDUSTRI</td></tr>
        <tr><td>Hari, Tanggal</td><td>:</td><td>{{ $hari }}, {{ $dt->translatedFormat('j F Y') }}</td></tr>
        <tr><td>Tempat</td><td>:</td><td>{{ $sidang->ruangan }} Jurusan Teknik Industri</td></tr>
        <tr><td>Pukul</td><td>:</td><td>{{ $jamMulai->format('H.i') }} WIB - Selesai</td></tr>
    </table>

    <table border="1" style="width: 100%; border-collapse: collapse; text-align: center; margin-bottom: 30px;">
        <tr>
            <td style="padding: 10px;">NAMA MAHASISWA</td>
            <td style="padding: 10px;">NO. MAHASISWA</td>
            <td style="padding: 10px;">TANDA TANGAN</td>
        </tr>
        <tr>
            <td style="padding: 15px; text-transform: uppercase;">{{ $mhs->nama ?? '-' }}</td>
            <td style="padding: 15px;">{{ $mhs->nim ?? '-' }}</td>
            <td style="padding: 15px; text-align: left;">&nbsp;&nbsp;&nbsp;&nbsp;</td>
        </tr>
    </table>

    <table class="ttd-pejabat-container" style="margin-top: 15px; margin-bottom: 15px;">
        <tr>
            <td style="vertical-align: top; padding-left: 0;">
                <p style="margin:0">Ketua Jurusan Teknik Industri</p>
                <div style="height: 60px;"></div>
                <p style="margin:0" class="nowrap">{{ $kajur->nama ?? '-' }}</p>
                <p style="margin:0">NIP. {{ formatNip($kajur->nip ?? '-') }}</p>
            </td>
            <td style="padding-left: 5%; vertical-align: top;">
                <p style="margin:0">Koordinator Prodi Teknik Industri</p>
                <div style="height: 60px;"></div>
                <p style="margin:0" class="nowrap">{{ $koordinator->nama ?? '-' }}</p>
                <p style="margin:0">NIP. {{ formatNip($koordinator->nip ?? '-') }}</p>
            </td>
        </tr>
    </table>

    <div class="section-title-hadir underline">Dosen Pembimbing</div>
    <table class="table-ttd-hadir">
        <tr>
            <td style="width: 65%;">1. {{ $mhs->pembimbing->nama ?? '-' }}</td>
            <td style="width: 35%;">1. ....................</td>
        </tr>
    </table>

    <div class="section-title-hadir underline">Ketua Sidang</div>
    <table class="table-ttd-hadir">
        <tr>
            <td style="width: 65%;">1. {{ $sidang->ketuaSidang->nama ?? '-' }}</td>
            <td style="width: 35%;">1. ....................</td>
        </tr>
    </table>

    <div class="section-title-hadir underline">Dosen Penguji</div>
    <table class="table-ttd-hadir">
        <tr>
            <td style="width: 65%;">1. {{ $sidang->penguji1->nama ?? '-' }}</td>
            <td style="width: 35%;">1. ....................</td>
        </tr>
        <tr>
            <td style="width: 65%;">2. {{ $sidang->penguji2->nama ?? '-' }}</td>
            <td style="width: 35%; padding-left: 100px;">2. ....................</td>
        </tr>
        <tr>
            <td style="width: 65%;">3. {{ $sidang->penguji3->nama ?? '-' }}</td>
            <td style="width: 35%;">3. ....................</td>
        </tr>
        <tr>
            <td style="width: 65%;">4. {{ $sidang->penguji4->nama ?? '-' }}</td>
            <td style="width: 35%; padding-left: 100px;">4. ....................</td>
        </tr>
    </table>


    <!-- ==================== FORM REVISI (PER PENGUJI) ==================== -->
    @foreach($pengujis as $pengujiName)
    <div style="page-break-before: always;"></div>
    
    <div class="header-teks">
        <div>KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</div>
        <div class="underline uppercase">UNIVERSITAS PEMBANGUNAN NASIONAL "VETERAN" YOGYAKARTA</div>
    </div>
    <br>
    
    <div class="judul text-center" style="margin-bottom: 25px;">
        <span class="bold">FORM REVISI <span class="uppercase">{{ $jenisSidangLabel }}</span></span>
    </div>
    
    <table class="table-data-top" style="margin-bottom: 10px;">
        <tr><td style="width: 150px;">Nama Pemakalah</td><td style="width: 15px;">:</td><td class="uppercase">{{ $mhs->nama ?? '-' }}</td></tr>
        <tr><td>No. Mahasiswa</td><td>:</td><td>{{ $mhs->nim ?? '-' }}</td></tr>
        <tr><td>Judul Skripsi</td><td>:</td><td class="uppercase text-justify" style="font-size: {{ $fontSizeJudul }};">{{ $judulTA }}</td></tr>
    </table>

    <hr style="border: 0; border-bottom: 2px solid #000; margin-bottom: 20px;">

    <div class="bold underline text-center" style="margin-bottom: 15px;">REVISI/CATATAN PENGUJI</div>
    <table class="table-form-revisi" style="margin-bottom: 10px;">
        <tr>
            <th style="width: 10%; text-align: center; background-color: #e5e5e5;">No.</th>
            <th style="width: 90%; text-align: center; background-color: #e5e5e5;">U r a i a n</th>
        </tr>
        @for($i=0; $i<17; $i++)
        <tr>
            <td style="padding: 14px;"></td>
            <td style="padding: 14px;"></td>
        </tr>
        @endfor
    </table>

    <div style="width: 100%; position: absolute; bottom: 0;">
        <hr style="border: 0; border-top: 1px solid #000; margin: 0 0 10px 0;">
        <table style="width: 100%;">
            <tr>
                <td style="width: 60%; vertical-align: top;">
                    <p style="margin:0"><span class="underline">Catatan</span> :</p>
                    <p style="margin:0">Lembar ini harus ditunjukkan</p>
                    <p style="margin:0">kepada dosen saat melaksanakan revisi.</p>
                </td>
                <td style="width: 40%; text-align: center; vertical-align: top;">
                    <p style="margin:0; padding-bottom: 15px;">Yogyakarta, {{ $dt->translatedFormat('j F Y') }}</p>
                    <p style="margin:0">Penguji</p>
                    <div style="height: 60px;"></div>
                    <div class="nowrap" style="font-size: 9pt;">{{ $pengujiName }}</div>
                </td>
            </tr>
        </table>
    </div>
    @endforeach

    <!-- ==================== SURAT KETERANGAN REVISI ==================== -->
    <div style="page-break-before: always;"></div>

    <div class="header-teks">
        <div>KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</div>
        <div class="underline uppercase">UNIVERSITAS PEMBANGUNAN NASIONAL "VETERAN" YOGYAKARTA</div>
    </div>
    <br>

    <div class="judul text-center" style="line-height: 1.5; margin-bottom: 25px;">
        <span class="bold">SURAT KETERANGAN TELAH MELAKSANAKAN</span><br>
        <span class="bold">REVISI DRAFT TUGAS AKHIR II</span>
    </div>

    <p class="paragraf">Yang bertanda tangan dibawah ini menerangkan bahwa Tugas Akhir atas nama :</p>
    
    <table class="table-data-top" style="margin-bottom: 15px;">
        <tr>
            <td style="width: 150px;">Nama</td>
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
            <td colspan="4" class="uppercase text-justify" style="font-size: {{ $fontSizeJudul }};">{{ $judulTA }}</td>
        </tr>
    </table>

    <p class="paragraf text-justify" style="margin-bottom: 30px;">
        Telah melaksanakan revisi draft Tugas Akhir II yang telah disidangkan pada hari {{ $hari }} tanggal {{ $dt->translatedFormat('j F Y') }}.
    </p>

    <div style="text-align: right; margin-top: 20px; margin-bottom: 30px;">
        <p style="margin:0; padding-right: 50px;">Yogyakarta, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ $dt->format('Y') }}</p>
    </div>

    <p class="paragraf" style="margin-bottom: 15px;">Dosen Penguji yang memerintahkan Revisi :</p>

    <table class="table-sk-revisi">
        <tr>
            <th style="width: 5%; text-align: center; font-weight: normal;">No</th>
            <th style="width: 45%; text-align: center; font-weight: normal;">N a m a</th>
            <th style="width: 20%; text-align: center; font-weight: normal;">Jabatan</th>
            <th style="width: 30%; text-align: center; font-weight: normal;">Tanda Tangan</th>
        </tr>
        @foreach($pengujis as $index => $pengujiName)
        <tr>
            <td style="text-align: center;">{{ $index + 1 }}.</td>
            <td>{{ $pengujiName }}</td>
            <td style="text-align: center;">Penguji {{ ['I', 'II', 'III', 'IV'][$index] }}</td>
            <td>{{ $index + 1 }}.  ........................</td>
        </tr>
        @endforeach
    </table>

</body>
</html>
