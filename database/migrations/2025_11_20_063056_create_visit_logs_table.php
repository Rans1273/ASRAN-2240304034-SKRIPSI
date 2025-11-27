<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('visit_logs', function (Blueprint $table) {
            $table->id(); // Atribut ID
            
            // Relasi ke tabel members (untuk ambil Nama, NIM/NIP, Kategori, Fakultas, Jurusan)
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            
            // Menyimpan Tanggal & Jam sekaligus
            $table->dateTime('waktu_masuk'); 
            $table->dateTime('waktu_keluar')->nullable(); // Null = Belum keluar
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_logs');
    }
};