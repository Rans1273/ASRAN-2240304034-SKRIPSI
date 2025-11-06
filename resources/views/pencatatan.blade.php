<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Pengunjung Aktif — Perpustakaan UBT</title>

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- SheetJS (Excel) & jsPDF (PDF) -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.19.3/dist/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js"></script>

    <style>
        :root {
            --bg: #f7f9fc;
            --card: #fff;
            --text: #0f172a;
            --muted: #6b7280;
            --border: #e5e7eb;
            --primary: #2563eb;
            --primary-50: #eff6ff;
            --success: #059669;
            --danger: #ef4444;
        }

        * {
            box-sizing: border-box
        }

        html,
        body {
            height: 100%
        }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial;
        }

        .container {
            max-width: 1184px;
            margin: 0 auto;
            padding: 20px
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px 18px;
        }

        h1 {
            font-size: 22px;
            margin: 0;
            font-weight: 800
        }

        .muted {
            color: var(--muted)
        }

        .toolbar {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 16px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 14px;
        }

        .group {
            display: flex;
            gap: 8px;
            align-items: center
        }

        .input,
        select {
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 10px 12px;
            background: #fff;
            color: #111827;
        }

        .btn {
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 10px 12px;
            background: #fff;
            cursor: pointer;
            font-weight: 700;
        }

        .btn.primary {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
            margin-top: 16px;
        }

        .card-title {
            font-weight: 800;
            margin: 0 0 10px 0
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        thead th {
            background: #f8fafc;
            color: var(--muted);
            text-align: left;
            font-weight: 700;
            border-bottom: 1px solid var(--border);
            padding: 12px;
        }

        tbody td {
            border-bottom: 1px solid var(--border);
            padding: 12px
        }

        .badge {
            display: inline-block;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 700;
        }

        .badge.blue {
            background: var(--primary-50);
            color: #1d4ed8
        }

        .badge.green {
            background: #e6f9f3;
            color: #047857
        }

        .chip {
            display: inline-flex;
            gap: 8px;
            align-items: center;
            border: 1px solid var(--border);
            padding: 6px 10px;
            border-radius: 999px;
            background: #fff;
        }

        .hint {
            font-size: 12px;
            color: var(--muted)
        }

        .footer {
            margin: 22px 0 8px;
            color: var(--muted);
            font-size: 12px;
            text-align: center
        }

        @media (max-width:720px) {
            .toolbar {
                flex-direction: column;
                align-items: flex-start
            }

            .input {
                width: 100%
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- HEADER -->
        <div class="topbar">
            <div>
                <h1>Pengunjung Aktif (Sedang di Perpustakaan)</h1>
                <div class="muted">Menampilkan daftar siapa saja yang masih berada di dalam berdasarkan log Masuk/Keluar
                    RFID</div>
            </div>
            <div class="chip">
                <span class="hint">Diperbarui:</span>
                <strong id="lastUpdate">—</strong>
            </div>
        </div>

        <!-- Toolbar: Filter & Ekspor -->
        <div class="toolbar">
            <div class="group">
                <label class="hint">Dari</label>
                <input type="date" id="from" class="input">
                <label class="hint">Sampai</label>
                <input type="date" id="to" class="input">
                <button class="btn" id="btnReset">Reset</button>
            </div>
            <div class="group" style="margin-left:auto">
                <input type="text" id="search" class="input" placeholder="Cari nama…">
                <select id="kategori" class="input">
                    <option value="">Semua Kategori</option>
                    <option value="Mahasiswa">Mahasiswa</option>
                    <option value="Dosen">Dosen</option>
                </select>
                <button class="btn" id="btnExcel">Export Excel</button>
                <button class="btn primary" id="btnPDF">Export PDF</button>
            </div>
        </div>

        <!-- Tabel Pengunjung Aktif -->
        <div class="card">
            <h3 class="card-title">Daftar Pengunjung Saat Ini</h3>
            <table id="tbl">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>NIM</th>
                        <th>Waktu Masuk Terakhir</th>
                        <th>Kategori</th>
                    </tr>
                </thead>
                <tbody id="tbody"></tbody>
            </table>
            <div class="hint" style="margin-top:10px">
                * Hanya menampilkan pengunjung dengan <b>status terakhir = Masuk</b>. Saat mereka keluar, data otomatis
                tidak tampil.
            </div>
        </div>

        <div class="footer">© 2025 UBT • Smart Gate Perpustakaan</div>
    </div>

    <script>
        // =========================
        // LOG PERISTIWA RFID
        // =========================
        const events = [
            { nama: 'User 1', nim: '211001', waktu: '2025-04-30T08:36:00', kategori: 'Mahasiswa', status: 'Masuk' },
            { nama: 'User 2', nim: '211002', waktu: '2025-04-30T08:20:00', kategori: 'Mahasiswa', status: 'Masuk' },
            { nama: 'User 3', nim: 'D-11001', waktu: '2025-04-30T08:15:00', kategori: 'Dosen', status: 'Masuk' },
            { nama: 'User 2', nim: '211002', waktu: '2025-04-30T10:05:00', kategori: 'Mahasiswa', status: 'Keluar' },
            { nama: 'User 4', nim: '211004', waktu: '2025-04-29T13:09:00', kategori: 'Mahasiswa', status: 'Masuk' },
            { nama: 'User 5', nim: 'D-11002', waktu: '2025-04-29T12:45:00', kategori: 'Dosen', status: 'Keluar' },
            { nama: 'User 6', nim: '211006', waktu: '2025-04-28T09:12:00', kategori: 'Mahasiswa', status: 'Masuk' },
            { nama: 'User 7', nim: '211007', waktu: '2025-04-28T11:05:00', kategori: 'Mahasiswa', status: 'Keluar' },
        ];

        const tbody = document.getElementById('tbody');
        const from = document.getElementById('from');
        const to = document.getElementById('to');
        const search = document.getElementById('search');
        const kategori = document.getElementById('kategori');
        const lastUpdate = document.getElementById('lastUpdate');

        const fmtID = (d) =>
            new Date(d).toLocaleString('id-ID', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });

        // Ambil pengunjung yang masih di dalam (status terakhir = Masuk)
        function getActiveVisitors(allEvents) {
            const map = new Map();
            allEvents.forEach((ev) => {
                if (!map.has(ev.nim)) map.set(ev.nim, []);
                map.get(ev.nim).push(ev);
            });

            const active = [];
            for (const [nim, logs] of map.entries()) {
                logs.sort((a, b) => new Date(b.waktu) - new Date(a.waktu));
                const last = logs[0];
                if (last.status === 'Masuk') active.push(last);
            }
            active.sort((a, b) => new Date(b.waktu) - new Date(a.waktu));
            return active;
        }

        function render(rows) {
            tbody.innerHTML = '';
            rows.forEach((r) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
            <td>${r.nama}</td>
            <td>${r.nim}</td>
            <td>${fmtID(r.waktu)}</td>
            <td><span class="badge ${r.kategori === 'Mahasiswa' ? 'blue' : 'green'}">${r.kategori}</span></td>`;
                tbody.appendChild(tr);
            });
            lastUpdate.textContent = new Date().toLocaleString('id-ID');
        }

        function applyFilters() {
            let rows = getActiveVisitors(events);

            const f = from.value ? new Date(from.value + 'T00:00:00') : null;
            const t = to.value ? new Date(to.value + 'T23:59:59') : null;
            if (f) rows = rows.filter((r) => new Date(r.waktu) >= f);
            if (t) rows = rows.filter((r) => new Date(r.waktu) <= t);

            const q = search.value.trim().toLowerCase();
            if (q) rows = rows.filter((r) => r.nama.toLowerCase().includes(q));

            if (kategori.value) rows = rows.filter((r) => r.kategori === kategori.value);

            render(rows);
        }

        // Filter dan reset
        [from, to, search, kategori].forEach((el) => el.addEventListener('input', applyFilters));
        document.getElementById('btnReset').addEventListener('click', () => {
            from.value = '';
            to.value = '';
            search.value = '';
            kategori.value = '';
            applyFilters();
        });

        // Export Excel
        document.getElementById('btnExcel').addEventListener('click', () => {
            const rows = Array.from(tbody.querySelectorAll('tr')).map((tr) =>
                Array.from(tr.children).map((td) => td.innerText)
            );
            const header = ['Nama', 'NIM', 'Waktu Masuk Terakhir', 'Kategori'];
            const ws = XLSX.utils.aoa_to_sheet([header, ...rows]);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Pengunjung Aktif');
            XLSX.writeFile(wb, `pengunjung-aktif_${new Date().toISOString().slice(0, 10)}.xlsx`);
        });

        // Export PDF
        document.getElementById('btnPDF').addEventListener('click', () => {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'pt');
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(14);
            doc.text('Daftar Pengunjung Aktif Perpustakaan UBT', 40, 40);
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(10);
            doc.text(`Dibuat: ${new Date().toLocaleString('id-ID')}`, 40, 58);

            const head = [['Nama', 'NIM', 'Waktu Masuk Terakhir', 'Kategori']];
            const body = Array.from(tbody.querySelectorAll('tr')).map((tr) =>
                Array.from(tr.children).map((td) => td.innerText)
            );

            doc.autoTable({
                head,
                body,
                startY: 80,
                styles: { fontSize: 9 },
                headStyles: { fillColor: [37, 99, 235] },
            });
            doc.save(`pengunjung-aktif_${new Date().toISOString().slice(0, 10)}.pdf`);
        });

        // Init awal
        applyFilters();
    </script>
</body>

</html>