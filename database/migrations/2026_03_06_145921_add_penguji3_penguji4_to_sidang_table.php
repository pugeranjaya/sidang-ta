<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sidang', function (Blueprint $table) {
            $table->unsignedBigInteger('penguji3_id')->nullable()->after('penguji2_id');
            $table->unsignedBigInteger('penguji4_id')->nullable()->after('penguji3_id');

            $table->foreign('penguji3_id')->references('id')->on('dosen')->nullOnDelete();
            $table->foreign('penguji4_id')->references('id')->on('dosen')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sidang', function (Blueprint $table) {
            $table->dropForeign(['penguji3_id']);
            $table->dropForeign(['penguji4_id']);
            $table->dropColumn(['penguji3_id', 'penguji4_id']);
        });
    }
};
