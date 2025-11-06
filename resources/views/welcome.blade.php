<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard Buku Tamu Perpustakaan</title>

    <!-- Font & Icon -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

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
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial;
            background: var(--bg);
            color: var(--text);
            margin: 0;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .container {
            max-width: 1184px;
            margin: 0 auto;
            padding: 20px
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--card);
            padding: 18px 20px;
            border: 1px solid var(--border);
            border-radius: 12px
        }

        .title {
            font-weight: 700;
            font-size: 26px
        }

        .controls {
            display: flex;
            gap: 12px;
            align-items: center
        }

        .control {
            display: flex;
            gap: 8px;
            align-items: center;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 10px 12px;
            color: #111827
        }

        .muted {
            color: var(--muted)
        }

        .tabs {
            display: flex;
            gap: 16px;
            background: var(--card);
            border: 1px solid var(--border);
            padding: 6px;
            border-radius: 10px
        }

        .tab {
            padding: 8px 14px;
            border-radius: 8px;
            color: var(--muted);
            font-weight: 600;
            cursor: pointer
        }

        .tab.active {
            color: var(--primary);
            background: var(--primary-50)
        }

        .alert {
            display: flex;
            gap: 10px;
            align-items: center;
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px 14px;
            margin-top: 16px
        }

        .grid-kpi {
            margin-top: 16px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px
        }

        .kpi .label {
            color: var(--muted);
            font-weight: 600
        }

        .kpi .value {
            font-size: 34px;
            font-weight: 800;
            margin-top: 8px
        }

        .kpi .delta {
            margin-top: 8px;
            font-size: 13px;
            color: #10b981;
            font-weight: 600
        }

        .grid-main {
            margin-top: 16px;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 16px
        }

        .chart-title {
            font-weight: 700;
            margin-bottom: 6px
        }

        .subtle {
            font-size: 12px;
            color: var(--muted)
        }

        .right-col {
            display: grid;
            grid-template-rows: auto auto;
            gap: 16px
        }

        .list-simple {
            width: 100%;
            border-collapse: collapse
        }

        .list-simple th,
        .list-simple td {
            padding: 10px 8px;
            border-bottom: 1px solid var(--border);
            font-size: 14px
        }

        .list-simple th {
            text-align: left;
            color: var(--muted);
            font-weight: 600
        }

        .badge {
            display: inline-block;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 700
        }

        .badge.success {
            background: #e6f9f3;
            color: #047857
        }

        .badge.danger {
            background: #fee2e2;
            color: #b91c1c
        }

        .row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px
        }

        .card-toolbar {
            display: flex;
            gap: 8px;
            align-items: center
        }

        .input {
            border: 1px solid var(--border);
            padding: 8px 10px;
            border-radius: 10px;
            width: 240px;
            background: #fff
        }

        .btn {
            border: 1px solid var(--border);
            padding: 8px 12px;
            border-radius: 10px;
            background: #fff;
            cursor: pointer;
            font-weight: 600
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden
        }

        .table th,
        .table td {
            padding: 14px 12px;
            border-bottom: 1px solid var(--border);
            font-size: 14px
        }

        .table thead th {
            background: #f8fafc;
            color: var(--muted);
            text-align: left
        }

        .status-ok {
            color: #16a34a;
            font-weight: 700
        }

        .chart-fixed {
            height: 300px;
            max-height: 300px;
            width: 100%
        }

        .donut-fixed {
            height: 200px;
            max-height: 200px;
            width: 100%
        }

        @media (max-width:1000px) {
            .grid-kpi {
                grid-template-columns: repeat(2, 1fr)
            }

            .grid-main {
                grid-template-columns: 1fr
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- TOP BAR -->
        <div class="topbar">
            <div class="title">Dashboard</div>
            <div class="controls">
                <div class="control"><i data-feather="info"></i><span class="muted">25/04/2025 – 30/04/2025</span></div>
                <div class="control"><span class="muted">Filter</span><i data-feather="chevron-down"></i></div>
                <div class="tabs">
                    <div class="tab active">Ringkas</div>
                    <div class="tab">Data Pencatatan</div>
                    <div class="tab">Log</div>
                </div>
            </div>
        </div>

        <!-- ALERT -->
        <div class="alert">
            <i data-feather="alert-circle" class="muted"></i>
            <div class="muted">Sistem dalam keadaan normal dan beroperasi tanpa adanya masalah.</div>
        </div>

        <!-- KPI CARDS -->
        <div class="grid-kpi">
            <div class="card kpi">
                <div class="label">Hari ini</div>
                <div class="value">102</div>
                <div class="delta">+ 20,5 %</div>
            </div>
            <div class="card kpi">
                <div class="label">Kemarin</div>
                <div class="value">80</div>
            </div>
            <div class="card kpi">
                <div class="label">Minggu ini</div>
                <div class="value">560</div>
            </div>
            <div class="card kpi">
                <div class="label">Bulan ini</div>
                <div class="value">2.430</div>
            </div>
        </div>

        <!-- MAIN GRID -->
        <div class="grid-main">
            <!-- Grafik 7 Hari -->
            <div class="card">
                <div class="chart-title">Kunjungan 7 Hari Terakhir</div>
                <div class="chart-fixed"><canvas id="lineChart"></canvas></div>
                <div class="row" style="margin-top:12px">
                    <div class="subtle">Perbandingan Harian</div>
                    <div class="delta">+20,5 %</div>
                </div>
            </div>

            <!-- Kolom Kanan -->
            <div class="right-col">
                <div class="card">
                    <div class="chart-title">Kategori Pengunjung</div>
                    <div class="donut-fixed"><canvas id="donutChart"></canvas></div>
                    <div class="row" style="margin-top:8px">
                        <div class="subtle"><span
                                style="display:inline-block;width:10px;height:10px;background:#60a5fa;border-radius:999px;margin-right:6px"></span>Mahasiswa
                        </div>
                        <div class="subtle"><span
                                style="display:inline-block;width:10px;height:10px;background:#1d4ed8;border-radius:999px;margin-right:6px"></span>Dosen
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="chart-title">Kunjungan Terbaru</div>
                    <table class="list-simple">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>NIM</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Rahmat Santoso</td>
                                <td>21101</td>
                                <td><span class="status-ok">Masuk</span></td>
                            </tr>
                            <tr>
                                <td>Siti Permata</td>
                                <td>21102</td>
                                <td><span class="status-ok">Masuk</span></td>
                            </tr>
                            <tr>
                                <td>Agus Wijaya</td>
                                <td>21103</td>
                                <td><span class="status-ok">Masuk</span></td>
                            </tr>
                            <tr>
                                <td>Lina Cahya</td>
                                <td>21104</td>
                                <td><span class="status-ok">Masuk</span></td>
                            </tr>
                            <tr>
                                <td>Bayu Kurnia</td>
                                <td>21105</td>
                                <td><span class="badge danger">Keluar</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tabel Bawah -->
        <div class="card" style="margin-top:16px">
            <div class="row" style="margin-bottom:10px">
                <div class="chart-title">Kunjungan</div>
                <div class="card-toolbar">
                    <div class="row" style="gap:8px">
                        <div class="input" style="display:flex;align-items:center;gap:8px">
                            <i data-feather="search" class="muted"></i>
                            <input id="search" placeholder="Cari"
                                style="border:none;outline:none;width:180px;background:transparent">
                        </div>
                        <button class="btn">Ekspor</button>
                    </div>
                </div>
            </div>
            <table class="table" id="table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>NIM</th>
                        <th>Waktu Kunjungan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Rahmat Santoso</td>
                        <td>211001</td>
                        <td>30/04/2025 08:36</td>
                        <td class="status-ok">Masuk</td>
                    </tr>
                    <tr>
                        <td>Siti Permata</td>
                        <td>211002</td>
                        <td>30/04/2025 08:20</td>
                        <td class="status-ok">Masuk</td>
                    </tr>
                    <tr>
                        <td>Agus Wijaya</td>
                        <td>211003</td>
                        <td>30/04/2025 08:15</td>
                        <td class="status-ok">Masuk</td>
                    </tr>
                    <tr>
                        <td>Lina Cahya</td>
                        <td>211004</td>
                        <td>30/04/2025 08:09</td>
                        <td class="status-ok">Masuk</td>
                    </tr>
                    <tr>
                        <td>Bayu Kurnia</td>
                        <td>211005</td>
                        <td>30/04/2025 08:03</td>
                        <td><span class="badge danger">Keluar</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        feather.replace();

        // Grafik Line
        const lineCtx = document.getElementById('lineChart');
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: ['25 Apr', '26 Apr', '27 Apr', '28 Apr', '29 Apr', '30 Apr'],
                datasets: [{
                    label: 'Total',
                    data: [45, 46, 56, 52, 64, 66],
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37,99,235,0.08)',
                    pointBackgroundColor: '#2563eb',
                    tension: 0.35,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#6b7280' } },
                    y: { grid: { color: '#eef2f7' }, ticks: { color: '#6b7280' } }
                },
                plugins: { legend: { display: false } }
            }
        });

        // Donut Chart
        const donutCtx = document.getElementById('donutChart');
        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: ['Mahasiswa', 'Dosen'],
                datasets: [{
                    data: [75, 25],
                    backgroundColor: ['#60a5fa', '#1d4ed8'],
                    borderWidth: 0
                }]
            },
            options: { cutout: '62%', maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });

        // Filter tabel
        const search = document.getElementById('search');
        const table = document.getElementById('table').getElementsByTagName('tbody')[0];
        search.addEventListener('input', () => {
            const q = search.value.toLowerCase();
            for (const tr of table.rows) {
                tr.style.display = tr.innerText.toLowerCase().includes(q) ? '' : 'none';
            }
        });
    </script>
</body>

</html>