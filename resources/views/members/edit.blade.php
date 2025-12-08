@extends('layouts.app')

@section('title', 'Edit Data Member')

@section('content')
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow-sm border border-gray-100">
        <form action="{{ route('members.update', $member->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 gap-6">
                {{-- UID --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">UID Kartu RFID</label>
                    <input type="text" name="uid" value="{{ old('uid', $member->uid) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50" required>
                    @error('uid') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- NPM/NIP --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">NPM / NIP</label>
                    {{-- Input name dan value sudah benar menggunakan 'npm_nip' --}}
                    <input type="text" name="npm_nip" value="{{ old('npm_nip', $member->npm_nip) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2" required>
                    @error('npm_nip') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- Nama --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                    <input type="text" name="nama" value="{{ old('nama', $member->nama) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2" required>
                </div>

                {{-- Kategori --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                    <select name="kategori" class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-white">
                        <option value="Mahasiswa" {{ $member->kategori == 'Mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                        <option value="Dosen" {{ $member->kategori == 'Dosen' ? 'selected' : '' }}>Dosen</option>
                        <option value="Staff" {{ $member->kategori == 'Staff' ? 'selected' : '' }}>Staff</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fakultas</label>
                        <input type="text" name="fakultas" value="{{ old('fakultas', $member->fakultas) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jurusan</label>
                        <input type="text" name="jurusan" value="{{ old('jurusan', $member->jurusan) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    </div>
                </div>

                {{-- Status --}}
                <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-100">
                    <label class="block text-sm font-bold text-gray-800 mb-2">Status Akses</label>
                    <div class="flex gap-4">
                        <label class="flex items-center">
                            <input type="radio" name="status" value="aktif" class="mr-2" {{ $member->status == 'aktif' ? 'checked' : '' }}>
                            <span class="text-green-700 font-medium">Aktif (Boleh Masuk)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="status" value="blokir" class="mr-2" {{ $member->status == 'blokir' ? 'checked' : '' }}>
                            <span class="text-red-700 font-medium">Blokir (Akses Ditolak)</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <a href="{{ route('members.index') }}" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Kembali</a>
                <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">Update Data</button>
            </div>
        </form>
    </div>
@endsection