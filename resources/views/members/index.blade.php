@extends('layouts.app')

@section('title', 'Manajemen Member')

@section('content')
    {{-- Notifikasi Sukses --}}
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6 flex flex-wrap gap-3 items-center justify-between">
        {{-- Tombol Tambah --}}
        <a href="{{ route('members.create') }}" class="bg-blue-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-blue-700 flex items-center">
            <i class="fas fa-plus mr-2"></i> Tambah Member
        </a>

        {{-- Form Pencarian --}}
        <form action="{{ route('members.index') }}" method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama / NPM / UID..." class="border rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 w-64">
            <button type="submit" class="bg-gray-100 border border-gray-300 text-gray-700 rounded-lg px-3 py-2 hover:bg-gray-200">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-gray-700 font-bold uppercase text-xs">
                <tr>
                    {{-- ID Disembunyikan sesuai permintaan --}}
                    <th class="px-6 py-4">UID Kartu</th>
                    <th class="px-6 py-4">NPM / NIP</th>
                    <th class="px-6 py-4">Nama Lengkap</th>
                    <th class="px-6 py-4">Info Akademik</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($members as $member)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-mono text-blue-600">{{ $member->uid }}</td>
                    <td class="px-6 py-4">{{ $member->npm_nip }}</td>
                    <td class="px-6 py-4 font-bold text-gray-800">{{ $member->nama }}</td>
                    <td class="px-6 py-4">
                        <div class="text-xs">
                            <span class="font-bold block">{{ $member->kategori }}</span>
                            {{ $member->jurusan }} - {{ $member->fakultas }}
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if($member->status == 'aktif')
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-bold">Aktif</span>
                        @else
                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-bold">Blokir</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center flex justify-center gap-2">
                        {{-- Tombol Edit --}}
                        <a href="{{ route('members.edit', $member->id) }}" class="bg-yellow-500 text-white p-2 rounded hover:bg-yellow-600" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        
                        {{-- Tombol Hapus --}}
                        <form action="{{ route('members.destroy', $member->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus {{ $member->nama }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 text-white p-2 rounded hover:bg-red-600" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-6 text-gray-400">Tidak ada data member ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="p-4 border-t border-gray-100">
            {{ $members->links() }}
        </div>
    </div>
@endsection