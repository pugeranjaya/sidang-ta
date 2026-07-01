<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\User;
use App\Models\Dosen;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Jalankan DatabaseSeeder utama supaya data mahasiswa, jadwal, dan admin/dosen default masuk ke Render (sama persis kayak di localhost)
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);

        $dosenData = [
            ['nama' => 'Prof. Ir. Nur Indrianti, M.T., D.Eng., IPU, ASEAN Eng.', 'email' => 'nur.indrianti@upnyk.ac.id'],
            ['nama' => 'Dr. Ir. Apriani Soepardi, S.T.P., M.T.', 'email' => 'apriani.soepardi@upnyk.ac.id'],
            ['nama' => 'Mochammad Chaeron, S.T., M.T.', 'email' => 'mochammad.chaeron@upnyk.ac.id'],
            ['nama' => 'Ir. Puryani, S.T., M.T.', 'email' => 'puryani@upnyk.ac.id'],
            ['nama' => 'Sutrisno, S.Si., M.T.', 'email' => 'sutrisno@upnyk.ac.id'],
            ['nama' => 'Dr. Muhammad Shodiq Abdul Khannan, S.T., M.T.', 'email' => 'muhammad.shodiq.abdul.khannan@upnyk.ac.id'],
            ['nama' => 'Ir. Dyah Rachmawati Lucitasari, M.T.', 'email' => 'dyah.rachmawati.lucitasari@upnyk.ac.id'],
            ['nama' => 'Ir. Irwan Soejanto, M.T.', 'email' => 'irwan.soejanto@upnyk.ac.id'],
            ['nama' => 'Ir. Intan Berlianty, S.T., M.T.', 'email' => 'intan.berlianty@upnyk.ac.id'],
            ['nama' => 'Ahmad Muhsin, S.T., M.Eng.', 'email' => 'ahmad.muhsin@upnyk.ac.id'],
            ['nama' => 'Dr. Ir. Sadi, S.T., M.T.', 'email' => 'sadi@upnyk.ac.id'],
            ['nama' => 'Dr. Ir. Tri Wibawa, S.T., M.T.', 'email' => 'tri.wibawa@upnyk.ac.id'],
            ['nama' => 'Ir. Gunawan Madyono Putro, S.T., M.T.', 'email' => 'gunawan.madyono.putro@upnyk.ac.id'],
            ['nama' => 'Ir. Andreas Mahendra Kuncoro, S.T., M.Sc., M.Sc., Ph.D', 'email' => 'andreas.mahendra.kuncoro@upnyk.ac.id'],
            ['nama' => 'Eric Ohara, S.T., MSIE, M.Sc.', 'email' => 'eric.ohara@upnyk.ac.id'],
            ['nama' => 'Ir. Laila Nafisah, S.T., M.T.', 'email' => 'laila.nafisah@upnyk.ac.id'],
            ['nama' => 'Dr. Agus Ristono, S.T., M.T.', 'email' => 'agus.ristono@upnyk.ac.id'],
            ['nama' => 'Ir. Eko Nursubiyantoro, S.T., M.T.', 'email' => 'eko.nursubiyantoro@upnyk.ac.id'],
            ['nama' => 'Trismi Ristyowati, S.T., M.T.', 'email' => 'trismi.ristyowati@upnyk.ac.id'],
            ['nama' => 'Puji Handayani Kasih, S.T., M.T.', 'email' => 'puji.handayani.kasih@upnyk.ac.id'],
            ['nama' => 'Ir. R. Achmad Chairdino Leuveano, S.T., M.Sc., Ph.D', 'email' => 'r.achmad.chairdino.leuveano@upnyk.ac.id'],
        ];

        // The default password is 'password123' based on the image
        $defaultPassword = Hash::make('password123');

        DB::transaction(function () use ($dosenData, $defaultPassword) {
            foreach ($dosenData as $dosen) {
                // Insert into `users` table
                User::firstOrCreate(
                    ['email' => $dosen['email']],
                    [
                        'name' => $dosen['nama'],
                        'role' => 'dosen',
                        'password' => $defaultPassword,
                        'email_verified_at' => now(),
                    ]
                );

                // For the `dosen` table, try to find an existing record that might have been created by `DosenSeeder` with a generic email.
                // Or create it if it doesn't exist. We check by similar name (using like).
                // Or just create it with a dummy NIP/Rumpun Keahlian if they don't exist at all.
                $cleanNameForSearch = '%' . preg_replace('/[^a-z]/', '%', strtolower(preg_replace('/(prof|dr|ir|s\.t|m\.t|d\.eng|ipu|asean eng|m\.sc|m\.eng|s\.tp|s\.si|m\.si|ph\.d|m\.kom|m\.hum|dra)\.?/i', '', $dosen['nama']))) . '%';
                
                $existingDosen = Dosen::whereRaw('LOWER(nama) LIKE ?', [$cleanNameForSearch])->first();
                if ($existingDosen) {
                    // Update email to exactly match so they can login
                    $existingDosen->email = $dosen['email'];
                    $existingDosen->nama = $dosen['nama']; // Update formatting to match image
                    $existingDosen->save();
                } else {
                    Dosen::create([
                        'nama' => $dosen['nama'],
                        'email' => $dosen['email'],
                        'nip' => 'DUMMY-' . uniqid(), // Fix: Unique dummy nip to prevent constraint violation
                        'rumpun_keahlian' => ['Umum'],
                    ]);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration needed
    }
};
