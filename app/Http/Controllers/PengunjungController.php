<?php

namespace App\Http\Controllers;

use App\Services\FirestoreService;

class PengunjungController extends Controller
{
    protected $firestore;

    public function __construct(FirestoreService $firestore)
    {
        $this->firestore = $firestore;
    }

    public function scan($id)
    {
        // cek apakah sudah ada di Firestore
        if ($this->firestore->exists($id)) {

            // KELUAR
            $this->firestore->keluar($id);

            return response()->json([
                'status' => 'keluar',
                'id' => $id
            ]);
        } else {

            // MASUK
            $this->firestore->masuk($id);

            return response()->json([
                'status' => 'masuk',
                'id' => $id
            ]);
        }
    }
}