<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_agen',
        'nama_pemilik',
        'tipe_agen',
        'nomor_whatsapp',
        'alamat_lengkap',
        'latitude',
        'longitude',
        'status',
        'cabang_id',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'cabang_id' => 'integer',
    ];

    /**
     * Relationship to Cabang
     */
    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    /**
     * Scope query to specific cabang
     */
    public function scopeByCabang($query, $cabang)
    {
        if ($cabang && $cabang !== 'all') {
            if (is_numeric($cabang)) {
                return $query->where('cabang_id', $cabang);
            }
            return $query->whereHas('cabang', function ($q) use ($cabang) {
                $q->where('kode_cabang', strtolower($cabang));
            });
        }
        return $query;
    }

    /**
     * Scope query to specific status (aktif/nonaktif)
     */
    public function scopeByStatus($query, ?string $status)
    {
        if ($status && $status !== 'all') {
            return $query->where('status', strtolower($status));
        }
        return $query;
    }

    /**
     * Scope query to specific tipe_agen
     */
    public function scopeByTipe($query, ?string $tipe)
    {
        if ($tipe && $tipe !== 'all') {
            return $query->where('tipe_agen', $tipe);
        }
        return $query;
    }
}
