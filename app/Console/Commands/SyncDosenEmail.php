<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Dosen;

class SyncDosenEmail extends Command
{
    protected $signature = 'sync:dosen-email';
    protected $description = 'Sync dosen email from users table';

    public function handle()
    {
        $users = User::where('role', 'dosen')->get();
        foreach ($users as $user) {
            Dosen::where('nama', 'like', '%' . $user->name . '%')->update(['email' => $user->email]);
        }
        $this->info('Synced successfully');
    }
}
