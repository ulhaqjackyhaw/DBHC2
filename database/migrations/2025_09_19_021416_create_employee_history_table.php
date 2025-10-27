<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_id')->constrained('versions')->onDelete('cascade');

            // Kolom yang sama persis dengan tabel data_karyawan
            $table->string('nik');
            $table->string('nama');
            $table->string('gender');
            $table->string('kode_jabatan');
            $table->string('lokasi');
            $table->string('unit');
            $table->string('jabatan');
            $table->string('kelompok_kelas_jabatan');
            $table->string('grade');
            $table->string('status_kepegawaian');
            $table->string('asal_instansi')->nullable();
            $table->string('tanggal_lahir');
            $table->string('pendidikan_terakhir');
            $table->string('tmt');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_history');
    }
};