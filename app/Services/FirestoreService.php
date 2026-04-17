<?php

namespace App\Services;

use Google\Cloud\Firestore\FirestoreClient;

class FirestoreService
{
    protected $db;

    public function __construct()
    {
        $this->db = new FirestoreClient([
            'projectId' => env('FIREBASE_PROJECT_ID'),
            'keyFilePath' => storage_path('app/firebase_credentials.json'),
        ]);
    }

    /**
     * CEK apakah pengunjung masih aktif di perpustakaan
     */
    public function exists($npm_nip)
    {
        $doc = $this->db
            ->collection('pengunjung')
            ->document($npm_nip)
            ->snapshot();

        return $doc->exists();
    }

    /**
     * PENGUNJUNG MASUK
     */
    public function masuk($member)
    {
        $npm_nip = $member->npm_nip;

        $this->db
            ->collection('pengunjung')
            ->document($npm_nip)
            ->set([
                'npm_nip'      => $npm_nip,
                'nama'         => $member->nama ?? null,
                'status'       => 'masuk',
                'waktu_masuk'  => now()->toDateTimeString(),
            ]);
    }

    /**
     * PENGUNJUNG KELUAR
     */
    public function keluar($npm_nip)
    {
        $this->db
            ->collection('pengunjung')
            ->document($npm_nip)
            ->delete();
    }
}