<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Roadmap extends Model
{
    protected $fillable = [
        'user_id',
        'title',        // from AI
        'description',  // from AI
        'type',
        'career_goal',
        'status',
        'progress',
        'level',        // from AI
        'estimate',     // from AI
        'course_code'   // from AI
    ];

    public function course()
    {
        // 2nd arg: foreign key on Roadmaps table
        // 3rd arg: local key on Courses table
        return $this->belongsTo(Course::class, 'course_code', 'course_code');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    protected $guarded = [];

    public function phases()
    {
        return $this->hasMany(RoadmapPhase::class)->orderBy('order_index');
    }

    public function tasks()
    {
        return $this->hasManyThrough(
            RoadmapTask::class,
            RoadmapPhase::class,
            'roadmap_id', // Foreign key on roadmap_phases table...
            'phase_id',   // Foreign key on roadmap_tasks table...
            'id',         // Local key on roadmaps table...
            'id'          // Local key on roadmap_phases table...
        );
    }
}
