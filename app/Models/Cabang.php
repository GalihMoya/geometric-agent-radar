<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cabang extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_cabang',
        'kode_cabang',
        'alamat',
        'telepon',
        'latitude',
        'longitude',
        'warna',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    /**
     * Relationship: One Cabang has many Agents (kios/loper)
     */
    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class, 'cabang_id');
    }
}
