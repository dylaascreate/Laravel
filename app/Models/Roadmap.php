<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Roadmap extends Model
{
    protected $guarded = [];

    public function phases()
    {
        return $this->hasMany(RoadmapPhase::class)->orderBy('order_index');
    }
}
