<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsteroidObservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'jpl_id',
        'name',
        'diameter_m',
        'distance_km',
        'velocity_kmh',
        'is_hazardous',
        'priority_level',
        'observation_date',
        'analysis_notes',
        'telescope_image',
    ];

    protected function casts(): array
    {
        return [
            'is_hazardous'     => 'boolean',
            'observation_date' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}