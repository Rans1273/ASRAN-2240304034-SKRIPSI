@extends('layouts.app')

@section('title', 'Log Kunjungan / Rekapitulasi')

@section('content')
    <div class="mb-6">
        <p class="text-gray-500 text-sm">Riwayat kunjungan perpustakaan UBT berdasarkan tanggal</p>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6 flex justify-between items-center flex-wrap gap-4">
        <div class="flex gap-3">
             <div class="flex items-center border rounded-lg px-3 py-2 text-sm bg-gray-50">
                 <span class="text-gray-400 mr-2">Dari:</span>
                 <input type="date" class="bg-transparent focus:outline-none text-gray-600">
            </div>
            <div class="flex items-center border rounded-lg px-3 py-2 text-sm bg-gray-50">
                 <span class="text-gray-400 mr-2">Sampai:</span>
                 <input type="date" class="bg-transparent focus:outline-none text-gray-600">
            </div>
        </div>
        <div class="flex gap-2">
            <button class="border border-gray-300 text-gray-700 rounded-lg px-4 py-2 text-sm font-medium hover:bg-gray-50">Export Excel</button>
            <button class="bg-blue-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-blue-700">Download PDF</button>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="font-bold text-lg text-gray-800">Rekap Data Kunjungan Harian</h3>
        </div>
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-gray-700 font-bold">
                <tr>
                    <th class="px-6 py-4">Tanggal</th>
                    <th class="px-6 py-4">Total Pengunjung</th>
                    <th class="px-6 py-4">Mahasiswa</th>
                    <th class="px-6 py-4">Dosen</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($logs as $log)
                    {{-- Logic untuk menghitung detail per kategori pada tanggal tersebut --}}
                    @php
                        $detail = \App\Models\VisitLog::whereDate('waktu_masuk', $log->date)
                                    ->with('member')
                                    ->get();
                        $countMhs = $detail->where('member.kategori', 'Mahasiswa')->count();
                        $countDosen = $detail->where('member.kategori', 'Dosen')->count();
                    @endphp

                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">{{ \Carbon\Carbon::parse($log->date)->format('d M Y') }}</td>
                        <td class="px-6 py-4 font-bold text-black">{{ $log->total }}</td>
                        <td class="px-6 py-4">
                            <span class="bg-blue-50 text-blue-600 px-2 py-1 rounded font-bold text-xs">{{ $countMhs }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="bg-green-50 text-green-600 px-2 py-1 rounded font-bold text-xs">{{ $countDosen }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-6 text-gray-400">Belum ada data log kunjungan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="p-4 border-t border-gray-100">
            {{ $logs->links() }}
        </div>
    </div>
@endsection