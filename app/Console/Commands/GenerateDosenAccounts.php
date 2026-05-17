<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Dosen;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class GenerateDosenAccounts extends Command
{
    protected $signature = 'dosen:generate-accounts';
    protected $description = 'Generate temporary user accounts for dosens who do not have one';

    public function handle()
    {
        $dosens = Dosen::whereNull('email')->orWhere('email', '')->get();
        $count = 0;

        $titles = ['Dr.', 'Ir.', ',', 'S.T.', 'M.T.', 'M.Eng.', 'Ph.D', 'S.Kom.', 'M.Kom.', 'M.Sc.', 'Prof.', 'ST', 'MT'];

        foreach ($dosens as $dosen) {
            $cleanName = trim(str_replace($titles, '', $dosen->nama));
            // Hapus spasi berlebih
            $cleanName = preg_replace('/\s+/', ' ', $cleanName);
            
            $slug = Str::slug($cleanName, '.');
            if (empty($slug)) {
                $slug = 'dosen.' . $dosen->id;
            }
            
            $email = $slug . '@upnyk.ac.id';

            // Ensure unique email
            $originalEmail = $email;
            $counter = 1;
            while (User::where('email', $email)->exists()) {
                $email = str_replace('@upnyk.ac.id', $counter . '@upnyk.ac.id', $originalEmail);
                $counter++;
            }

            $user = User::create([
                'name' => $dosen->nama,
                'email' => $email,
                'password' => Hash::make('password123'),
                'role' => 'dosen',
            ]);

            $dosen->update(['email' => $email]);
            $this->info("Created account for {$dosen->nama}: {$email}");
            $count++;
        }

        $this->info("Successfully generated $count accounts.");
    }
}
