<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoadmapPhase extends Model
{
    protected $guarded = [];

    protected $fillable = ['roadmap_id', 'title', 'description', 'skills', 'order_index'];

    // FIX: Add this $casts array
    protected $casts = [
        'skills' => 'array', // Automatically converts JSON string <-> PHP Array
    ];

    public function roadmap()
    {
        return $this->belongsTo(Roadmap::class);
    }

    public function tasks()
    {
        // Pass 'phase_id' as the second argument to override the default
        return $this->hasMany(RoadmapTask::class, 'phase_id')->orderBy('order_index');
    }
}
