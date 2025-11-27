<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitLog extends Model
{
    protected $guarded = [];
    
    protected $casts = [
        'waktu_masuk' => 'datetime',
        'waktu_keluar' => 'datetime',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Accessor untuk 'status'.
     * Cara pakainya di view: $visit->status
     */
    public function getStatusAttribute()
    {
        // Logika: Jika waktu keluar kosong, berarti Masuk. Jika ada isinya, berarti Keluar.
        return is_null($this->waktu_keluar) ? 'Masuk' : 'Keluar';
    }
}