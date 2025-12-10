@extends('layouts.app')

@section('title', 'Pengunjung Aktif (Sedang di Perpustakaan)')

@section('content')
    <div class="flex justify-between items-center mb-2">
        <p class="text-gray-500 text-sm">Menampilkan daftar siapa saja yang masih berada di dalam berdasarkan log Masuk/Keluar RFID</p>
        <div class="bg-white border rounded-full px-4 py-1 text-xs font-semibold text-gray-700 shadow-sm">
            {{-- Menggunakan now() untuk waktu saat ini --}}
            Diperbarui: <span class="font-bold">{{ now()->format('d/m/Y, H:i:s') }}</span>
        </div>
    </div>

    {{-- Filter & Export Toolbar --}}
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6 flex flex-wrap gap-3 items-center">
        <form id="filterForm" method="GET" action="{{ route('visitors.active') }}" class="flex flex-wrap gap-3 items-center w-full">
            
            {{-- Tombol Refresh --}}
            <button type="button" onclick="window.location.href='{{ route('visitors.active') }}'" class="text-sm font-bold text-gray-700 px-3 hover:text-blue-600 focus:outline-none">
                Refresh Data
            </button>

            {{-- Today's Date Info --}}
            <div class="flex items-center border rounded-lg px-3 py-2 text-sm bg-gray-50">
                <span class="text-gray-400 mr-2">Tanggal</span>
                <span class="text-gray-600 font-medium">{{ now()->format('d/m/Y') }}</span>
            </div>
            
            <div class="flex-grow"></div> 
            
            {{-- Search Input. Auto submit on change/enter (handle by script/change event) --}}
            <input type="text" name="search" onchange="document.getElementById('filterForm').submit();"
                placeholder="Cari nama atau NPM/NIP..." 
                class="border rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 w-full sm:w-48"
                value="{{ request('search') }}">
            
            {{-- Category Filter Select --}}
            <select name="kategori" onchange="document.getElementById('filterForm').submit();"
                class="border rounded-lg px-3 py-2 text-sm text-gray-600 bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                <option value="">Semua Kategori</option>
                {{-- Menggunakan lowercase sesuai enum database: 'mahasiswa', 'dosen' --}}
                <option value="mahasiswa" {{ request('kategori') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                <option value="dosen" {{ request('kategori') == 'dosen' ? 'selected' : '' }}>Dosen</option>
            </select>
            
            {{-- Tombol Export Excel (Menggunakan tag A agar sesuai style button di logs) --}}
            {{-- Menyertakan semua query parameter saat ini + flag active_only=true --}}
            <a href="{{ route('visitors.export.excel', array_merge(request()->query(), ['active_only' => 'true'])) }}" 
                class="bg-green-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-green-700 h-10 flex items-center">
                <i class="fas fa-file-excel mr-2"></i>Excel
            </a>
            
            {{-- Tombol Export PDF --}}
            <a href="{{ route('visitors.export.pdf', array_merge(request()->query(), ['active_only' => 'true'])) }}" 
                class="bg-red-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-red-700 h-10 flex items-center">
                <i class="fas fa-file-pdf mr-2"></i> PDF
            </a>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="font-bold text-lg text-gray-800">Daftar Pengunjung Saat Ini</h3>
        </div>
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-gray-700 font-bold uppercase text-xs">
                <tr>
                    <th class="px-6 py-4">Nama</th>
                    <th class="px-6 py-4">NPM / NIP</th>
                    <th class="px-6 py-4">Waktu Masuk Terakhir</th>
                    <th class="px-6 py-4">Kategori</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($activeVisitors as $visitor)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $visitor->member->nama }}</td>
                    <td class="px-6 py-4">{{ $visitor->member->npm_nip }}</td>
                    <td class="px-6 py-4">{{ $visitor->waktu_masuk->format('d/m/Y, H:i:s') }}</td>
                    <td class="px-6 py-4">
                        @if(strtolower($visitor->member->kategori) == 'dosen')
                            <span class="bg-green-100 text-green-600 py-1 px-3 rounded-full text-xs font-bold">Dosen</span>
                        @elseif(strtolower($visitor->member->kategori) == 'mahasiswa')
                            <span class="bg-blue-100 text-blue-600 py-1 px-3 rounded-full text-xs font-bold">Mahasiswa</span>
                        @else
                            <span class="bg-gray-100 text-gray-600 py-1 px-3 rounded-full text-xs font-bold">{{ ucfirst($visitor->member->kategori) }}</span>
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