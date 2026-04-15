@extends('layouts.app')

@section('title', 'Edit Member')

@section('content')
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow-sm border border-gray-100">
        <form action="{{ route('members.update', $member->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 gap-6">
                {{-- UID --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">UID Kartu RFID</label>
                    <input type="text" name="uid" value="{{ old('uid', $member->uid) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-blue-500 focus:border-blue-500" required>
                    @error('uid') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- NPM/NIP --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">NPM / NIP</label>
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
                    <select name="kategori" id="kategori" onchange="aturKolom()" class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-white">
                        <option value="Mahasiswa" {{ old('kategori', $member->kategori) == 'Mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                        <option value="Dosen" {{ old('kategori', $member->kategori) == 'Dosen' ? 'selected' : '' }}>Dosen</option>
                        <option value="Staff" {{ old('kategori', $member->kategori) == 'Staff' ? 'selected' : '' }}>Staff</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Fakultas --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fakultas</label>
                        <input type="text" name="fakultas" id="fakultas" value="{{ old('fakultas', $member->fakultas) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 transition-colors duration-200">
                    </div>
                    {{-- Jurusan --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jurusan</label>
                        <input type="text" name="jurusan" id="jurusan" value="{{ old('jurusan', $member->jurusan) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 transition-colors duration-200">
                    </div>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Akun</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-white">
                        <option value="aktif" {{ old('status', $member->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="blokir" {{ old('status', $member->status) == 'blokir' ? 'selected' : '' }}>Blokir (Tidak Bisa Masuk)</option>
                    </select>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <a href="{{ route('members.index') }}" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Batal</a>
                <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">Perbarui Data</button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        aturKolom();
    });

    function aturKolom() {
        const kategori = document.getElementById('kategori').value;
        const fakultas = document.getElementById('fakultas');
        const jurusan = document.getElementById('jurusan');

        if (kategori === 'Staff') {
            fakultas.disabled = true;
            jurusan.disabled = true;
            
            fakultas.classList.add('bg-gray-100', 'cursor-not-allowed', 'text-gray-400');
            jurusan.classList.add('bg-gray-100', 'cursor-not-allowed', 'text-gray-400');
            
            // Mengosongkan data saat kategori diubah ke Staff
            fakultas.value = '';
            jurusan.value = '';
            
            fakultas.required = false;
            jurusan.required = false;
        } else {
            fakultas.disabled = false;
            jurusan.disabled = false;
            
            fakultas.classList.remove('bg-gray-100', 'cursor-not-allowed', 'text-gray-400');
            jurusan.classList.remove('bg-gray-100', 'cursor-not-allowed', 'text-gray-400');
            
            fakultas.required = true;
            jurusan.required = true;
        }
    }
</script>
@endsection