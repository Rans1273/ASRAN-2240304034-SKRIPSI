<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use App\Models\Member; // Panggil Model Member

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
                $member = \App\Models\Member::where('uid', $uid_bersih)->first();

                if ($member) {
                    $this->info("4. Hasil Pencarian DB      : DITEMUKAN ✅");
                    $this->info("   - Nama: $member->nama");
                    $this->info("   - Status: $member->status");

                    if ($member->status == 'aktif') {
                        $this->info(">> KEPUTUSAN: BUKA");
                        $mqtt->publish($topic_perintah, 'BUKA', 0);
                    } else {
                        $this->warn(">> KEPUTUSAN: DITOLAK (Status Blokir)");
                        $mqtt->publish($topic_perintah, 'TOLAK', 0);
                    }
                } else {
                    $this->error("4. Hasil Pencarian DB      : TIDAK DITEMUKAN ❌");
                    $this->info(">> Saran: Cek apakah di database UID-nya sama persis?");
                    $this->info(">> KEPUTUSAN: DITOLAK");
                    $mqtt->publish($topic_perintah, 'TOLAK', 0);
                }
                $this->line("------------------------------------------------");

            }, 0);

            $mqtt->loop(true);

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}