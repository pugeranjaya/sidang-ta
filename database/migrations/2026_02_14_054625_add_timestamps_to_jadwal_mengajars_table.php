<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('jadwal_mengajars', function (Blueprint $table) {
            $table->timestamps();  // ← ini tambah created_at & updated_at sekaligus
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_mengajars', function (Blueprint $table) {
            $table->dropColumn(['created_at', 'updated_at']);
        });
    }
};