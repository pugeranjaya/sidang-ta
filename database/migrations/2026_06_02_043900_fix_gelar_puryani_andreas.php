<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tambahkan gelar Ir. untuk Puryani
        DB::table('dosen')
            ->where('nama', 'LIKE', 'Puryani%')
            ->whereNot('nama', 'LIKE', 'Ir.%')
            ->update(['nama' => DB::raw("'Ir. ' || nama")]);

        // Tambahkan gelar Ir. untuk Andreas
        DB::table('dosen')
            ->where('nama', 'LIKE', 'Andreas Mahendro%')
            ->whereNot('nama', 'LIKE', 'Ir.%')
            ->update(['nama' => DB::raw("'Ir. ' || nama")]);
    }

    public function down(): void
    {
        DB::table('dosen')
            ->where('nama', 'LIKE', 'Ir. Puryani%')
            ->update(['nama' => DB::raw("replace(nama, 'Ir. ', '')")]);

        DB::table('dosen')
            ->where('nama', 'LIKE', 'Ir. Andreas Mahendro%')
            ->update(['nama' => DB::raw("replace(nama, 'Ir. ', '')")]);
    }
};
