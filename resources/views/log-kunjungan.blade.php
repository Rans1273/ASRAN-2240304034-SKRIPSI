<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Kunjungan — Perpustakaan UBT</title>

    <!-- Font & Library -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
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
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial
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
            padding: 16px 18px
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
            padding: 14px
        }

        .input {
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 10px 12px;
            background: #fff;
            color: #111827
        }

        .btn {
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 10px 12px;
            background: #fff;
            cursor: pointer;
            font-weight: 700
        }

        .btn.primary {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
            margin-top: 16px
        }

        .chart-fixed {
            height: 320px;
            max-height: 320px;
            width: 100%
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px
        }

        thead th {
            background: #f8fafc;
            color: var(--muted);
            text-align: left;
            font-weight: 700;
            border-bottom: 1px solid var(--border);
            padding: 12px
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
            font-weight: 700
        }

        .badge.blue {
            background: var(--primary-50);
            color: #1d4ed8
        }

        .footer {
            margin: 22px 0 8px;
            color: var(--muted);
            font-size: 12px;
            text-align: center
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- HEADER -->
        <div class="topbar">
            <div>
                <h1>Log Kunjungan / Rekapitulasi</h1>
                <div class="muted">Riwayat kunjungan perpustakaan UBT berdasarkan tanggal</div>
            </div>
        </div>

        <!-- FILTER -->
        <div class="toolbar">
            <div>
                <label>Dari:</label>
                <input type="date" id="from" class="input">
                <label>Sampai:</label>
                <input type="date" id="to" class="input">
            </div>
            <div style="margin-left:auto;display:flex;gap:10px;flex-wrap:wrap">
                <button class="btn" id="btnExcel">Export Excel</button>
                <button class="btn primary" id="btnPDF">Download PDF</button>
            </div>
        </div>

        <!-- GRAFIK TREN -->
        <div class="card">
            <h3 style="margin-top:0">Grafik Tren Kunjungan 7 Hari Terakhir</h3>
            <div class="chart-fixed">
                <canvas id="chartKunjungan"></canvas>
            </div>
        </div>

        <!-- TABEL REKAP -->
        <div class="card">
            <h3 style="margin-top:0">Rekap Data Kunjungan</h3>
            <table id="tbl">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Total Pengunjung</th>
                        <th>Mahasiswa</th>
                        <th>Dosen</th>
                    </tr>
                </thead>
                <tbody id="tbody"></tbody>
            </table>
        </div>

        <div class="footer">© 2025 Universitas Borneo Tarakan • Sistem Buku Tamu Perpustakaan (Smart Gate RFID)</div>
    </div>

    <script>
        const data = [
            { tanggal: '2025-04-25', total: 42, mahasiswa: 32, dosen: 10 },
            { tanggal: '2025-04-26', total: 47, mahasiswa: 37, dosen: 10 },
            { tanggal: '2025-04-27', total: 52, mahasiswa: 40, dosen: 12 },
            { tanggal: '2025-04-28', total: 63, mahasiswa: 51, dosen: 12 },
            { tanggal: '2025-04-29', total: 70, mahasiswa: 55, dosen: 15 },
            { tanggal: '2025-04-30', total: 66, mahasiswa: 54, dosen: 12 },
            { tanggal: '2025-05-01', total: 73, mahasiswa: 58, dosen: 15 }
        ];

        const tbody = document.getElementById('tbody');
        const render = (rows) => {
            tbody.innerHTML = '';
            rows.forEach(r => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
          <td>${new Date(r.tanggal).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })}</td>
          <td><b>${r.total}</b></td>
          <td><span class="badge blue">${r.mahasiswa}</span></td>
          <td><span class="badge" style="background:#e6f9f3;color:#047857">${r.dosen}</span></td>
        `;
                tbody.appendChild(tr);
            });
        };
        render(data);

        // GRAFIK
        const ctx = document.getElementById('chartKunjungan');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(r => new Date(r.tanggal).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' })),
                datasets: [{
                    label: 'Total Pengunjung',
                    data: data.map(r => r.total),
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37,99,235,0.08)',
                    pointBackgroundColor: '#2563eb',
                    fill: true,
                    tension: 0.35
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#6b7280' } },
                    y: { grid: { color: '#eef2f7' }, ticks: { color: '#6b7280' } }
                },
                plugins: { legend: { display: false } }
            }
        });

        // FILTER
        const from = document.getElementById('from');
        const to = document.getElementById('to');
        const labelPeriode = document.getElementById('labelPeriode');
        function applyFilter() {
            let rows = [...data];
            const f = from.value ? new Date(from.value) : null;
            const t = to.value ? new Date(to.value) : null;
            if (f) rows = rows.filter(r => new Date(r.tanggal) >= f);
            if (t) rows = rows.filter(r => new Date(r.tanggal) <= t);
            render(rows);
            labelPeriode.textContent = (from.value || to.value) ? `${from.value || 'awal'} — ${to.value || 'akhir'}` : 'Seluruh Periode';
        }
        from.addEventListener('input', applyFilter);
        to.addEventListener('input', applyFilter);

        // EXPORT EXCEL
        document.getElementById('btnExcel').addEventListener('click', () => {
            const rows = Array.from(tbody.querySelectorAll('tr')).map(tr =>
                Array.from(tr.children).map(td => td.innerText)
            );
            const ws = XLSX.utils.aoa_to_sheet([['Tanggal', 'Total', 'Mahasiswa', 'Dosen'], ...rows]);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Rekap');
            XLSX.writeFile(wb, `rekap-kunjungan_${new Date().toISOString().slice(0, 10)}.xlsx`);
        });

        // EXPORT PDF
        document.getElementById('btnPDF').addEventListener('click', () => {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'pt');
            doc.setFont('helvetica', 'bold'); doc.setFontSize(14);
            doc.text('Rekap Kunjungan Perpustakaan UBT', 40, 40);
            doc.setFont('helvetica', 'normal'); doc.setFontSize(10);
            doc.text(`Dibuat: ${new Date().toLocaleString('id-ID')}`, 40, 58);

            const head = [['Tanggal', 'Total', 'Mahasiswa', 'Dosen']];
            const body = Array.from(tbody.querySelectorAll('tr')).map(tr => Array.from(tr.children).map(td => td.innerText));

            doc.autoTable({ head, body, startY: 80, styles: { fontSize: 9 }, headStyles: { fillColor: [37, 99, 235] } });
            doc.save(`rekap-kunjungan_${new Date().toISOString().slice(0, 10)}.pdf`);
        });
    </script>
</body>

</html>