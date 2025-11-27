<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VisitLog;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // 1. KARTU STATISTIK
        $todayCount = VisitLog::whereDate('waktu_masuk', $today)->count();
        $yesterdayCount = VisitLog::whereDate('waktu_masuk', Carbon::yesterday())->count();
        
        // Hitung persentase kenaikan/penurunan
        $diff = $todayCount - $yesterdayCount;
        $percentage = $yesterdayCount > 0 ? round(($diff / $yesterdayCount) * 100, 1) : 100;
        
        $weekCount = VisitLog::whereBetween('waktu_masuk', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        $monthCount = VisitLog::whereMonth('waktu_masuk', Carbon::now()->month)->count();

        // 2. GRAFIK 7 HARI TERAKHIR (Line Chart)
        // Mengambil data 7 hari terakhir dan dikelompokkan per tanggal
        $chartData = VisitLog::select(DB::raw('DATE(waktu_masuk) as date'), DB::raw('count(*) as total'))
            ->where('waktu_masuk', '>=', Carbon::now()->subDays(6))
            ->groupBy('date')
            ->get()
            ->pluck('total', 'date');

        // Normalisasi data grafik (agar tanggal yang kosong tetap muncul nilai 0)
        $labels = [];
        $dataVisits = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::now()->subDays($i)->format('d M'); // Label: 30 Apr
            $dataVisits[] = $chartData[$date] ?? 0;
        }

        // 3. KATEGORI (Donut Chart)
        // Kita hitung user unik yang datang hari ini berdasarkan kategori
        $categoryStats = VisitLog::whereDate('waktu_masuk', $today)
            ->with('member')
            ->get()
            ->groupBy('member.kategori')
            ->map->count();
            
        $mhsCount = $categoryStats['Mahasiswa'] ?? 0;
        $dosenCount = $categoryStats['Dosen'] ?? 0;

        // 4. TABEL KUNJUNGAN TERBARU
        $latestVisits = VisitLog::with('member') // Eager load relasi member
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
        // Mengambil data yang waktu_keluarnya masih NULL
        $activeVisitors = VisitLog::with('member')
            ->whereNull('waktu_keluar')
            ->orderBy('waktu_masuk', 'desc')
            ->get();

        return view('active', compact('activeVisitors'));
    }

    public function logs(Request $request)
    {
        // Ambil semua data log, urutkan dari yang terbaru
        $query = VisitLog::with('member')->orderBy('waktu_masuk', 'desc');

        // Filter Tanggal (Jika user memilih range tanggal)
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('waktu_masuk', [
                $request->start_date . ' 00:00:00', 
                $request->end_date . ' 23:59:59'
            ]);
        }

        $visits = $query->get();

        // KELOMPOKKAN DATA BERDASARKAN TANGGAL
        // Hasilnya: ['2025-04-25' => [data_log_1, data_log_2], '2025-04-26' => [...]]
        $groupedLogs = $visits->groupBy(function($date) {
            return \Carbon\Carbon::parse($date->waktu_masuk)->format('Y-m-d');
        });

        return view('logs', compact('groupedLogs'));
    }
}