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
        Schema::create('data_karyawan', function (Blueprint $table) {
            $table->id();
            $table->string('nik')->unique();
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_karyawan');
    }
};
