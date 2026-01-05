<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $fillable = ['name', 'slug', 'domain', 'description'];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('proficiency');
    }

    public function courses()
    {
        // Links back to your existing Course model
        return $this->belongsToMany(Course::class, 'course_skill');
    }
}
