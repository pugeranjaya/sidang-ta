<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sidang', function (Blueprint $table) {
            $table->string('status')->default('belum lulus')->change();
        });
        
        DB::statement("UPDATE sidang SET status = 'lulus' WHERE status IN ('selesai', 'disetujui')");
        DB::statement("UPDATE sidang SET status = 'belum lulus' WHERE status IN ('pending', 'tidak lulus')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sidang', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });
        
        DB::statement("UPDATE sidang SET status = 'selesai' WHERE status = 'lulus'");
        DB::statement("UPDATE sidang SET status = 'pending' WHERE status = 'belum lulus'");
    }
};
