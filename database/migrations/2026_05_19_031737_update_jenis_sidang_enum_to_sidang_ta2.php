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
            $table->string('jenis_sidang')->default('Seminar TA 1')->change();
        });
        DB::statement("UPDATE sidang SET jenis_sidang = 'Sidang TA 2' WHERE jenis_sidang = 'Seminar TA 2'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sidang', function (Blueprint $table) {
            $table->string('jenis_sidang')->default('Seminar TA 1')->change();
        });
        DB::statement("UPDATE sidang SET jenis_sidang = 'Seminar TA 2' WHERE jenis_sidang = 'Sidang TA 2'");
    }
};
