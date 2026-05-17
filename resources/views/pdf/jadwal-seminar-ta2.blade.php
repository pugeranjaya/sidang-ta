<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Jadwal Seminar TA 2</title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            margin: 20px;
        }

        .header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
            font-size: 14pt;
        }

        .header table,
        .header th,
        .header td {
            border: none;
            padding: 0;
        }

        .info {
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 30px;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 5px;
            vertical-align: top;
        }

        th {
            background-color: #f2f2f2;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .grup-block {
            page-break-inside: avoid;
            break-inside: avoid;
        }
    </style>
</head>

<body>

    <div class="header">
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 0;">
            <tr>
                <td style="width: 15%; text-align: left; vertical-align: middle;">
                    @php
                        if (!function_exists('formatNip')) {
                            function formatNip($nip) {
                                $nip = preg_replace('/[^0-9]/', '', (string)$nip);
                                if (strlen($nip) === 18) {
                                    return preg_replace('/^(\d{8})(\d{6})(\d{1})(\d{3})$/', '$1 $2 $3 $4', $nip);
                                }
                                return $nip;
                            }
                        }

                        $imagePath = public_path('images/logo-upn.png');
                        $src = '';
                        if (file_exists($imagePath)) {
                            $imageData = base64_encode(file_get_contents($imagePath));
                            $src = 'data:image/png;base64,' . $imageData;
                        }
                    @endphp
                    @if($src)
                        <img src="{{ $src }}" alt="Logo UPN" style="max-height: 120px; width: auto; object-fit: contain;">
                    @endif
                </td>
                <td style="width: 70%; text-align: center; vertical-align: middle; line-height: 1.3;">
                    UNIVERSITAS PEMBANGUNAN NASIONAL "VETERAN" YOGYAKARTA<br>
                    FAKULTAS TEKNIK INDUSTRI<br>
                    PROGRAM STUDI TEKNIK INDUSTRI<br>
                    <span style="font-size: 11pt; font-weight: normal;">Jl. Babarsari No. 2 Tambakbayan Yogyakarta
                        55281, Telepon & Fax (0274) 486256</span>
                </td>
                <td style="width: 15%;"></td>
            </tr>
        </table>

        <hr style="border: 1px solid black; margin-top: 5px; margin-bottom: 10px;">

        <div style="text-align: center; line-height: 1.3; margin-top: 20px;">
            JADWAL SEMINAR TUGAS AKHIR {{ strtoupper($jenis_sidang) }}<br>
            PROGRAM STUDI TEKNIK INDUSTRI<br>
            PERIODE {{ strtoupper($bulan_nama) }} {{ $tahun }}
        </div>
    </div>

    @foreach($groupedSidang as $grup => $sidangs)
        @php
            // format $grup is "tanggal|ruangan"
            list($tanggalStr, $ruangan) = explode('|', $grup);
            $tanggal = \Carbon\Carbon::parse($tanggalStr)->translatedFormat('l, d F Y');
        @endphp

        <div class="grup-block">
            <div class="info">
                <strong>Hari/Tanggal : {{ $tanggal }}</strong>
            </div>
            <div class="info">
                <strong>Ruang : {{ $ruangan }}</strong>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 3%">No</th>
                        <th style="width: 8%">Jam</th>
                        <th style="width: 12%">Nama</th>
                        <th style="width: 17%">Judul</th>
                        <th style="width: 10%">Pembimbing</th>
                        <th style="width: 10%">Ketua Sidang</th>
                        <th style="width: 40%">Penguji</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sidangs as $index => $s)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center" style="white-space: nowrap;">
                                {{ \Carbon\Carbon::parse($s->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($s->jam_selesai)->format('H:i') }}
                            </td>
                            <td>
                                {{ $s->mahasiswa->nama ?? '-' }}<br>
                                {{ $s->mahasiswa->nim ?? '-' }}
                            </td>
                            <td>{{ $s->mahasiswa->judul_ta ?? '-' }}</td>
                            <td>{{ $s->mahasiswa->pembimbing->nama ?? '-' }}</td>
                            <td>{{ $s->ketuaSidang->nama ?? '-' }}</td>
                            <td>
                                1. {{ $s->penguji1->nama ?? '-' }}<br>
                                2. {{ $s->penguji2->nama ?? '-' }}<br>
                                3. {{ $s->penguji3->nama ?? '-' }}<br>
                                4. {{ $s->penguji4->nama ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    @if($groupedSidang->isEmpty())
        <div style="text-align: center; margin-top: 50px; font-style: italic;">
            Tidak ada data jadwal untuk periode ini.
        </div>
    @endif

    <table style="border: none; margin-top: 40px; width: 100%; border-collapse: collapse;">
        <tr>
            <td style="border: none; width: 60%; vertical-align: top; padding: 0;">
                <p style="margin: 0; font-weight: bold;">Keterangan :</p>
                <p style="margin: 0;">Mahasiswa yang dijadwalkan seminar, diharapkan</p>
                <table style="border: none; margin: 0; padding: 0; width: 100%;">
                    <tr>
                        <td style="border: none; width: 15px; vertical-align: top; padding: 0;">1.</td>
                        <td style="border: none; padding: 0; vertical-align: top;">Mengkonfirmasi ulang kesediaan Dosen
                            Pembimbing sesuai jadwal yang telah disusun</td>
                    </tr>
                    <tr>
                        <td style="border: none; width: 15px; vertical-align: top; padding: 0;">2.</td>
                        <td style="border: none; padding: 0; vertical-align: top;">Hadir 30 menit sebelum pelaksanaan
                            seminar</td>
                    </tr>
                </table>
            </td>
            <td style="border: none; width: 40%; vertical-align: top; text-align: center; padding: 0;">
                <p style="margin: 0;">Yogyakarta, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <br><br><br><br><br>
                <p style="margin: 0; text-decoration: underline;">{{ $kajur->nama }}</p>
                <p style="margin: 0;">NIP. {{ formatNip($kajur->nip) }}</p>
            </td>
        </tr>
    </table>

</body>

</html>