<?php

namespace App\Exports;

use App\Models\VisitLog;
// Pastikan baris-baris ini ada:
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class VisitLogsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = VisitLog::with('member')->orderBy('waktu_masuk', 'asc');

        // Terapkan filter tanggal jika ada
        if ($this->request->has('start_date') && $this->request->has('end_date') && $this->request->start_date != null) {
            $query->whereBetween('waktu_masuk', [
                $this->request->start_date . ' 00:00:00', 
                $this->request->end_date . ' 23:59:59'
            ]);
        }

        return $query->get();
    }

    public function map($log): array
    {
        return [
            $log->waktu_masuk->format('Y-m-d'),
            $log->member->nama,
            $log->member->npm_nip, // Pastikan sesuai database (npm_nip)
            $log->member->kategori,
            $log->member->jurusan,
            $log->waktu_masuk->format('H:i:s'),
            $log->waktu_keluar ? $log->waktu_keluar->format('H:i:s') : 'Masih di dalam',
            $log->waktu_keluar ? $log->waktu_keluar->diffInMinutes($log->waktu_masuk) . ' Menit' : '-',
        ];
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Nama Lengkap',
            'NIM / NIP',
            'Kategori',
            'Jurusan',
            'Jam Masuk',
            'Jam Keluar',
            'Durasi',
        ];
    }
}