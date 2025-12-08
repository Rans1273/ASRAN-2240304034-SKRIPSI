@extends('layouts.app')

@section('title', 'Tambah Member Baru')

@section('content')
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow-sm border border-gray-100">
        <form action="{{ route('members.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 gap-6">
                {{-- UID --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">UID Kartu RFID</label>
                    <input type="text" name="uid" value="{{ old('uid') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-blue-500 focus:border-blue-500" placeholder="" required>
                    @error('uid') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- NPM/NIP --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">NPM / NIP</label>
                    <input type="text" name="npm_nip" value="{{ old('npm_nip') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2" required>
                    @error('npm_nip') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- Nama --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                    <input type="text" name="nama" value="{{ old('nama') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2" required>
                </div>

                {{-- Kategori --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                    <select name="kategori" class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-white">
                        <option value="Mahasiswa">Mahasiswa</option>
                        <option value="Dosen">Dosen</option>
                        <option value="Staff">Staff</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Fakultas --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fakultas</label>
                        <input type="text" name="fakultas" value="{{ old('fakultas') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    </div>
                    {{-- Jurusan --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jurusan</label>
                        <input type="text" name="jurusan" value="{{ old('jurusan') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    </div>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Akun</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-white">
                        <option value="aktif" selected>Aktif</option>
                        <option value="blokir">Blokir (Tidak Bisa Masuk)</option>
                    </select>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <a href="{{ route('members.index') }}" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Batal</a>
                <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">Simpan Data</button>
            </div>
        </form>
    </div>
@endsection