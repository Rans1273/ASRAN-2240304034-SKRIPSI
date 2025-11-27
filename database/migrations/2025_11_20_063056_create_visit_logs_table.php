<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_create_visit_logs_table.php
    public function up()
    {
        Schema::create('visit_logs', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel members
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            
            $table->dateTime('waktu_masuk'); // Menyimpan Tanggal + Jam + Menit
            $table->dateTime('waktu_keluar')->nullable(); // Jika NULL berarti sedang aktif
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visit_logs');
    }
};
