<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Dosen;

class UpdateRumpunSeeder extends Seeder
{
    public function run(): void
    {
        $rumpunData = [
            'SPOI' => [
                'Nur Indrianti',
                'Apriani Soepardi',
                'Mochammad Chaeron',
                'Puryani',
                'Sutrisno',
                'Shodiq Abdul Khannan',
            ],
            'PSIE' => [
                'Dyah Rachmawati Lucitasari',
                'Irwan Soejanto',
                'Intan Berlianty',
                'Ahmad Muhsin',
            ],
            'KPDP' => [
                'Sadi',
                'Tri Wibawa',
                'Gunawan Madyono Putro',
                'Andreas Mahendro Kuncoro',
                'Eric Ohara',
            ],
            'RPTE' => [
                'Laila Nafisah',
                'Agus Ristono',
                'Eko Nursubiyantoro',
                'Trismi Ristyowati',
                'Puji Handayani Kasih',
                'Achmad Chairdino Leuveano',
            ],
        ];

        $dosenList = Dosen::all();
        $updatedCount = 0;

        foreach ($dosenList as $dosen) {
            $matchedRumpun = [];
            foreach ($rumpunData as $code => $keywords) {
                foreach ($keywords as $keyword) {
                    if (stripos($dosen->nama, $keyword) !== false) {
                        $matchedRumpun[] = $code;
                        break;
                    }
                }
            }

            if (!empty($matchedRumpun)) {
                $dosen->rumpun_keahlian = $matchedRumpun;
                $dosen->save();
                $updatedCount++;
            }
        }

        echo "Updated $updatedCount lecturers with correct Rumpun from file.\n";
    }
}
