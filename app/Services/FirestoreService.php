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
        ]);
    }

    public function exists($id)
    {
        $doc = $this->db->collection('pengunjung')->document($id)->snapshot();
        return $doc->exists();
    }

    public function masuk($id)
    {
        $this->db->collection('pengunjung')
            ->document($id)
            ->set([
                'id' => $id,
                'status' => 'masuk',
                'waktu_masuk' => now()->toDateTimeString()
            ]);
    }

    public function keluar($id)
    {
        $this->db->collection('pengunjung')
            ->document($id)
            ->delete();
    }
}