<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoadmapTask extends Model
{
    protected $guarded = [];

    protected $casts = [
        'completed' => 'boolean',
    ];

    public function phase()
    {
        // Ensure the inverse also uses the correct column
        return $this->belongsTo(RoadmapPhase::class, 'phase_id');
    }
}
