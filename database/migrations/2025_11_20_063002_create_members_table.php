<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_create_members_table.php
    public function up()
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique(); // UID dari Kartu RFID
            $table->string('nim_nip')->unique(); // NPM atau NIP
            $table->string('nama');
            $table->enum('kategori', ['Mahasiswa', 'Dosen', 'Staff']);
            $table->string('fakultas')->nullable();
            $table->string('jurusan')->nullable();
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
