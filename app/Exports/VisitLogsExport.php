<?php

namespace App\Exports;

use App\Models\VisitLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VisitLogsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        // Filter: Hanya ambil log yang membernya BUKAN staff
        $query = VisitLog::with('member')
            ->whereHas('member', function($q) {
                $q->where('kategori', '!=', 'staff');
            })
            ->orderBy('waktu_masuk', 'asc');

        // --- Logika untuk Halaman Pengunjung AKTIF ---
        if ($this->request->has('active_only') && $this->request->active_only == 'true') {
            $query->whereNull('waktu_keluar')
                  ->orderBy('waktu_masuk', 'desc');

            // Filter Search untuk Pengunjung Aktif
            if ($this->request->has('search') && $this->request->search != null) {
                $search = $this->request->search;
                $query->whereHas('member', function($q) use ($search) {
                    $q->where('nama', 'LIKE', "%{$search}%")
                      ->orWhere('npm_nip', 'LIKE', "%{$search}%");
                });
            }

            // Filter Kategori untuk Pengunjung Aktif
            if ($this->request->has('kategori') && $this->request->kategori != null) {
                $query->whereHas('member', function($q) {
                    $q->where('kategori', $this->request->kategori);
                });
            }
            
        } 
        // --- Logika default untuk Halaman Logs (Filter Tanggal) ---
        else if ($this->request->has('start_date') && $this->request->has('end_date') && $this->request->start_date != null) {
            $query->whereBetween('waktu_masuk', [
                $this->request->start_date . ' 00:00:00',
                $this->request->end_date . ' 23:59:59'
            ]);
        }

        return $query->get();
    }

    public function headings(): array
    {
        // Jika export dari halaman aktif, tambahkan kolom Durasi
        if ($this->request->has('active_only') && $this->request->active_only == 'true') {
             return [
                'No.',
                'UID Kartu',
                'NPM/NIP',
                'Nama',
                'Kategori',
                'Fakultas',
                'Jurusan',
                'Waktu Masuk',
                'Durasi Kunjungan',
            ];
        }
        
        return [
            'No.',
            'UID Kartu',
            'NPM/NIP',
            'Nama',
            'Kategori',
            'Fakultas',
            'Jurusan',
            'Waktu Masuk',
            'Waktu Keluar',
            'Durasi Kunjungan',
        ];
    }

    public function map($visit): array
    {
        $waktu_keluar = $visit->waktu_keluar 
            ? Carbon::parse($visit->waktu_keluar)->format('d/m/Y H:i:s') 
            : 'Masih di Dalam';
            
        $waktu_masuk = Carbon::parse($visit->waktu_masuk);
        $waktu_keluar_obj = $visit->waktu_keluar ? Carbon::parse($visit->waktu_keluar) : Carbon::now();

        $durasi = $waktu_masuk->diff($waktu_keluar_obj)->format('%h jam %i menit %s detik');

        // Jika export dari halaman aktif, kolom waktu keluar diganti dengan Durasi
        if ($this->request->has('active_only') && $this->request->active_only == 'true') {
            return [
                $visit->id,
                $visit->member->uid,
                $visit->member->npm_nip,
                $visit->member->nama,
                ucfirst($visit->member->kategori),
                $visit->member->fakultas,
                $visit->member->jurusan,
                Carbon::parse($visit->waktu_masuk)->format('d/m/Y H:i:s'),
                $durasi,
            ];
        }
        
        return [
            $visit->id,
            $visit->member->uid,
            $visit->member->npm_nip,
            $visit->member->nama,
            ucfirst($visit->member->kategori),
            $visit->member->fakultas,
            $visit->member->jurusan,
            Carbon::parse($visit->waktu_masuk)->format('d/m/Y H:i:s'),
            $waktu_keluar,
            $durasi,
        ];
    }
}