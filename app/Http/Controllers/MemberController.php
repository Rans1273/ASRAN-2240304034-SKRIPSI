<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    // Menampilkan daftar member
    public function index(Request $request)
    {
        $query = Member::query();

        // Fitur pencarian sederhana
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('nim_nip', 'LIKE', "%{$search}%")
                  ->orWhere('uid', 'LIKE', "%{$search}%");
        }

        $members = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('members.index', compact('members'));
    }

    // Menampilkan form tambah
    public function create()
    {
        return view('members.create');
    }

    // Menyimpan data baru ke database
    public function store(Request $request)
    {
        $request->validate([
            'uid' => 'required|unique:members,uid',
            'nama' => 'required',
            'npm_nip' => 'required|unique:members,npm_nip',
            'kategori' => 'required',
            'fakultas' => 'required',
            'jurusan' => 'required',
            'status' => 'required',
        ]);

        Member::create($request->all());

        return redirect()->route('members.index')->with('success', 'Data Member berhasil ditambahkan!');
    }

    // Menampilkan form edit
    public function edit(Member $member)
    {
        return view('members.edit', compact('member'));
    }

    // Memperbarui data di database
    public function update(Request $request, Member $member)
    {
        $request->validate([
            'uid' => 'required|unique:members,uid,'.$member->id,
            'nama' => 'required',
            'nim_nip' => 'required|unique:members,nim_nip,'.$member->id,
            'kategori' => 'required',
            'fakultas' => 'required',
            'jurusan' => 'required',
            'status' => 'required',
        ]);

        $member->update($request->all());

        return redirect()->route('members.index')->with('success', 'Data Member berhasil diperbarui!');
    }

    // Menghapus data
    public function destroy(Member $member)
    {
        $member->delete();
        return redirect()->route('members.index')->with('success', 'Data Member berhasil dihapus!');
    }
}