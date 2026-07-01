<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for PostgreSQL (Neon) deployment.
 * This replaces all 34 incremental migrations that were MySQL-specific.
 * Creates the final schema in one shot.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Users, password_reset_tokens, sessions (from Laravel default + role column)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('role')->default('admin');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // 2. Cache table
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        // 3. Jobs tables
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // 4. Dosen table (final schema after all alter migrations)
        Schema::create('dosen', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('email')->unique()->nullable();
            $table->string('nip')->unique()->nullable();
            $table->json('rumpun_keahlian')->nullable();
            $table->boolean('status')->default(true);
            $table->boolean('abaikan_bentrok')->default(false);
            $table->text('alasan_nonaktif')->nullable();
            $table->timestamps();
        });

        // 5. Mahasiswa table (final schema, renamed from mahasiswas)
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->string('nim')->unique();
            $table->string('nama');
            $table->string('judul_ta');
            $table->string('no_hp')->nullable();
            $table->string('rumpun_ta');
            $table->foreignId('pembimbing_id')->nullable()->constrained('dosen');
            $table->timestamps();
        });

        // 6. Jadwal mengajar table (final schema, renamed from jadwal_mengajars)
        Schema::create('jadwal_mengajar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dosen_id')->constrained('dosen')->onDelete('cascade');
            $table->string('hari');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('ruangan')->nullable();
            $table->string('mata_kuliah')->nullable();
            $table->string('tahun_akademik');
            $table->boolean('abaikan_bentrok')->default(false);
            $table->timestamps();
        });

        // 7. Rumpun keahlians table
        Schema::create('rumpun_keahlians', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        // 8. Sidang table (final schema, renamed from sidangs, status removed)
        Schema::create('sidang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->onDelete('cascade');
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('ruangan');
            $table->string('jenis_sidang')->default('Seminar TA 1');
            $table->foreignId('penguji1_id')->constrained('dosen');
            $table->foreignId('penguji2_id')->constrained('dosen');
            $table->unsignedBigInteger('penguji3_id')->nullable();
            $table->unsignedBigInteger('penguji4_id')->nullable();
            $table->foreignId('ketua_sidang_id')->nullable()->constrained('dosen')->nullOnDelete();
            $table->foreign('penguji3_id')->references('id')->on('dosen')->nullOnDelete();
            $table->foreign('penguji4_id')->references('id')->on('dosen')->nullOnDelete();
            $table->timestamps();
        });

        // 9. Pengecualian jadwals table
        Schema::create('pengecualian_jadwals', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->time('jam_mulai_diblokir');
            $table->time('jam_selesai_diblokir');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengecualian_jadwals');
        Schema::dropIfExists('sidang');
        Schema::dropIfExists('rumpun_keahlians');
        Schema::dropIfExists('jadwal_mengajar');
        Schema::dropIfExists('mahasiswa');
        Schema::dropIfExists('dosen');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
