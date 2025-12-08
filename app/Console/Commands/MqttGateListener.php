<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use App\Models\Member; // Panggil Model Member
use App\Models\VisitLog; // Tambahkan Model VisitLog untuk pencatatan

class MqttGateListener extends Command
{
    // Nama perintah yang nanti diketik di terminal
    protected $signature = 'gate:listen';
    protected $description = 'Menjalankan service untuk mendengarkan sensor Smart Gate';

    public function handle()
    {
        $server   = '10.74.70.59';
        $port     = 1883;
        $clientId = 'Laravel-Debug-' . uniqid();

        $topic_lapor    = 'ubt/perpustakaan/gate/lapor';
        $topic_perintah = 'ubt/perpustakaan/gate/perintah';

        $this->info("Menghubungkan ke MQTT Broker...");

        try {
            $mqtt = new \PhpMqtt\Client\MqttClient($server, $port, $clientId);
            $settings = (new \PhpMqtt\Client\ConnectionSettings)->setKeepAliveInterval(60);
            $mqtt->connect($settings, true);
            
            $this->info("✅ Mode DEBUG Aktif. Menunggu kartu...");

            $mqtt->subscribe($topic_lapor, function ($topic, $message) use ($mqtt, $topic_perintah) {
                
                // 1. TANGKAP DATA MENTAH
                $raw_message = $message;
                
                // 2. BERSIHKAN DATA (Hapus Spasi Depan/Belakang & Enter)
                // Kita paksa ubah ke Huruf Besar juga biar aman
                $uid_bersih = strtoupper(trim($raw_message)); 
                
                // --- DEBUGGING AREA (LIHAT TERMINAL) ---
                $this->line("------------------------------------------------");
                $this->info("1. Data Mentah dari ESP32 : '$raw_message'");
                $this->info("2. Data Setelah Dibersihkan: '$uid_bersih'");
                $this->info("3. Panjang Karakter        : " . strlen($uid_bersih));
                
                // 3. CEK DATABASE
                // Pastikan nama kolom di database sesuai ('uid' atau 'rfid_uid')
                $member = Member::where('uid', $uid_bersih)->first();

                $keputusan = 'TOLAK'; // Default keputusan

                if ($member) {
                    $this->info("4. Hasil Pencarian DB      : DITEMUKAN ✅");
                    $this->info("   - Nama: $member->nama");
                    $this->info("   - Kategori: $member->kategori");
                    $this->info("   - Status: $member->status");

                    if ($member->status == 'aktif') {
                        $keputusan = 'BUKA';
                        
                        // PERBEDAAN UTAMA: LOGGING HANYA UNTUK NON-STAFF
                        if ($member->kategori !== 'staff') { //
                            $last_log = VisitLog::where('member_id', $member->id)
                                ->whereNull('waktu_keluar')
                                ->latest('waktu_masuk')
                                ->first();

                            if ($last_log) {
                                // Log ditemukan: Member sedang di dalam (Masuk), catat waktu keluar
                                $last_log->waktu_keluar = now();
                                $last_log->save();
                                $this->info(">> LOG: Waktu KELUAR dicatat untuk $member->nama.");
                            } else {
                                // Log tidak ditemukan: Member di luar, catat waktu masuk baru
                                VisitLog::create([
                                    'member_id' => $member->id,
                                    'waktu_masuk' => now(),
                                ]);
                                $this->info(">> LOG: Waktu MASUK dicatat untuk $member->nama.");
                            }
                        } else {
                            $this->warn(">> LOG: Pencatatan diabaikan karena Kategori: STAFF.");
                        }

                        $this->info(">> KEPUTUSAN GATE: BUKA");

                    } else {
                        $this->warn(">> KEPUTUSAN GATE: DITOLAK (Status Blokir)");
                        $keputusan = 'TOLAK';
                    }
                } else {
                    $this->error("4. Hasil Pencarian DB      : TIDAK DITEMUKAN ❌");
                    $this->info(">> KEPUTUSAN GATE: DITOLAK");
                }
                
                // Publish command ke MQTT
                $mqtt->publish($topic_perintah, $keputusan, 0);
                
                $this->line("------------------------------------------------");
            }, 0);

            $mqtt->loop(true);

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}