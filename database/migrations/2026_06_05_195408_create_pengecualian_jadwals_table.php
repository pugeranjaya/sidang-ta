<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pengecualian_jadwals', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->time('jam_mulai_diblokir');
            $table->time('jam_selesai_diblokir');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengecualian_jadwals');
    }
};
