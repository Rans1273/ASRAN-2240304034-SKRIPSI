<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Member;
use App\Models\VisitLog;
use Carbon\Carbon;
use Kreait\Firebase\Contract\Database;
use Google\Cloud\Firestore\FirestoreClient;

class FirebaseGateListener extends Command
{
    protected $signature = 'gate:firebase-listen';
    protected $description = 'Memantau Firebase Realtime Database untuk aksi Smart Gate dan mencatat kunjungan';

    protected $database;
    protected $firestore;

    public function __construct(Database $database)
    {
        parent::__construct();
        $this->database = $database;

        $this->firestore = new FirestoreClient([
            'projectId' => env('FIREBASE_PROJECT_ID'),
        ]);
    }

    public function handle()
    {
        $this->info("✅ Menghubungkan ke Firebase. Menunggu kartu ditap...");

        try {
            $reference = $this->database->getReference('gate_system');
        } catch (\Exception $e) {
            $this->error("Gagal terhubung ke Firebase: " . $e->getMessage());
            return self::FAILURE;
        }

        while (true) {
            try {
                $snapshot = $reference->getValue();
                $lastUid = $snapshot['last_uid'] ?? null;
                $command = $snapshot['command'] ?? 'done';

                if ($command === 'waiting' && !empty($lastUid)) {
                    $this->processGate($reference, $lastUid);
                }
            } catch (\Exception $e) {
                $this->error("Error saat memantau Firebase: " . $e->getMessage());
            }

            sleep(1);
        }
    }

    private function processGate($reference, $uid)
    {
        $uidBersih = strtoupper(trim($uid));
        $this->info("------------------------------------------------");
        $this->info("RFID Terdeteksi (UID hanya untuk pencarian): $uidBersih");
        
        // 🔹 UID hanya untuk cari member
        $member = Member::where('uid', $uidBersih)->first();

        if ($member) {

            if ($member->status !== 'aktif') {
                $this->error("Akses Ditolak: Member " . $member->nama . " tidak aktif.");
                $reference->update([
                    'command' => 'reject',
                    'status_msg' => 'Member Tidak Aktif'
                ]);
                return;
            }

            // 🔥 WAJIB: ambil npm_nip dari database
            $npm_nip = $member->npm_nip;

            // ❌ Jika kosong → STOP (tidak boleh pakai UID)
            if (empty($npm_nip)) {
                $this->error("Data npm_nip kosong! Tidak dikirim ke Firestore.");
                return;
            }

            $this->info("Member: {$member->nama} | NPM/NIP: {$npm_nip}");

            // 🔹 Cek log terakhir
            $lastLog = VisitLog::where('member_id', $member->id)
                ->whereNull('waktu_keluar')
                ->latest('waktu_masuk')
                ->first();

            if ($lastLog) {

                // =========================
                // 🔴 TAP KELUAR
                // =========================
                $lastLog->update([
                    'waktu_keluar' => Carbon::now()
                ]);

                $this->info("Aksi: TAP KELUAR");

                // 🔥 HAPUS dari Firestore (pakai npm_nip)
                $this->firestore->collection('pengunjung')
                    ->document($npm_nip)
                    ->delete();

            } else {

                // =========================
                // 🟢 TAP MASUK
                // =========================
                VisitLog::create([
                    'member_id'   => $member->id,
                    'waktu_masuk' => Carbon::now(),
                    'waktu_keluar'=> null,
                ]);

                $this->info("Aksi: TAP MASUK");

                // 🔥 SIMPAN ke Firestore (HANYA npm_nip)
                $this->firestore->collection('pengunjung')
                    ->document($npm_nip)
                    ->set([
                        'npm_nip' => $npm_nip
                    ]);
            }

            // 🔹 Respon ke ESP32 (tetap)
            $reference->update([
                'command' => 'open',
                'last_name' => $member->nama,
                'status_msg' => 'Akses Diterima'
            ]);

        } else {

            $this->error("Akses Ditolak: UID tidak terdaftar.");
            $reference->update([
                'command' => 'reject',
                'status_msg' => 'Kartu Tidak Terdaftar'
            ]);
        }

        $this->info("------------------------------------------------");
    }
}