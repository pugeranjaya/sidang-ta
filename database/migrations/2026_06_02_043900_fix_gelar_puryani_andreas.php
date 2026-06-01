<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tambahkan gelar Ir. untuk Puryani
        DB::table('dosens')
            ->where('nama', 'LIKE', 'Puryani%')
            ->whereNot('nama', 'LIKE', 'Ir.%')
            ->update(['nama' => DB::raw("CONCAT('Ir. ', nama)")]);

        // Tambahkan gelar Ir. untuk Andreas
        DB::table('dosens')
            ->where('nama', 'LIKE', 'Andreas Mahendro%')
            ->whereNot('nama', 'LIKE', 'Ir.%')
            ->update(['nama' => DB::raw("CONCAT('Ir. ', nama)")]);
    }

    public function down(): void
    {
        DB::table('dosens')
            ->where('nama', 'LIKE', 'Ir. Puryani%')
            ->update(['nama' => DB::raw("REPLACE(nama, 'Ir. ', '')")]);

        DB::table('dosens')
            ->where('nama', 'LIKE', 'Ir. Andreas Mahendro%')
            ->update(['nama' => DB::raw("REPLACE(nama, 'Ir. ', '')")]);
    }
};
