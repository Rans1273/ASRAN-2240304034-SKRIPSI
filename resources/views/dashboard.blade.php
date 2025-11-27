@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-center text-gray-600 shadow-sm">
        <i class="fas fa-exclamation-circle text-gray-400 mr-3 text-lg"></i>
        <span class="text-sm">Sistem dalam keadaan normal dan beroperasi tanpa adanya masalah.</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
            <p class="text-gray-500 text-sm font-medium">Hari ini</p>
            <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($todayCount) }}</h3>
            <p class="{{ $percentage >= 0 ? 'text-green-500' : 'text-red-500' }} text-xs font-bold mt-1">
                {{ $percentage >= 0 ? '+' : '' }} {{ $percentage }} %
            </p>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
            <p class="text-gray-500 text-sm font-medium">Kemarin</p>
            <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($yesterdayCount) }}</h3>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
            <p class="text-gray-500 text-sm font-medium">Minggu ini</p>
            <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($weekCount) }}</h3>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
            <p class="text-gray-500 text-sm font-medium">Bulan ini</p>
            <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($monthCount) }}</h3>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h4 class="font-bold text-gray-800 mb-4">Kunjungan 7 Hari Terakhir</h4>
            <canvas id="visitorsChart" height="150"></canvas>
            <div class="mt-4 text-right">
                 <span class="text-gray-500 text-xs">Perbandingan Harian</span>
                 <span class="text-green-600 font-bold text-sm ml-2">+{{ $percentage }} %</span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h4 class="font-bold text-gray-800 mb-4">Kategori Pengunjung</h4>
            <div class="relative" style="height: 200px;">
                <canvas id="categoryChart"></canvas>
            </div>
            <div class="flex justify-between mt-4 text-xs text-gray-500 px-4">
                <span class="flex items-center"><span class="w-2 h-2 rounded-full bg-blue-500 mr-2"></span> Mahasiswa</span>
                <span class="flex items-center"><span class="w-2 h-2 rounded-full bg-blue-800 mr-2"></span> Dosen</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-3 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-4">
                <h4 class="font-bold text-gray-800">Kunjungan Terbaru</h4>
                <a href="{{ route('visitors.logs') }}" class="px-3 py-1.5 border rounded-lg text-sm font-medium hover:bg-gray-50 text-gray-600">Lihat Semua</a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="bg-gray-50 text-gray-700 font-medium">
                        <tr>
                            <th class="px-4 py-3 rounded-tl-lg">Nama</th>
                            <th class="px-4 py-3">NIM / NIP</th>
                            <th class="px-4 py-3">Waktu Kunjungan</th>
                            <th class="px-4 py-3 rounded-tr-lg">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($latestVisits as $visit)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $visit->member->nama }}</td>
                            <td class="px-4 py-3">{{ $visit->member->nim_nip }}</td>
                            <td class="px-4 py-3">{{ $visit->waktu_masuk->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3">
                                @if($visit->status == 'Masuk' && $visit->waktu_keluar == null)
                                    <span class="text-green-600 font-bold">Masuk</span>
                                @else
                                    <span class="bg-red-100 text-red-600 px-2 py-1 rounded text-xs font-bold">Keluar</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-gray-400">Belum ada data kunjungan hari ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    // Konfigurasi Line Chart (Data Dinamis dari Controller)
    const ctxVisits = document.getElementById('visitorsChart').getContext('2d');
    new Chart(ctxVisits, {
        type: 'line',
        data: {
            labels: @json($labels), // Array tanggal dari Controller
            datasets: [{
                label: 'Kunjungan',
                data: @json($dataVisits), // Array jumlah dari Controller
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointBackgroundColor: '#3B82F6'
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [2, 2] }, ticks: { stepSize: 1 } },
                x: { grid: { display: false } }
            }
        }
    });

    // Konfigurasi Donut Chart (Data Dinamis)
    const ctxCategory = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctxCategory, {
        type: 'doughnut',
        data: {
            labels: ['Mahasiswa', 'Dosen'],
            datasets: [{
                data: [{{ $mhsCount }}, {{ $dosenCount }}], // Data dari variable blade
                backgroundColor: ['#3B82F6', '#1E40AF'],
                borderWidth: 0
            }]
        },
        options: {
            cutout: '70%',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });
</script>
@endsection