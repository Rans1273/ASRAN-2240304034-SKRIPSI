<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id(); // ID internal sistem
            $table->string('uid')->unique(); // UID dari Kartu RFID
            $table->string('nama');
            $table->string('npm_nip'); // Bisa diisi NPM atau NIP
            $table->string('fakultas');
            $table->string('jurusan');
            $table->enum('kategori', ['dosen', 'mahasiswa', 'staff']); // Kategori user
            $table->enum('status', ['aktif', 'blokir'])->default('aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
