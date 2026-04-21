<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Support\Facades\Log;

class MemberController extends Controller
{
    public function index()
    {
        // Perbaikan: Gunakan paginate() agar fungsi ->links() di view bisa berjalan
        $members = Member::latest()->paginate(10); 
        return view('members.index', compact('members'));
    }

    public function create()
    {
        return view('members.create');
    }

    public function store(Request $request)
{
    $validatedData = $request->validate([
        'uid'      => 'required|string|unique:members,uid',
        'nama'     => 'required|string|max:255',
        'npm_nip'  => 'required|string|unique:members,npm_nip',
        'kategori' => 'required|string',
        'jurusan'  => 'nullable|string|max:255', 
        'fakultas' => 'nullable|string|max:255',
        'status'   => 'required|string',
    ]);

    // 1. SIMPAN KE DATABASE LARAVEL (AMAN & TETAP JALAN)
    $member = Member::create($validatedData);

    // 2. SIMPAN KE FIRESTORE (TIDAK MENGGANGGU SISTEM)
    try {
        $firestore = new FirestoreClient([
            'projectId' => env('FIREBASE_PROJECT_ID'),
            'keyFilePath' => storage_path('app/firebase/firebase.json'),
        ]);

        $firestore->collection('members')
            ->document((string) $member->id)
            ->set([
                'uid'        => $member->uid,
                'nama'       => $member->nama,
                'npm_nip'    => $member->npm_nip,
                'kategori'   => $member->kategori,
                'jurusan'    => $member->jurusan,
                'fakultas'   => $member->fakultas,
                'status'     => $member->status,
                'created_at' => now()->toDateTimeString(),
            ]);

    } catch (\Exception $e) {
        // ❗ Tidak mengganggu sistem utama
        // bisa aktifkan ini kalau debugging:
        // dd($e->getMessage());
    }

    return redirect()->route('members.index')
        ->with('success', 'Data member berhasil ditambahkan!');
}

    public function edit(Member $member)
    {
        return view('members.edit', compact('member'));
    }

    public function update(Request $request, Member $member)
    {
        // 1. Validasi data seperti biasa
        $validatedData = $request->validate([
            'nama'     => 'required|string|max:255',
            'npm_nip'  => 'required|string|unique:members,npm_nip,' . $member->id,
            'kategori' => 'required|string',
            'jurusan'  => 'nullable|string|max:255',
            'fakultas' => 'nullable|string|max:255',
            'uid'      => 'required|string', // Pastikan kolom lain juga divalidasi
            'status'   => 'required|string',
        ]);

        // 2. LOGIKA TAMBAHAN: Paksa kosongkan jika kategori adalah Staff
        // Pastikan 'Staff' menggunakan huruf besar sesuai dengan value di <option> Anda
        if ($validatedData['kategori'] === 'Staff') {
            $validatedData['jurusan'] = null;
            $validatedData['fakultas'] = null;
        }

        // 3. Simpan perubahan
        $member->update($validatedData);

        return redirect()->route('members.index')
                        ->with('success', 'Data member berhasil diperbarui dan disesuaikan!');
    }

    public function destroy(Member $member)
    {
        $member->delete();

        return redirect()->route('members.index')
                         ->with('success', 'Data member berhasil dihapus!');
    }
}