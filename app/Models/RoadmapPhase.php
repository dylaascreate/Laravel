<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoadmapPhase extends Model
{
    protected $guarded = [];

    protected $casts = [
        'skills' => 'array', // Automatically convert JSON to Array
    ];

    public function tasks()
    {
        // Pass 'phase_id' as the second argument to override the default
        return $this->hasMany(RoadmapTask::class, 'phase_id')->orderBy('order_index');
    }
}
