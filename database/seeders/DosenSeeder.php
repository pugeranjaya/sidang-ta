<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Dosen;

class DosenSeeder extends Seeder
{
    public function run(): void
    {
        $dosenData = [
            // ========== SPOI ==========
            [
                'nama' => 'Prof. Ir. Nur Indrianti, M.T., D.Eng., IPU, ASEAN Eng.',
                'nip' => '19670508 202121 2 001',
                'rumpun_keahlian' => ['SPOI'],
            ],
            [
                'nama' => 'Dr. Ir. Apriani Soepardi, S.T.P., M.T.',
                'nip' => '19731118 202121 2 005',
                'rumpun_keahlian' => ['SPOI'],
            ],
            [
                'nama' => 'Mochammad Chaeron, S.T., M.T.',
                'nip' => '19700724 202121 1 003',
                'rumpun_keahlian' => ['SPOI'],
            ],
            [
                'nama' => 'Ir. Puryani, S.T., M.T.',
                'nip' => '19720208 202121 2 005',
                'rumpun_keahlian' => ['SPOI'],
            ],
            [
                'nama' => 'Sutrisno, S.Si., M.T.',
                'nip' => '19780420 202121 1 008',
                'rumpun_keahlian' => ['SPOI'],
            ],
            [
                'nama' => 'Dr. M. Shodiq Abdul Khannan, S.T., M.T.',
                'nip' => '19821010 200501 1 003',
                'rumpun_keahlian' => ['SPOI'],
            ],

            // ========== PSIE ==========
            [
                'nama' => 'Ir. Dyah Rachmawati Lucitasari, M.T.',
                'nip' => '19651219 199103 2 001',
                'rumpun_keahlian' => ['PSIE'],
            ],
            [
                'nama' => 'Ir. Irwan Soejanto, M.T.',
                'nip' => '19660111 199403 1 001',
                'rumpun_keahlian' => ['PSIE'],
            ],
            [
                'nama' => 'Ir. Intan Berlianty, S.T., M.T.',
                'nip' => '19761019 200501 2 001',
                'rumpun_keahlian' => ['PSIE'],
            ],
            [
                'nama' => 'Ahmad Muhsin, S.T., M.Eng.',
                'nip' => '19791221 202121 1 002',
                'rumpun_keahlian' => ['PSIE'],
            ],

            // ========== KPDP ==========
            [
                'nama' => 'Dr. Ir. Sadi, S.T., M.T.',
                'nip' => '19710313 202121 1 002',
                'rumpun_keahlian' => ['KPDP'],
            ],
            [
                'nama' => 'Dr. Ir. Tri Wibawa, S.T., M.T.',
                'nip' => '19730216 202121 1 003',
                'rumpun_keahlian' => ['KPDP'],
            ],
            [
                'nama' => 'Ir. Gunawan Madyono Putro, S.T., M.T.',
                'nip' => '19690914 199103 1 004',
                'rumpun_keahlian' => ['KPDP'],
            ],
            [
                'nama' => 'Ir. Andreas  Mahendro Kuncoro, S.T., M.Sc., M.Sc., Ph.D',
                'nip' => '19811117 202203 1 001',
                'rumpun_keahlian' => ['KPDP'],
            ],
            [
                'nama' => 'Eric Ohara, S.T., MSIE, M.Sc.',
                'nip' => '19840912 202421 1 013',
                'rumpun_keahlian' => ['KPDP'],
            ],

            // ========== RPTE ==========
            [
                'nama' => 'Ir. Laila Nafisah, S.T., M.T.',
                'nip' => '19710520 202121 2 002',
                'rumpun_keahlian' => ['RPTE'],
            ],
            [
                'nama' => 'Dr. Agus Ristono, S.T., M.T.',
                'nip' => '19741125 202121 1 003',
                'rumpun_keahlian' => ['RPTE'],
            ],
            [
                'nama' => 'Ir. Eko Nursubiyantoro, S.T., M.T.',
                'nip' => '19680921 199103 1 001',
                'rumpun_keahlian' => ['RPTE'],
            ],
            [
                'nama' => 'Trismi Ristyowati, S.T., M.T.',
                'nip' => '19770828 202121 2 004',
                'rumpun_keahlian' => ['RPTE'],
            ],
            [
                'nama' => 'Puji Handayani Kasih, S.T., M.T.',
                'nip' => '19931007 202012 2 015',
                'rumpun_keahlian' => ['RPTE'],
            ],
            [
                'nama' => 'Ir. Raden Achmad Chairdino Leuveano, S.T., M.Sc., Ph.D.',
                'nip' => '19890212 202203 1 009',
                'rumpun_keahlian' => ['RPTE'],
            ],
        ];

        // Ensure we clear DB to avoid duplicates if seeding multiple times
        Dosen::query()->delete();

        $jabatans = ['Asisten Ahli', 'Lektor', 'Lektor Kepala', 'Guru Besar'];

        foreach ($dosenData as $d) {
            $formattedNip = str_replace(' ', '', $d['nip']);
            // Generate email otomatis dari nama (bersihkan gelar)
            $cleanName = strtolower($d['nama']);
            $cleanName = preg_replace('/(prof|dr|ir|s\.t|m\.t|d\.eng|ipu|asean eng|m\.sc|m\.eng|s\.tp|s\.si|m\.si|ph\.d|m\.kom|m\.hum|dra)\.?/i', '', $cleanName);
            $cleanName = preg_replace('/[^a-z]/', '', $cleanName); // Ambil huruf saja
            $email = $cleanName . '@upnyk.ac.id';
            Dosen::create([
                'nama' => $d['nama'],
                'nip' => $formattedNip,
                'email' => $email,
                'rumpun_keahlian' => $d['rumpun_keahlian'],
            ]);
        }
    }
}
