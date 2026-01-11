<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    // We use 'course_code' as the primary lookup, but standard ID is fine for DB key
    protected $fillable = [
        'course_code',
        'course_name',
        'description',
        'next_course_code',
        'category',
        'credit',
        'associated_skills',
        'course_content_outline',
    ];

    // AUTOMATICALLY CONVERT ARRAYS TO JSON
    protected $casts = [
        'associated_skills' => 'array',
        'course_content_outline' => 'array',
        'credit' => 'integer'
    ];

    public function students()
    {
        return $this->belongsToMany(User::class, 'course_user')
            ->withPivot('status', 'grade')
            ->withTimestamps();
    }
}
