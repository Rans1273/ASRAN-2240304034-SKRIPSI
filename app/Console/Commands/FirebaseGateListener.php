<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Member; //
use App\Models\VisitLog; //
use Carbon\Carbon;
use Kreait\Firebase\Contract\Database; //

class FirebaseGateListener extends Command
{
    /**
     * Nama dan signature dari console command.
     * Jalankan dengan: php artisan gate:firebase-listen
     */
    protected $signature = 'gate:firebase-listen';

    /**
     * Deskripsi console command.
     */
    protected $description = 'Memantau Firebase Realtime Database untuk aksi Smart Gate dan mencatat kunjungan';

    protected $database;

    /**
     * Membuat instance command baru.
     */
    public function __construct(Database $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    /**
     * Mengeksekusi console command.
     */
    public function handle()
    {
        $this->info("✅ Menghubungkan ke Firebase. Menunggu kartu ditap...");

        try {
            // Referensi ke path 'gate_system' di Firebase Realtime Database
            $reference = $this->database->getReference('gate_system');
        } catch (\Exception $e) {
            $this->error("Gagal terhubung ke Firebase: " . $e->getMessage());
            return self::FAILURE;
        }

        // Loop terus-menerus untuk mendengarkan perubahan data di Firebase
        while (true) {
            try {
                $snapshot = $reference->getValue();
                $lastUid = $snapshot['last_uid'] ?? null;
                $command = $snapshot['command'] ?? 'done';

                // Jika ESP32 mengirim status 'waiting', proses logika gate
                if ($command === 'waiting' && !empty($lastUid)) {
                    $this->processGate($reference, $lastUid);
                }
            } catch (\Exception $e) {
                $this->error("Error saat memantau Firebase: " . $e->getMessage());
            }

            // Jeda 1 detik agar tidak membebani server
            sleep(1);
        }
    }

    /**
     * Logika utama pemrosesan gate dan database MySQL
     */
    private function processGate($reference, $uid)
    {
        $uidBersih = strtoupper(trim($uid));
        $this->info("------------------------------------------------");
        $this->info("RFID Terdeteksi: $uidBersih");
        
        // 1. Cari member di database MySQL berdasarkan UID
        $member = Member::where('uid', $uidBersih)->first();

        if ($member) {
            // Cek apakah member dalam status aktif
            if ($member->status !== 'aktif') {
                $this->error("Akses Ditolak: Member " . $member->nama . " tidak aktif.");
                $reference->update([
                    'command' => 'reject',
                    'status_msg' => 'Member Tidak Aktif'
                ]);
                return;
            }

            $this->info("Member Ditemukan: " . $member->nama);

            // 2. Logika pencatatan VisitLog (Tap Masuk vs Tap Keluar)
            // Cari data kunjungan terakhir member yang belum mencatat waktu keluar
            $lastLog = VisitLog::where('member_id', $member->id)
                ->whereNull('waktu_keluar')
                ->latest('waktu_masuk')
                ->first();

            if ($lastLog) {
                // --- LOGIKA TAP KELUAR ---
                // Jika ada log masuk yang belum keluar, update waktu_keluar
                $lastLog->update([
                    'waktu_keluar' => Carbon::now()
                ]);
                $this->info("Aksi: TAP KELUAR dicatat untuk " . $member->nama);
            } else {
                // --- LOGIKA TAP MASUK ---
                // Jika tidak ada log yang menggantung, buat data kunjungan baru
                VisitLog::create([
                    'member_id'   => $member->id,
                    'waktu_masuk' => Carbon::now(),
                    'waktu_keluar'=> null,
                ]);
                $this->info("Aksi: TAP MASUK dicatat untuk " . $member->nama);
            }

            // 3. Kirim perintah balik ke Firebase untuk aksi alat (Buka Gate/Indikator)
            $reference->update([
                'command' => 'open',
                'last_name' => $member->nama,
                'status_msg' => 'Akses Diterima'
            ]);

        } else {
            // Member tidak terdaftar di MySQL
            $this->error("Akses Ditolak: UID " . $uidBersih . " tidak terdaftar.");
            $reference->update([
                'command' => 'reject',
                'status_msg' => 'Kartu Tidak Terdaftar'
            ]);
        }
        $this->info("------------------------------------------------");
    }
}