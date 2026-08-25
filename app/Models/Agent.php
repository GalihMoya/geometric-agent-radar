<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'city',
        'district',
        'village',
        'type',
        'status',
        'latitude',
        'longitude',
        'signal_strength',
        'phone',
        'description',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'signal_strength' => 'integer',
    ];

    /**
     * Scope query to specific city
     */
    public function scopeByCity($query, ?string $city)
    {
        if ($city && $city !== 'all') {
            return $query->where('city', strtolower($city));
        }
        return $query;
    }

    /**
     * Scope query to specific status
     */
    public function scopeByStatus($query, ?string $status)
    {
        if ($status && $status !== 'all') {
            return $query->where('status', strtolower($status));
        }
        return $query;
    }

    /**
     * Scope query to specific type
     */
    public function scopeByType($query, ?string $type)
    {
        if ($type && $type !== 'all') {
            return $query->where('type', $type);
        }
        return $query;
    }
}
