<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE sidang MODIFY COLUMN jenis_sidang ENUM('Seminar TA 1', 'Seminar TA 2', 'Sidang TA 2') DEFAULT 'Seminar TA 1'");
        DB::statement("UPDATE sidang SET jenis_sidang = 'Sidang TA 2' WHERE jenis_sidang = 'Seminar TA 2'");
        DB::statement("ALTER TABLE sidang MODIFY COLUMN jenis_sidang ENUM('Seminar TA 1', 'Sidang TA 2') DEFAULT 'Seminar TA 1'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sidang_ta2', function (Blueprint $table) {
            //
        });
    }
};
