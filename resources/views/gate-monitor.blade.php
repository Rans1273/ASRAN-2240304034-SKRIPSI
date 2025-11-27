<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Smart Gate UBT</title>
    
    <style>
        body { font-family: sans-serif; text-align: center; padding: 50px; background-color: #f4f4f4; }
        .card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 400px; margin: auto; }
        .status-box { 
            font-size: 24px; font-weight: bold; padding: 20px; margin-top: 20px; border-radius: 5px; color: white;
            background-color: #6c757d; /* Abu-abu default */
            transition: background-color 0.5s;
        }
        #log-area { margin-top: 20px; text-align: left; font-size: 12px; color: #555; height: 150px; overflow-y: scroll; border: 1px solid #ddd; padding: 10px; }
    </style>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.min.js" type="text/javascript"></script>
</head>
<body>

    <div class="card">
        <h2>Smart Gate Monitor</h2>
        <p>Status Pintu Saat Ini:</p>
        
        <div id="status-display" class="status-box">STANDBY</div>

        <div id="log-area">Menunggu koneksi...</div>
    </div>

    <script>
        // ==========================================
        // KONFIGURASI MQTT WEBSOCKET
        // ==========================================
        const broker = "10.74.70.59";
        const port = 9001; // PORT KHUSUS WEBSOCKET (Bukan 1883)
        const topic_perintah = "ubt/perpustakaan/gate/perintah"; // Mendengar keputusan server (BUKA/TOLAK)
        const topic_lapor = "ubt/perpustakaan/gate/lapor"; // Mendengar laporan kartu masuk

        // Buat ID Client unik agar tidak bentrok
        const clientID = "Web-Monitor-" + parseInt(Math.random() * 100000);

        // Inisialisasi Client Paho
        const client = new Paho.MQTT.Client(broker, port, clientID);

        // Handler ketika koneksi terputus
        client.onConnectionLost = function (responseObject) {
            console.log("Koneksi Putus: " + responseObject.errorMessage);
            log("Koneksi terputus... refresh halaman.");
        };

        // Handler ketika pesan masuk
        client.onMessageArrived = function (message) {
            console.log("Pesan masuk di topik: " + message.destinationName);
            console.log("Isi: " + message.payloadString);
            
            // Log semua pesan
            if (message.destinationName === topic_lapor) {
                log("📡 Scan Kartu UID: " + message.payloadString);
                document.getElementById("status-display").innerText = "MEMVERIFIKASI...";
                document.getElementById("status-display").style.backgroundColor = "#ffc107"; // Kuning
            }

            // Update UI jika ada perintah BUKA/TOLAK
            if (message.destinationName === topic_perintah) {
                updateUI(message.payloadString);
            }
        };

        // Fungsi Koneksi
        function connectMQTT() {
            log("Menghubungkan ke Broker...");
            client.connect({
                onSuccess: function () {
                    log("✅ Terhubung ke MQTT!");
                    
                    // Subscribe ke topik yang diperlukan
                    client.subscribe(topic_perintah);
                    client.subscribe(topic_lapor);
                },
                onFailure: function (message) {
                    log("❌ Gagal Konek: " + message.errorMessage);
                }
            });
        }

        // Fungsi Update UI Visual
        function updateUI(perintah) {
            const box = document.getElementById("status-display");
            
            if (perintah === "BUKA") {
                box.innerText = "SILAKAN MASUK";
                box.style.backgroundColor = "#28a745"; // Hijau
                
                // Balik ke standby setelah 4 detik
                setTimeout(() => {
                    box.innerText = "STANDBY";
                    box.style.backgroundColor = "#6c757d"; 
                }, 4000);
            } 
            else if (perintah === "TOLAK") {
                box.innerText = "AKSES DITOLAK";
                box.style.backgroundColor = "#dc3545"; // Merah
                
                setTimeout(() => {
                    box.innerText = "STANDBY";
                    box.style.backgroundColor = "#6c757d"; 
                }, 2000);
            }
        }

        // Fungsi Helper Log
        function log(msg) {
            const logDiv = document.getElementById("log-area");
            const time = new Date().toLocaleTimeString();
            logDiv.innerHTML = `[${time}] ${msg}<br>` + logDiv.innerHTML;
        }

        // Jalankan koneksi saat halaman dimuat
        connectMQTT();

    </script>
</body>
</html>