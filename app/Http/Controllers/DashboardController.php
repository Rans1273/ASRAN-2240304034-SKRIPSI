<?php

namespace App\Http\Controllers;

use App\Models\VisitLog;
use App\Models\Member;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\VisitLogsExport;
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // 1. KARTU STATISTIK UTAMA
        $todayCount = VisitLog::whereDate('waktu_masuk', $today)->count();
        $yesterdayCount = VisitLog::whereDate('waktu_masuk', Carbon::yesterday())->count();
        
        // Hitung persentase kenaikan/penurunan
        $diff = $todayCount - $yesterdayCount;
        $percentage = $yesterdayCount > 0 ? round(($diff / $yesterdayCount) * 100, 1) : 100;
        
        $weekCount = VisitLog::whereBetween('waktu_masuk', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        $monthCount = VisitLog::whereMonth('waktu_masuk', Carbon::now()->month)->count();

        // 2. GRAFIK 7 HARI TERAKHIR (Line Chart)
        $chartData = VisitLog::select(DB::raw('DATE(waktu_masuk) as date'), DB::raw('count(*) as total'))
            ->where('waktu_masuk', '>=', Carbon::now()->subDays(6))
            ->groupBy('date')
            ->get()
            ->pluck('total', 'date');

        // Normalisasi data grafik
        $labels = [];
        $dataVisits = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::now()->subDays($i)->format('d M');
            $dataVisits[] = $chartData[$date] ?? 0;
        }

        // 3. KATEGORI PENGUNJUNG HARI INI (Donut Chart)
        // Mengambil data hari ini beserta relasi member
        $visitsToday = VisitLog::with('member')->whereDate('waktu_masuk', $today)->get();
        
        // Menghitung jumlah berdasarkan kategori (menggunakan strtolower untuk antisipasi perbedaan huruf besar/kecil di DB)
        $mhsCount = $visitsToday->filter(function ($visit) {
            return $visit->member && strtolower($visit->member->kategori) === 'mahasiswa';
        })->count();

        $dosenCount = $visitsToday->filter(function ($visit) {
            return $visit->member && strtolower($visit->member->kategori) === 'dosen';
        })->count();

        // 4. TABEL KUNJUNGAN TERBARU (5 Data Terakhir)
        $latestVisits = VisitLog::with('member')
            ->whereDate('waktu_masuk', $today)
            ->orderBy('waktu_masuk', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'todayCount', 'yesterdayCount', 'weekCount', 'monthCount', 'percentage',
            'labels', 'dataVisits',
            'mhsCount', 'dosenCount',
            'latestVisits'
        ));
    }

    public function active()
    {
        // Menampilkan pengunjung yang belum checkout (waktu_keluar masih NULL)
        $activeVisitors = VisitLog::with('member')
            ->whereNull('waktu_keluar')
            ->orderBy('waktu_masuk', 'desc')
            ->get();

        return view('active', compact('activeVisitors'));
    }

    public function logs(Request $request)
    {
        // Query dasar mengambil data log beserta data membernya
        $query = VisitLog::with('member')->orderBy('waktu_masuk', 'desc');

        // Filter berdasarkan Tanggal (Jika user memilih rentang tanggal)
        if ($request->has('start_date') && $request->has('end_date') && $request->start_date != null) {
            $query->whereBetween('waktu_masuk', [
                $request->start_date . ' 00:00:00', 
                $request->end_date . ' 23:59:59'
            ]);
        }

        $visits = $query->get();

        // Mengelompokkan data berdasarkan Tanggal (Y-m-d) untuk tampilan Accordion/Rekapitulasi
        $groupedLogs = $visits->groupBy(function($date) {
            return Carbon::parse($date->waktu_masuk)->format('Y-m-d');
        });

        return view('logs', compact('groupedLogs'));
    }

    // --- FITUR EXPORT EXCEL ---
    public function exportExcel(Request $request)
    {
        // Memanggil Class Export yang sudah dibuat sebelumnya
        return Excel::download(new VisitLogsExport($request), 'rekap_kunjungan_' . date('Y-m-d_His') . '.xlsx');
    }

    // --- FITUR EXPORT PDF ---
    public function exportPdf(Request $request)
    {
        // Gunakan logika query yang sama dengan logs/excel agar data konsisten
        $query = VisitLog::with('member')->orderBy('waktu_masuk', 'asc');

        $periode = 'Seluruh Data';

        if ($request->has('start_date') && $request->has('end_date') && $request->start_date != null) {
            $query->whereBetween('waktu_masuk', [
                $request->start_date . ' 00:00:00', 
                $request->end_date . ' 23:59:59'
            ]);
            $periode = Carbon::parse($request->start_date)->format('d/m/Y') . ' - ' . Carbon::parse($request->end_date)->format('d/m/Y');
        }

        $visits = $query->get();

        // Load view khusus PDF
        $pdf = Pdf::loadView('exports.logs_pdf', compact('visits', 'periode'));
        
        // Set orientasi kertas jika kolom banyak (opsional)
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('laporan_kunjungan_' . date('Y-m-d') . '.pdf');
    }
}