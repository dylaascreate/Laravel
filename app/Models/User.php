<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasRoles, HasApiTokens, HasFactory, Notifiable;

    public function projects()
{
    return $this->hasMany(Project::class);
}

    public function roadmaps()
    {
        return $this->hasMany(Roadmap::class);
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class)->withPivot('proficiency')->withTimestamps();
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_user')
            ->withPivot('status', 'grade')
            ->withTimestamps(); // [!] Important for sorting by updated_at
    }

    public function career()
    {
        return $this->belongsTo(Career::class, 'career_id');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'career_id',
        'status', // Added
        'bio',
        'github',
        'linkedin',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'roles',
        'permissions'
    ];

    protected $appends = ['role'];

    public function getRoleAttribute()
    {
        return $this->getRoleNames()->first();
    }
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
