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
    /**
     * Helper function untuk mengecualikan log kunjungan dari member dengan kategori 'staf'.
     * Hanya member dengan kategori selain 'staff' yang dihitung/ditampilkan dalam log.
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    private function excludeStaff($query)
    {
        // Menggunakan whereHas untuk memfilter record VisitLog berdasarkan kategori Member
        return $query->whereHas('member', function($q) {
            $q->where('kategori', '!=', 'staff');
        });
    }

    public function index()
    {
        $today = Carbon::today();
        
        // Query dasar untuk statistik, hanya mencakup log NON-STAFF
        $baseQuery = $this->excludeStaff(VisitLog::query());

        // 1. KARTU STATISTIK UTAMA
        $todayCount = (clone $baseQuery)->whereDate('waktu_masuk', $today)->count();
        $yesterdayCount = (clone $baseQuery)->whereDate('waktu_masuk', Carbon::yesterday())->count();
        
        // Hitung persentase kenaikan/penurunan
        $diff = $todayCount - $yesterdayCount;
        $percentage = $yesterdayCount > 0 ? round(($diff / $yesterdayCount) * 100, 1) : 100;
        
        $weekCount = (clone $baseQuery)->whereBetween('waktu_masuk', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        $monthCount = (clone $baseQuery)->whereMonth('waktu_masuk', Carbon::now()->month)->count();

        // 2. GRAFIK 7 HARI TERAKHIR (Line Chart)
        $chartData = VisitLog::select(DB::raw('DATE(visit_logs.waktu_masuk) as date'), DB::raw('count(*) as total'))
            ->join('members', 'visit_logs.member_id', '=', 'members.id')
            ->where('members.kategori', '!=', 'staff') // Filter Staff
            ->where('visit_logs.waktu_masuk', '>=', Carbon::now()->subDays(6))
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
        $visitsToday = $this->excludeStaff(VisitLog::with('member'))
            ->whereDate('waktu_masuk', $today)
            ->get();
        
        $mhsCount = $visitsToday->filter(function ($visit) {
            return $visit->member && strtolower($visit->member->kategori) === 'mahasiswa';
        })->count();

        $dosenCount = $visitsToday->filter(function ($visit) {
            return $visit->member && strtolower($visit->member->kategori) === 'dosen';
        })->count();

        // 4. TABEL KUNJUNGAN TERBARU (5 Data Terakhir)
        $latestVisits = $this->excludeStaff(VisitLog::with('member'))
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

    public function active(Request $request)
    {
        // Query dasar mengambil pengunjung yang belum checkout (waktu_keluar masih NULL), dan BUKAN staf
        $query = $this->excludeStaff(VisitLog::with('member'))
            ->whereNull('waktu_keluar')
            ->orderBy('waktu_masuk', 'desc');

        // Tambahkan filter Pencarian (Search)
        if ($request->has('search') && $request->search != null) {
            $search = $request->search;
            $query->whereHas('member', function($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('npm_nip', 'LIKE', "%{$search}%");
            });
        }

        // Tambahkan filter Kategori (Category)
        if ($request->has('kategori') && $request->kategori != null) {
            $query->whereHas('member', function($q) use ($request) {
                $q->where('kategori', $request->kategori);
            });
        }

        $activeVisitors = $query->get();

        return view('active', compact('activeVisitors'));
    }

    public function logs(Request $request)
    {
        // Query dasar mengambil data log beserta data membernya, dan BUKAN staf
        $query = $this->excludeStaff(VisitLog::with('member'))
            ->orderBy('waktu_masuk', 'desc');

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
        // Pengecekan apakah ini export dari halaman pengunjung aktif
        if ($request->has('active_only') && $request->active_only == 'true') {
            // Menggunakan VisitLogsExport yang seharusnya sudah diupdate untuk menangani active_only dan filter
            return Excel::download(new VisitLogsExport($request), 'pengunjung_aktif_' . date('Y-m-d_His') . '.xlsx');
        }
        
        // Logika default untuk logs (jika tidak ada active_only)
        return Excel::download(new VisitLogsExport($request), 'rekap_kunjungan_' . date('Y-m-d_His') . '.xlsx');
    }

    // --- FITUR EXPORT PDF ---
    public function exportPdf(Request $request)
    {
        // Gunakan logika query yang sama dengan logs/excel
        $query = $this->excludeStaff(VisitLog::with('member'));

        $periode = 'Seluruh Data';

        // Pengecekan apakah ini export dari halaman pengunjung aktif
        if ($request->has('active_only') && $request->active_only == 'true') {
            $query->whereNull('waktu_keluar')
                  ->orderBy('waktu_masuk', 'desc');
            $periode = 'Pengunjung Aktif Saat Ini';

            // Terapkan filter Search/Category dari halaman Active
            if ($request->has('search') && $request->search != null) {
                $search = $request->search;
                $query->whereHas('member', function($q) use ($search) {
                    $q->where('nama', 'LIKE', "%{$search}%")
                      ->orWhere('npm_nip', 'LIKE', "%{$search}%");
                });
            }
            if ($request->has('kategori') && $request->kategori != null) {
                $query->whereHas('member', function($q) use ($request) {
                    $q->where('kategori', $request->kategori);
                });
            }

        } else {
            // Logika default untuk logs (filter tanggal)
            $query->orderBy('waktu_masuk', 'asc');
            if ($request->has('start_date') && $request->has('end_date') && $request->start_date != null) {
                $query->whereBetween('waktu_masuk', [
                    $request->start_date . ' 00:00:00', 
                    $request->end_date . ' 23:59:59'
                ]);
                $periode = Carbon::parse($request->start_date)->format('d/m/Y') . ' - ' . Carbon::parse($request->end_date)->format('d/m/Y');
            }
        }

        $visits = $query->get();

        // Load view khusus PDF
        $pdf = Pdf::loadView('exports.logs_pdf', compact('visits', 'periode'));
        
        // Set orientasi kertas
        $pdf->setPaper('a4', 'portrait');

        if ($request->has('active_only') && $request->active_only == 'true') {
            return $pdf->download('laporan_aktif_' . date('Y-m-d') . '.pdf');
        } else {
            return $pdf->download('laporan_kunjungan_' . date('Y-m-d') . '.pdf');
        }
    }
}