<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Member;
use App\Models\VisitLog;
// Hapus atau komen baris Contract\Database jika masih merah
// use Kreait\Firebase\Contract\Database; 

class FirebaseGateListener extends Command
{
    protected $signature = 'gate:firebase-listen';
    protected $description = 'Memantau Firebase Realtime Database untuk aksi Smart Gate';

    protected $database;

    // Ubah constructor agar tidak meminta Interface secara langsung
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info("✅ Menghubungkan ke Firebase...");

        try {
            // Memanggil database melalui app container (Laravel Firebase Provider)
            $this->database = app('firebase.database'); 
            $reference = $this->database->getReference('gate_system');
        } catch (\Exception $e) {
            $this->error("Gagal terhubung ke Firebase: " . $e->getMessage());
            return self::FAILURE;
        }

        $this->info("✅ Berhasil! Menunggu kartu ditap...");

        while (true) {
            try {
                $snapshot = $reference->getValue();
                // ... (Sisa kode logika Anda tetap sama seperti sebelumnya)
                
                // Contoh logika pengecekan singkat
                $command = $snapshot['command'] ?? 'done';
                if ($command === 'waiting') {
                    $this->processGate($reference, $snapshot['last_uid'] ?? '');
                }

            } catch (\Exception $e) {
                $this->error("Error: " . $e->getMessage());
            }

            sleep(1); 
        }
    }

    // Pindahkan logika ke fungsi terpisah agar rapi
    private function processGate($reference, $uid) {
        $uidBersih = strtoupper(trim($uid));
        $member = Member::where('uid', $uidBersih)->first();

        if ($member && $member->status == 'aktif') {
            $this->info("Akses diterima: " . $member->nama);
            // Logika VisitLog ...
            $reference->update(['command' => 'open']);
        } else {
            $this->error("Akses ditolak untuk UID: " . $uidBersih);
            $reference->update(['command' => 'reject']);
        }
    }
}