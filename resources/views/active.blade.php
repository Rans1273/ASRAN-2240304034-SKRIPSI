@extends('layouts.app')

@section('title', 'Pengunjung Aktif (Sedang di Perpustakaan)')

@section('content')
    <div class="flex justify-between items-center mb-2">
        <p class="text-gray-500 text-sm">Menampilkan daftar siapa saja yang masih berada di dalam berdasarkan log Masuk/Keluar RFID</p>
        <div class="bg-white border rounded-full px-4 py-1 text-xs font-semibold text-gray-700 shadow-sm">
            Diperbarui: <span class="font-bold">{{ now()->format('d/m/Y, H:i:s') }}</span>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6 flex flex-wrap gap-3 items-center">
        <div class="flex items-center border rounded-lg px-3 py-2 text-sm bg-gray-50">
             <span class="text-gray-400 mr-2">Tanggal</span>
             <span class="text-gray-600 font-medium">{{ now()->format('d/m/Y') }}</span>
        </div>
        <button onclick="window.location.reload()" class="text-sm font-bold text-gray-700 px-3 hover:text-blue-600">Refresh Data</button>
        
        <div class="flex-grow"></div> <input type="text" placeholder="Cari nama..." class="border rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 w-48">
        
        <select class="border rounded-lg px-3 py-2 text-sm text-gray-600 bg-white">
            <option>Semua Kategori</option>
            <option>Mahasiswa</option>
            <option>Dosen</option>
        </select>

        <button class="border border-gray-300 text-gray-700 rounded-lg px-4 py-2 text-sm font-medium hover:bg-gray-50">Export Excel</button>
        <button class="bg-blue-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-blue-700">Export PDF</button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="font-bold text-lg text-gray-800">Daftar Pengunjung Saat Ini</h3>
        </div>
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-gray-700 font-bold uppercase text-xs">
                <tr>
                    <th class="px-6 py-4">Nama</th>
                    <th class="px-6 py-4">NIM / NIP</th>
                    <th class="px-6 py-4">Waktu Masuk Terakhir</th>
                    <th class="px-6 py-4">Kategori</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($activeVisitors as $visitor)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $visitor->member->nama }}</td>
                    <td class="px-6 py-4">{{ $visitor->member->npm_nip }}</td>
                    <td class="px-6 py-4">{{ $visitor->waktu_masuk->format('d/m/Y, H.i') }}</td>
                    <td class="px-6 py-4">
                        @if($visitor->member->kategori == 'Dosen')
                            <span class="bg-green-100 text-green-600 py-1 px-3 rounded-full text-xs font-bold">Dosen</span>
                        @else
                            <span class="bg-blue-100 text-blue-600 py-1 px-3 rounded-full text-xs font-bold">{{ $visitor->member->kategori }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                        Tidak ada pengunjung yang sedang aktif saat ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 bg-gray-50 text-xs text-gray-500 border-t border-gray-100">
            * Hanya menampilkan pengunjung dengan status terakhir = Masuk. Saat mereka keluar, data otomatis hilang dari daftar ini.
        </div>
    </div>
@endsection