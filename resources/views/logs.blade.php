@extends('layouts.app')

@section('title', 'Log Kunjungan & Rekapitulasi')

@section('content')
    <div class="mb-6">
        <p class="text-gray-500 text-sm">Klik pada baris tanggal untuk melihat rincian detail pengunjung.</p>
    </div>

    {{-- Filter Tanggal --}}
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
        <form action="{{ route('visitors.logs') }}" method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="border rounded-lg px-3 py-2 text-sm bg-gray-50 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="border rounded-lg px-3 py-2 text-sm bg-gray-50 focus:ring-blue-500">
            </div>
            <button type="submit" class="bg-blue-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-blue-700 h-10">
                Filter Data
            </button>
            <a href="{{ route('visitors.logs') }}" class="text-gray-500 text-sm hover:underline py-2">Reset</a>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-100 text-gray-700 font-bold uppercase text-xs">
                <tr>
                    <th class="px-6 py-4">Tanggal</th>
                    <th class="px-6 py-4 text-center">Total Pengunjung</th>
                    <th class="px-6 py-4 text-center">Mahasiswa</th>
                    <th class="px-6 py-4 text-center">Dosen</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($groupedLogs as $date => $logs)
                    @php
                        // Hitung statistik per hari
                        $total = $logs->count();
                        $mhs = $logs->where('member.kategori', 'Mahasiswa')->count();
                        $dosen = $logs->where('member.kategori', 'Dosen')->count();
                        $rowId = 'details-' . str_replace('-', '', $date); // ID unik untuk javascript
                    @endphp

                    {{-- BARIS 1: REKAPITULASI HARIAN (Bisa Diklik) --}}
                    <tr class="hover:bg-blue-50 cursor-pointer transition-colors border-l-4 border-transparent hover:border-blue-500" 
                        onclick="toggleDetails('{{ $rowId }}')">
                        
                        <td class="px-6 py-4 font-bold text-gray-800">
                            <i class="fas fa-calendar-day mr-2 text-blue-500"></i>
                            {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="bg-gray-200 text-gray-800 py-1 px-3 rounded-full font-bold text-xs">{{ $total }}</span>
                        </td>
                        <td class="px-6 py-4 text-center text-blue-600 font-medium">{{ $mhs }}</td>
                        <td class="px-6 py-4 text-center text-green-600 font-medium">{{ $dosen }}</td>
                        <td class="px-6 py-4 text-right text-gray-400">
                            <i class="fas fa-chevron-down transition-transform" id="icon-{{ $rowId }}"></i>
                        </td>
                    </tr>

                    {{-- BARIS 2: TABEL RINCIAN (Tersembunyi Default) --}}
                    <tr id="{{ $rowId }}" class="hidden bg-gray-50">
                        <td colspan="5" class="p-4">
                            <div class="bg-white border rounded-lg shadow-inner overflow-hidden">
                                <div class="px-4 py-2 bg-blue-600 text-white text-xs font-bold uppercase tracking-wider">
                                    Rincian Kunjungan - {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                                </div>
                                <table class="w-full text-xs">
                                    <thead class="bg-gray-100 border-b">
                                        <tr>
                                            <th class="px-4 py-2 text-left">ID Log</th>
                                            <th class="px-4 py-2 text-left">Nama Lengkap</th>
                                            <th class="px-4 py-2 text-left">NIM / NIP</th>
                                            <th class="px-4 py-2 text-left">Kategori</th>
                                            <th class="px-4 py-2 text-center">Jam Masuk</th>
                                            <th class="px-4 py-2 text-center">Jam Keluar</th>
                                            <th class="px-4 py-2 text-center">Durasi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($logs as $log)
                                        <tr>
                                            <td class="px-4 py-2 text-gray-500">#{{ $log->id }}</td>
                                            <td class="px-4 py-2 font-medium text-gray-900">{{ $log->member->nama }}</td>
                                            <td class="px-4 py-2">{{ $log->member->nim_nip }}</td>
                                            <td class="px-4 py-2">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $log->member->kategori == 'Dosen' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                                                    {{ $log->member->kategori }}
                                                </span>
                                            </td>
                                            {{-- Format Jam Masuk --}}
                                            <td class="px-4 py-2 text-center font-mono text-blue-600">
                                                {{ $log->waktu_masuk->format('H:i:s') }}
                                            </td>
                                            {{-- Format Jam Keluar --}}
                                            <td class="px-4 py-2 text-center font-mono text-red-600">
                                                @if($log->waktu_keluar)
                                                    {{ $log->waktu_keluar->format('H:i:s') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-center text-gray-500">
                                                @if($log->waktu_keluar)
                                                    {{ $log->waktu_keluar->diffInMinutes($log->waktu_masuk) }} Menit
                                                @else
                                                    <span class="text-green-500 font-bold animate-pulse">Aktif</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 text-gray-400">
                            <i class="fas fa-inbox text-4xl mb-2 block"></i>
                            Belum ada data kunjungan pada rentang tanggal ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

@section('scripts')
<script>
    // Fungsi sederhana untuk Show/Hide rincian
    function toggleDetails(id) {
        const detailRow = document.getElementById(id);
        const icon = document.getElementById('icon-' + id);
        
        if (detailRow.classList.contains('hidden')) {
            detailRow.classList.remove('hidden');
            icon.classList.add('rotate-180'); // Putar ikon panah
        } else {
            detailRow.classList.add('hidden');
            icon.classList.remove('rotate-180'); // Balikkan ikon panah
        }
    }
</script>
@endsection