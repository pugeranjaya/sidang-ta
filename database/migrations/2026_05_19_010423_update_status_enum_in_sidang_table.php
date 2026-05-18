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
        // 1. Expand ENUM to hold both old and new values safely
        DB::statement("ALTER TABLE sidang MODIFY COLUMN status ENUM('pending', 'disetujui', 'selesai', 'lulus', 'tidak lulus', 'belum lulus') DEFAULT 'belum lulus'");
        
        // 2. Map old values to new values
        DB::statement("UPDATE sidang SET status = 'lulus' WHERE status IN ('selesai', 'disetujui')");
        DB::statement("UPDATE sidang SET status = 'belum lulus' WHERE status IN ('pending', 'tidak lulus')");
        
        // 3. Shrink ENUM to only contain the 2 new values
        DB::statement("ALTER TABLE sidang MODIFY COLUMN status ENUM('belum lulus', 'lulus') DEFAULT 'belum lulus'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Expand ENUM to hold old values
        DB::statement("ALTER TABLE sidang MODIFY COLUMN status ENUM('pending', 'disetujui', 'selesai', 'lulus', 'tidak lulus', 'belum lulus') DEFAULT 'pending'");
        
        // 2. Map new values back to old values (best effort)
        DB::statement("UPDATE sidang SET status = 'selesai' WHERE status = 'lulus'");
        DB::statement("UPDATE sidang SET status = 'pending' WHERE status = 'belum lulus'");
        
        // 3. Shrink ENUM back to original
        DB::statement("ALTER TABLE sidang MODIFY COLUMN status ENUM('pending', 'disetujui', 'selesai') DEFAULT 'pending'");
    }
};
